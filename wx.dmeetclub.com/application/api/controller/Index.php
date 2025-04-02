<?php

namespace app\api\controller;

use app\common\model\Blog;
use app\common\controller\Api;
use app\common\library\Code;
use app\common\library\Dict;
use app\common\model\Notice;
use app\common\model\UserFollow;
use app\common\model\UserForm;
use app\common\model\UserLabel;
use DI\CompiledContainer;
use think\Config;
use think\Request;

/**
 * 首页接口
 */
class Index extends BaseApi
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];
    protected $user;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
    }

    /**
     * 平台公告 - 列表
     * @param Request $request
     * @return
     * @throws \think\exception\DbException
     */
    public function noticeList(Request $request)
    {
        $list = (new Notice)->getList();
        $this->renderSuccess($list);
    }

    /**
     * 平台公告 - 详情
     * @param Request $request
     * @return
     * @throws \think\exception\DbException
     */
    public function noticeDetail(Request $request)
    {
        $row = (new Notice)->getDetail($request->post('id', null, 'trim,intval'));
        if($row !== false) {
            $this->renderSuccess($row);
        }
        $this->renderError("公告不存在");
    }

    /**
     * 标签列表 - 仅2级
     */
    public function labelList()
    {
        $list = (new UserLabel)->getIndexList();
        $this->renderSuccess($list);
    }

    /**
     * 标签列表 -- 层级 1、2级
     */
    public function allLabelList()
    {
        $list = (new UserLabel)->getIndexAllList();
        $this->renderSuccess($list);
    }

    /**
     * 搜索
     * @param Request $request
     * @throws \think\exception\DbException
     * @throws \app\common\exception\BaseException
     */
    public function search(Request $request)
    {
        /** @var \app\common\model\User user */
        $this->user = $this->getUser();

        //判断用户是否会员
        if($this->user->is_member == Dict::IS_FALSE) {
            $this->renderError("该功能仅限会员开放");
        }
        $search = $request->post('keyword', -1, 'trim');

        $list = (new \app\common\model\User)->list(function($query) use($search) {
            $query->where('nickname', 'like', "%{$search}%")->whereOr('mobile', 'like', "%{$search}%");
        });

        //我关注的
        $follow = model('app\common\model\UserFollow')->where('user_id', $this->user->id)->column('follow_user_id');
        //关注我的
        $fans = model('app\common\model\UserFollow')->where('follow_user_id', $this->user->id)->column('user_id');


        foreach($list as $key => $item)
        {
            $followType = 1;
            if(in_array($item->id, $follow)) {
                $followType = 2;

                if(in_array($item->id, $fans)) {
                    $followType = 3;
                }
            }
            $item->follow_type = $followType;
            $item->visible(['id', 'nickname', 'gender', 'height', 'follow_type']);
            $item->append(['avatar_text', 'birth_text', 'area_text', 'is_cert_education', 'is_member', 'is_cert_realname', 'education_type_text', 'work_type_text',]);
        }

        $this->renderSuccess($list);

    }

    /**
     * 推荐列表
     * @param Request $request
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function recommendList(Request $request)
    {
        try {
            /** @var \app\common\model\User $user */
            $user = $this->getUser();
        /* 根据用户信息完善情况确定推荐用户的数量
         * 若用户信息未完善（is_improve 为 Dict::IS_FALSE），则仅推荐 1 名用户
         * 若用户信息已完善，则推荐 30 名用户
         */
            $limit = $user->is_improve == Dict::IS_FALSE ? 1 : 30;
        }catch (\Exception $ex) {
            $user = null;            
            $limit = 1; //未登录展示一个用户
        }
        /* 获取用户当前的定位信息
         * 从请求的 POST 参数中获取 'position' 字段的值，若未提供则默认为空字符串 
         */
        $position = $request->post('position', '');
        /* 查询推荐用户列表
         * 调用 \app\common\model\User 模型的 list 方法进行查询
         * @param callable $callback 闭包函数，用于构建查询条件
         * @param int $limit 推荐用户的数量
         * @param string $order 排序规则，按照距离升序排列
         * @param string $position 用户当前的定位信息
         * @var \think\Collection $list 推荐用户列表
         */
        $list = (new \app\common\model\User)->list(function($query) use($user, $request) {
        if(!empty($user)) { // 若用户已登录
            $searchArea = $request->post('area_id', null, 'intval,trim');  // 获取用户筛选的地区 ID
            if ($searchArea!== null && $searchArea > 0) {   //初始化推荐列表不按地区筛选，直接显示所有结果
                $query->where("find_in_set({$searchArea}, `area_path`)");
            }            
            $searchGender = $request->post('gender', 0, 'intval,trim');  //筛选 - 性别
            if(empty($searchGender)) {                    
         // $searchGender = $user->gender == Dict::GENDER_MALE ? Dict::GENDER_FEMALE : Dict::GENDER_MALE; //默认展示异性
            $searchGender = -1;     //默认展示全部 ，-1不限制男女            
            }
            if($searchGender != -1) { // 若指定了筛选性别（不为 -1）
                $query->where('gender', $searchGender);
            }
            
            $searchAge = $request->post('age', null, 'trim');   //筛选 - 年龄范围
            if(!empty($searchAge)) {
                $searchAge = explode('-', $searchAge);
                $beginSearchAge = $searchAge[0] ?: 16;   // 取数组的第一个元素作为起始年龄，若为空则默认为16岁
                $endSearchAge   = $searchAge[1] ?: 80;  // 取数组的第二个元素作为结束年龄，若为空则默认为80岁
                $query->whereBetween('age', [$beginSearchAge, $endSearchAge]);
            }
            
            $searchHeight = $request->post('height', null, 'trim');   // 筛选 - 身高范围
            if(!empty($searchHeight)) {
                $searchHeight = explode('-', $searchHeight);
                $beginSearchHeight = $searchHeight[0] ?: 140;   // 取数组的第一个元素作为起始身高，若为空则默认为140cm
                $endSearchHeight   = $searchHeight[1] ?: 200;
                $query->whereBetween('height', [$beginSearchHeight, $endSearchHeight]);
            }
            
            $searchLabels = $request->post('labels/a', null, 'trim');   //筛选 - 标签
            if(!empty($searchLabels)) {     // 若用户指定了标签列表
                $str = [];
                foreach($searchLabels as $key => $litem) {  // 遍历标签列表
                    $str[] = "JSON_SEARCH(`label`, 'one', ".$litem. ") is not NULL";    // 查询 label 字段中包含当前标签的记录
                }
                !$str || $query->whereRaw(implode(' or ', $str));   // 若查询条件数组不为空，则添加标签筛选条件
            }
        }
    }, $limit, 'distance asc',$position);   //按照距离从近到远排序

    // 检查查询结果是否为空
    if ($list->isEmpty()) {
        $this->renderSuccess([]);
        return;
}
    //找出所有二级标签
    $labels = model('app\common\model\UserLabel')->where('is_delete', Dict::IS_FALSE)->column('name', 'id');
    foreach($list as $key => $item) // 遍历推荐用户列表
    {
        $item->birth_year = date('y', strtotime($item->birth)); // 从用户的出生日期中提取出生年份的后两位
        $item->distance = round($item->distance, 2);    // 将用户与当前位置的距离保留两位小数
        $_labels = $item->label ?: [];  // 获取用户的标签列表，若为空则设为空数组

        $userLabels = [];
        foreach($_labels as $key => $_label)    // 遍历用户的标签列表
        {
            $userLabels[] = $labels[$_label['id']] ?? "";   // 根据标签 ID 从 $labels 数组中获取对应的标签名称，若不存在则为空字符串
        }
        $item->label_text = array_values(array_unique(array_filter($userLabels)));  // 去除重复和空的标签名称，重新索引数组
        $item->visible(['id','nickname','gender','height', 'area','is_member', 'school', 'label_text','distance']); // 设置用户信息中需要显示的字段
        $item->append(['birth_year', 'avatar_text', 'is_cert_realname', 'is_cert_education', 'work_type_text', 'education_type_text']);
    }
    $this->renderSuccess($list);
    }

    /**
     * 个人详情页
     * @param Request $request
     * @throws \think\exception\DbException
     * @throws \app\common\exception\BaseException
     */
    public function userInfo(Request $request)
    {
        /** @var \app\common\model\User user */
        $this->user = $this->getUser();

        $userId = $request->post('id', null, 'trim,intval');

        // 用户当前定位、首页增加距离显示
        $position = $request->post('position', '');
        $lon = 117.227267;//经度
        $lat = 31.820567;//纬度
        if(isset($position) && false !== strpos($position, ',')) {
            list($lon, $lat) = explode(',', $position);
        }

        $item = \app\common\model\User::field("*, (st_distance_sphere(point(".$lon.",".$lat."), `active_point`)/1000) as distance, ST_AsText(`active_point`) as point_text")
            ->with('cert')
            ->where(['id' => $userId ?: $this->user->id, 'is_improve' => Dict::IS_TRUE])
            ->find();
            
        if(!$item) {
            $this->renderError("用户不存在");
        }
        $item->birth_year = date('y', strtotime($item->birth));

        $tmpInfo = array();
        $extraTmpInfo = array();
        $extraInfo = $item->extra_info ?: [];
        //解析用户的扩展信息
        foreach($extraInfo as $key => $info) {
            if(!array_key_exists($info['key'], $tmpInfo))  $tmpInfo[$info['key']] = [];

            $formatterValue = $info['real_value'];
            if($info['type'] == Dict::USER_FORM_TYPE_AREA_PICKER) {
                $areaNew = model('app\common\model\AreaNew')->column('id,name');
                $areaPath = model('app\common\model\AreaNew')->where('id', $info['real_value'])->value('path');
                $formatterValue = DMUserArea($areaNew, $areaPath);
            }
            if($info['type'] == Dict::USER_FORM_TYPE_SELECT) {
                $content = $info['content'] ?: [];
                $selectOptions = array_column($content, 'value');
                $searchKey = array_search($info['real_value'], $selectOptions);
                $formatterValue = $content[$searchKey]['title'] ?? "";
            }

            if($info['type'] == Dict::USER_FORM_TYPE_MULTI_SELECT) {
                $content = $info['content'] ?: [];
                $selectOptions = array_column($content, 'value');
                $realValue = explode(",", $info['real_value'] ?: []);
                $formatterValue = [];
                foreach($realValue as $_rkey => $_ritem) {
                    $searchKey = array_search($_ritem, $selectOptions);
                    array_push($formatterValue, $content[$searchKey]['title'] ?? "");
                }
                $formatterValue = implode(',', $formatterValue);
            }
            $tmpInfo[$info['key']] = [
                "value" => $info['real_value'],
                "formatter_value" => $formatterValue,
            ];
        }

        //后台设置的自定义字段
        $selfField = (new UserForm)->selfFieldList();
        //将用户扩展信息带入后台设定的字段
        foreach($selfField as $key => $self)
        {
            array_push($extraTmpInfo, [
                "key" => $self->key,
                "name" => $self->name,
                "value" => $tmpInfo[$self->key]['value'] ?? null,
                "formatter_value" => $tmpInfo[$self->key]['formatter_value'] ?? null,
            ]);
        }

        $item->extra = $extraTmpInfo;

        //是否关注
        $item->is_follow = (new UserFollow)->isExist($this->user->id, $userId);

        //所在地
        $areaNew = model('app\common\model\AreaNew')->column('id,name');
        $areaPath = trim($item->area_path, ',');
        $areaPath = explode(',', $areaPath);
        array_walk($areaPath, function(&$item) use($areaNew) {
            $item = $areaNew[$item];
        });
        $areaPath = array_slice($areaPath, -2);
        $item->area = implode("", $areaPath);

        //家乡
        $hometown = trim($item->hometown, ',');
        $hometown = explode(',', $hometown);
        array_walk($hometown, function(&$item) use($areaNew) {
            $item = $areaNew[$item];
        });
        $hometown = array_slice($hometown, -2);
        $item->hometown = implode("", $hometown);

        $albums = $item->albums  ?: [];
        array_unshift($albums, $item->avatar);
        array_walk($albums, function(&$_album) {
           $_album = cdnurl($_album, true);
        });
        $item->albums_text = $albums;
        $item->distance = round($item->distance, 1);
        //标签
        $label = model('app\common\model\UserLabel')
            ->whereIn('id', array_column($item->label ?: [],'id'))
            ->where('is_delete', Dict::IS_FALSE)
            ->field('id,name')
            ->select();
            
        $item->label = $label;
        $item->visible(['id','nickname','gender','height', 'area','is_member','intro', 'myExpect','label', 'school', 'weight', 'salary','hometown','distance','active_point_text']);
        $item->append(['birth_year', 'avatar_text', 'albums_text', 'is_cert_education', 'is_member', 'is_cert_realname', 'education_type_text', 'work_type_text', 'constellation_text','extra', 'is_follow']);
        $this->renderSuccess($item);

    }

    /**
     * 关注
     * @param Request $request
     * @throws \think\exception\DbException
     */
    public function follow(Request $request)
    {
        /** @var \app\common\model\User user */
        $this->user = $this->getUser();

        if($this->user->is_cert_realname != Dict::IS_TRUE) {
            $this->renderError("请先完成实名认证", Code::USER_NOT_CERT_REALNAME);
        }
        $followId = $request->post('follow_id', null, 'trim,intval');
        if($this->user->id == $followId) {
            $this->renderError("不能关注自己");
        }

        //
        $follow = model('app\common\model\UserFollow')->where([
            "user_id"        => $this->user->id,
            "follow_user_id" => $followId
        ])->find();
        if($follow) { //存在关系则取消关注
            $result = $follow->delete();
        } else { // 不存在关系则创建关注
            //查看被关注人是否存在
            $followUser = \app\common\model\User::get($followId);
            if(!$followUser) {
                $this->renderError("被关注账号不存在");
            }

            $result = UserFollow::create([
                "user_id"        => $this->user->id,
                "follow_user_id" => $followId
            ], true);

            //发送模板消息
            try {
                if($followUser['email']){
                    $subject = "关注通知";
                    $body = "尊敬的#".$followUser['nickname']."，<br>您被新的粉丝关注了，<br>请登录DMeet直面 微信小程序查看。".Dict::EMAIL_TEXT;
                    (new \app\common\library\NewEmail)->send($followUser['email'],$subject,$body);
                }
            } catch (\Exception $ex) {}
        }

        if($result !== false) {
            $this->renderSuccess([], "操作成功");
        }
        $this->renderError("操作失败");
    }

    /**
     * 默认定位
     */
    public function info(Request $request)
    {
        //默认定位
        $default_position = Config::get('site.default_position');
        if(empty($default_position)){
            $position = $request->post('point', null, 'trim');
            if(isset($position) && false !== strpos($position, ',')) 
            {
                list($lon, $lat) = explode(',', $position);
                if($lon && $lat){
                    $default_position = getArea($lon,$lat);
                }
            }
            if(empty($default_position)){
                return '日本-东京';
            }
        }
        //开屏广告
        $screen =  Config::get('site.screen') ? cdnurl(Config::get('site.screen'), true) : "";
        $this->renderSuccess(compact('default_position', 'screen'));
    }

    /**
     * 关注公众号
     */
    public function followWechat()
    {
        $user = $this->getUser();
        if($user->is_follow_wechat == Dict::IS_TRUE) { //已关注
            $this->renderSuccess([], "关注成功");
        }

        $ret= $user->save(['is_follow_wechat' => Dict::IS_TRUE]);
        if($ret !== false)
        {
            $this->renderSuccess([], "关注成功");
        }

        $this->renderError("关注失败");
    }
}
