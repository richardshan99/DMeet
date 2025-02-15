<?php

namespace app\api\controller;

use app\common\library\Dict;
use app\common\model\User as UserModel;
use app\common\model\UserChange;
use app\common\model\UserForm;
use app\common\model\UserLabel;
use app\common\library\Sms;
use think\Db;
use think\Request;
use think\Validate;


/**
 * 会员接口
 */
class User extends BaseApi
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = '*';

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 小程序授权登录
     */
    public function login()
    {
        /** @var \app\common\model\User $model */
        $model = new UserModel;
        $user = $model->login($this->request->post());

        return $this->renderSuccess($user);
    }

    /**
     * 完善信息字段列表
     */
    public function improveFieldList()
    {
        $regular = (new UserForm)->regularFieldList();
        $self    = (new UserForm)->selfFieldList();
        $this->renderSuccess(compact('regular', 'self'));
    }

    /**
     * 获取用户信息
     */
    public function info()
    {
        /** @var \app\common\model\User $user */
        $user = $this->getUser();
        //获取信息
        $info = $user->getInfo();
        $this->renderSuccess($info);
    }

    /**
     * 驳回&待完善用户获取历史提交数据
     * @param Request $request
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function getImproveInfo(Request $request)
    {
        $user = $this->getUser();
        if($user->is_improve == Dict::USER_IMPROVE_AUDIT) {
            $this->renderError("您的信息正在审核中");
        }
        if($user->is_improve == Dict::USER_IMPROVE_TRUE) {
            $this->renderError("您的信息已完善");
        }
        $info = $user->getImproveInfo();
        $this->renderSuccess($info);
    }

    /**
     * 完善信息
     *
     * @ApiMethod (POST)
     * @param Request $request
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function improve(Request $request)
    {
        $user = $this->getUser();
        if($user->is_improve == Dict::USER_IMPROVE_AUDIT) {
            $this->renderError("信息审核中，无需重复提交");
        }

        if($user->is_improve == Dict::USER_IMPROVE_TRUE) {
            $this->renderError("信息已完善，无需重复提交");
        }

        $data = $request->post(null,'','trim,strip_tags');
        $rule = [];
        $message = [];

        if(!Validate::is($data['email'], "email")){
            $this->renderError('邮箱格式不正确');
        }
        $ret = \app\common\library\Ems::check($data['email'], $data['email_code'], 'improve');
        if (!$ret){
            $this->renderError(__('邮箱验证码不正确'));
        }

        //昵称唯一
//        $nicknameRequire = model('app\common\model\UserForm')->where('key', 'nickname')->value('is_require');
        $rule["nickname"] = 'require|unique:user,nickname,'.$user->id;
        $message['nickname.unique'] = '昵称已存在，请更换 ';
        $validate = new \think\Validate($rule, $message);
        $result  = $validate->check($data);
        if(false === $result) {
            // 验证失败 输出错误信息
            $this->renderError($validate->getError());
        }

        //年龄
        $age = isset($data['birth']) && !empty($data['birth']) ? DMCalculateAge($data['birth']) : null;
        if($age <= 0){  // 验证年龄
            $this->renderError("请输入正确的生日");
        }
        //所在地
        $permanentArea = isset($data['permanent_area']) && !empty($data['permanent_area']) ? $data['permanent_area'] : null;
        if($permanentArea) {
            $data['area_id'] = $permanentArea;
            $areaNew = model('app\common\model\AreaNew')->get($permanentArea);
            if($areaNew) {
                $data['area_path'] = $areaNew->path;
            } else {
                $this->renderError("你选择的所在地选项不存在");
            }
        }
        //家乡
        $hometown = isset($data['hometown']) && !empty($data['hometown']) ? $data['hometown'] : null;
        if($hometown) {
            $areaNew = model('app\common\model\AreaNew')->get($hometown);
            if($areaNew) {
                $data['hometown'] = $areaNew->path;
            } else {
                $this->renderError("您选择的家乡选项不存在");
            }
        }

        try {
            //2024-7-16 初始提交用户增加审核环节
            (new UserChange)->generate($user, $data);
            unset($data['avatar']);
            unset($data['nickname']);
            $active_point = isset($data['active_point']) ? $data['active_point'] : null; // 增加活跃区域
            unset($data['active_point']);
            $ret = $user->allowField(true)->save(array_merge($data, [
                'is_improve' => Dict::USER_IMPROVE_AUDIT,
                'age' => $age
            ]));
            if($active_point) {
                $_ia = explode(',', $active_point);
                if(isset($_ia[0]) && isset($_ia[1])) {
                    $cord = $_ia[0].",".$_ia[1];
                    Db::execute('update DM_user set `active_point` = point('.$cord.') where id ='.$user->id);
                }
            }
        } catch (\Exception $ex) {
            $this->renderError($ex->getMessage());
            $this->renderError("提交失败");
        }  

        //新用户注册向管理员发邮件，by Richard 
        $subject = "有新用户注册";
        $body = "有新用户注册，快去查看，<br>请登录DMeet直面 微信小程序查看。".Dict::EMAIL_TEXT;
        (new \app\common\library\NewEmail)->send('richard@dmeetclub.com', $subject, $body);  

        if($ret !== false) {
            $this->renderSuccess([], "提交成功");
        }
 
    }

    /**
     * 编辑头像
     * @param Request $request
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function editAvatar(Request $request)
    {
        $avatar = $request->post('avatar/a');
        if(!$avatar) {
            $this->renderError("请上传头像");
        }

        /** @var \app\common\model\User $user */
        $user = $this->getUser();
        if($user->is_check_avatar == Dict::IS_TRUE) {
            $this->renderError("信息审核中");
        }

        try {
            $ret = $user->edit(['avatar' => $avatar]);
        }  catch (\Exception $e) {
            $this->renderError($e->getMessage());
        }

        if($ret !== false) {
            $this->renderSuccess([], "提交成功");
        }

    }

    /**
     * 编辑用户信息
     * @param Request $request
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function edit(Request $request)
    {
        $data = $request->post(null, null, 'trim');

        /** @var \app\common\model\User $user */
        $user = $this->getUser();

        //自定义字段
        $selfField = (new UserForm)->selfFieldList();
        $extraInfo = array();
        foreach($selfField as $key => $item)
        {
            //用户填写的自定义字段
            if(array_key_exists($item['key'], $data)){
                array_push($extraInfo, array_merge($item->toArray(), ['real_value' => $data[$item['key']]]));
            }
        }
        $data['extra_info'] = $extraInfo;


        //年龄
        $age = isset($data['birth']) && !empty($data['birth']) ? DMCalculateAge($data['birth']) : null;
        //所在地
        $permanentArea = isset($data['permanent_area']) && !empty($data['permanent_area']) ? $data['permanent_area'] : null;
        if($permanentArea) {
            $data['area_id'] = $permanentArea;
            $areaNew = model('app\common\model\AreaNew')->get($permanentArea);
            if($areaNew) {
                $data['area_path'] = $areaNew->path;
            } else {
                $this->renderError("你选择的所在地选项不存在");
            }
        }
        //家乡
        $hometown = isset($data['hometown']) && !empty($data['hometown']) ? $data['hometown'] : null;
        if($hometown) {
            $areaNew = model('app\common\model\AreaNew')->get($hometown);
            if($areaNew) {
                $data['hometown'] = $areaNew->path;
            } else {
                $this->renderError("您选择的家乡选项不存在");
            }
        }
        try {
            $ret = $user->edit(array_merge($data, ['age' => $age]));
        }  catch (\Exception $e) {
            $this->renderError($e->getMessage());
        }
        if($ret !== false) {
            $this->renderSuccess([], "提交成功");
        }

        $this->renderError("提交失败");

    }

    /**
     * 编辑自我介绍
     * @param Request $request
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function editIntro(Request $request)
    {
        $intro = $request->post('intro', null, 'trim');
        if(!$intro) {
            $this->renderError("请填写自我介绍");
        }

        /** @var \app\common\model\User $user */
        $user = $this->getUser();
        if($user->is_check_intro == Dict::IS_TRUE) {
            $this->renderError("信息审核中");
        }

        try {
            $ret = $user->edit(['intro' => $intro]);
        }  catch (\Exception $e) {
            $this->renderError($e->getMessage());
        }
        if($ret !== false) {
            $this->renderSuccess([], "提交成功");
        }

        $this->renderError("提交失败");

    }
   

    /**
     * 编辑对TA的要求/期望
     * @param Request $request
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function editmyExpect(Request $request)
    {
        $myExpect = $request->post('myExpect', null, 'trim');
        /** @var \app\common\model\User $user */
        $user = $this->getUser();
        try {
            $ret = $user->edit(['myExpect' => $myExpect]);
        }  catch (\Exception $e) {
            $this->renderError($e->getMessage());
        }
        if($ret !== false) {
            $this->renderSuccess([], "修改成功");
        }
        $this->renderError("修改失败");

    }


    /**
     * 标签列表
     */
    public function labelList()
    {
        $list = (new UserLabel)->getList();
        $this->renderSuccess($list);
    }

    /**
     * 编辑标签
     * @param Request $request
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function editLabel(Request $request)
    {
        $label = $request->post('label/a', null, 'trim');

        /** @var \app\common\model\User $user */
        $user = $this->getUser();

        // if(count($label) > 15) {
        //     $this->renderError("最多添加15个");
        // }
        try {
            $ret = $user->editLabel($label);
        }  catch (\Exception $e) {
            $this->renderError($e->getMessage());
        }
        if($ret !== false) {
            $this->renderSuccess([], "提交成功");
        }

        $this->renderError("提交失败");
    }


}
