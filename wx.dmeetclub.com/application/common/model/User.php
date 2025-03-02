<?php

namespace app\common\model;

use app\common\exception\BaseException;
use app\common\library\Dict;
use app\common\library\Helper;
use app\common\library\Token;
use app\common\library\wechat\WxUser;
use DI\CompiledContainer;
use think\Cache;
use think\Db;
use think\Env;
use think\Model;
use app\common\library\Sms;
use think\Validate;

/**
 * 会员模型
 */
class User extends Model
{
    protected $name = 'user';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    // 追加属性
    protected $append = [];

    protected $type = [
        "extra_info" => "json",
        "label"      => "json",
        "albums"     => "json"
    ];

    //初始化数据
    protected $insert = [
        "nickname"        => "微信用户",        //初始化默认昵称
        "avatar"          => "https://qiniu.dmeetclub.com/assets/img/avatar.png",        //初始化默认头像
        "is_improve"      => Dict::IS_FALSE,  //是否完善信息：否
        "is_member"       => Dict::IS_FALSE,  //是否会员：否
        "is_follow_wechat"  => Dict::IS_FALSE,  //是否关注公众号：否
        "is_cert_realname"  => Dict::IS_FALSE,  //是否实名：否
        "is_cert_work"      => Dict::IS_FALSE,  //是否认证工作：否
        "is_cert_education" => Dict::IS_FALSE,  //是否认证学校：否
        "is_check_avatar"   => Dict::IS_FALSE,  //是否审核头像：否
        "is_check_nickname" => Dict::IS_FALSE,  //是否审核昵称：否
        "is_check_intro"  => Dict::IS_FALSE,  //是否审核个人简介：否
        "status"          => Dict::USER_NORMAL,// 用户状态：正常
        "balance"         => '0.00',// 用户余额
    ];

    /**
     * 头像
     * @param $value
     * @param $row
     * @return string
     */
    public function getAvatarTextAttr($value, $row)
    {
        return $row['avatar'] ? cdnurl($row['avatar'], true) : "";
    }

    /**
     * 性别
     * @param $value
     * @param $row
     * @return string
     */
    public function getGenderTextAttr($value, $row)
    {
        return Dict::getGender($row['gender']);
    }

    /**
     * 会员到期时间
     * @param $value
     * @param $row
     * @return false|string
     */
    public function getMemberExpireTextAttr($value, $row)
    {
        return $row['member_expire'] ? date("Y-m-d", $row['member_expire']) : "";
    }
    
     /**
     * 会员身份
     */
    public function getIsMemberAttr($value, $row)
    {
        if($row['is_member'] == Dict::IS_TRUE && $row['member_expire'] >time()){
            return Dict::IS_TRUE;
        }else{
            return Dict::IS_FALSE;
        }
        
    }

    /**
     * 出生年份 - 后两位
     * @param $value
     * @param $row
     * @return false|string
     */
    public function getBirthTextAttr($value, $row)
    {
        return $row['birth'] ? date('y', strtotime($row['birth'])) : "";
    }

    /**
     * 地区 - 最后两级
     * @param $value
     * @param $row
     * @return string
     */
    public function getAreaTextAttr($value, $row)
    {
        $path = $row['area_path'] ?: null;
        if(!$path) return "";
        $path = explode(',', trim($path, ','));
        $path = array_slice($path, -2);
        $value = model('app\common\model\AreaNew')->whereIn('id', $path)->column('name');
        return implode("", $value);
    }

    /**
     * 星座 -格式化
     * @param $value
     * @param $row
     * @return mixed|string
     */
    public function getConstellationTextAttr($value, $row)
    {
        return Dict::getConstellationType($row['constellation']);
    }

    /**
     * 工作情况 -格式化
     * @param $value
     * @param $row
     * @return mixed|string
     */
    public function getWorkTypeTextAttr($value, $row)
    {
        return Dict::getWorkType($row['work_type']);
    }

    /**
     * 学历 -格式化
     * @param $value
     * @param $row
     * @return mixed|string
     */
    public function getEducationTypeTextAttr($value, $row)
    {
        return Dict::getEducationType($row['education_type']);
    }

    /***
     * ===================================
     *  关联
     * ===================================
     */

    /**
     * 认证情况
     */
    public function cert()
    {
        return $this->hasOne('app\common\model\UserCert', 'user_id')->bind([
            "realname_status", "education_status", "work_status","idcard",
            "company", "position"
        ]);
    }


    /***
     * ===================================
     *  扩展
     * ===================================
     */
    /**
     * 用户登录
     * @param array $post
     * @return string
     * @throws BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     * @throws \Exception
     */
 

    public function login($post)
    {
        $session = $this->wxlogin($post['code']); 
        $data = $this->register($session, $post);
    
        $user = $data['user'];
        $user->user_id = $user->id;
        $user->token   =  $data['token'];

        $user->visible(['user_id', 'token', 'is_improve', 'login_area']);
        
        return $user;
    }
    


    /**
     * 微信登录 - 小程序
     * @param $code
     * @return array|mixed
     * @throws BaseException
     * @throws \think\exception\DbException
     */
    private function wxlogin($code)
    {
        // 获取当前小程序信息
        $appId = Env::get('mini.appid');
        $appSecret = Env::get('mini.appsecret');

        // 微信登录 (获取session_key)
        $WxUser = new WxUser($appId, $appSecret);
        if (!$session = $WxUser->sessionKey($code)) {
            throw new BaseException(['msg' => $WxUser->getError()]);
        }
        return $session;
    }

    /**
     * 自动注册用户
     * @param $session
     * @param $userInfo
     * @param null $post
     * @param $type
     * @return mixed
     * @throws BaseException
     * @throws \think\exception\DbException
     */
    private function register($session, $post)
    {
        $isNewUser = false;
        $user = self::get(['openid' => $session['openid']]);
 
        if(!$user) {//用户不存在则创建
            $user = $this;
            $userInfo['openid'] = $session['openid'];
            //若当前小程序已绑定到微信开放平台账号下会返回
            $userInfo['unionid'] = $session['unionid'] ?? "";
            $userInfo['source_id'] = $post['source_id'] ?? null;//渠道
            $userInfo['login_area'] = $post['login_area'] ?? null;//登录地区

            $isNewUser = true;
        } elseif($user->status != 'normal') { // 冻结
            throw new BaseException(['msg' => "用户已被冻结"]);
        }

        try {
            //获取手机号 
            $phone = $this->getUserPhoneNumber($post);
            $userInfo['mobile'] = $phone['purePhoneNumber'];            
            //若当前小程序已绑定到微信开放平台账号下会返回
            $userInfo['unionid'] = $session['unionid'] ?? "";
        } catch (\Exception $ex) {
            throw new BaseException(['msg' => $ex->getMessage()]);
        }

        //更新用户信息
        $user->allowField(true)->save($userInfo);
        //如果是新用户，生成认证信息
        if($isNewUser) {
            UserCert::create(['user_id' => $user->id], true);
            //渠道统计@time 2024-9-27
            (new Source)->statis($user, 1);
        }

        $this->token = $this->token($session['openid']);

        // 记录缓存, 30天
        Token::clear($user->id);
        Token::set($this->token, $user->id, 86400 * 30);
        return ['user' => $user, 'token' => $this->token];
    }

    /**
     * 获取手机号
     * @param $post
     * @return bool
     * @throws BaseException
     */
    public function getUserPhoneNumber($post)
    {
        // 获取当前小程序信息
        $appId = Env::get('mini.appid');
        $appSecret = Env::get('mini.appsecret');
        if (empty($appId) || empty($appSecret)) {
            throw new BaseException(['msg' => '小程序未授权']);
        }

        //获取access_token
        $WxUser = new WxUser($appId, $appSecret);
        if (!$accessToken = $WxUser->accessToken()) {
            throw new BaseException(['msg' => $WxUser->getError()]);
        }

        //获取手机号
        if (!$phone = $WxUser->phoneNumber($post, $accessToken)) {
            throw new BaseException(['msg' => $WxUser->getError()]);
        }
        return $phone;
    }

    /**
     * 获取用户信息
     */
    public function getInfo()
    {
        //我的关注
        $this->follow_num = model('app\common\model\UserFollow')->where('user_id', $this->id)->count();
        //我的粉丝
        $this->fans_num = model('app\common\model\UserFollow')->where('follow_user_id', $this->id)->count();
        //最新一次查看时间后新增的粉丝
        $this->new_fans_num = model('app\common\model\UserFollow')->where([
            'follow_user_id' => $this->id,
            'create_time' => ['>', $this->last_fans_time]
        ])->count();

        //最新一次查看时间后新增的消息
        $this->new_message_num = (int)(new UserMessage)->calNewMessageCount($this);

        //信息完成度
        $this->complete_ratio = $this->calCompleteRatio();

        $this->birth_year = date('y', strtotime($this->birth));
        //地区
        $areaNew = model('app\common\model\AreaNew')->column('id,name');
        $this->area = DMUserArea($areaNew, $this->area_path);
        $formatterHometown = explode(', ',trim($this->hometown, ','));
        $this->howntown_last_id = $formatterHometown ? array_pop($formatterHometown) : 0;
       
        $this->hometown = DMUserArea($areaNew, $this->hometown);
        

        //我的标签
        $labelIds = array_column($this->label ?: [], 'id');
        $labels = model('app\common\model\UserLabel')->whereIn('id', $labelIds)->where('is_delete', Dict::IS_FALSE)->field('id,name')->select();
        $this->label = $labels;

        //获取最新的变更信息
        $this->last_checked_avatar = model('app\common\model\UserChange')->where('user_id', $this->id)->whereNotNull('avatar')->order('create_time', 'desc')->value('avatar');
        $this->last_checked_nickname = model('app\common\model\UserChange')->where('user_id', $this->id)->whereNotNull('nickname')->order('create_time', 'desc')->value('nickname');
        $this->last_checked_intro = model('app\common\model\UserChange')->where('user_id', $this->id)->whereNotNull('intro')->order('create_time', 'desc')->value('intro');

        //个人相册
        $albums = $this->albums  ?: [];
        array_unshift($albums, $this->avatar);
        array_walk($albums, function(&$_album) {
            $_album = cdnurl($_album, true);
        });
        $this->albums_text = $albums;

        //扩展信息
        $extraInfo = $this->extra_info ?: [];
        foreach($extraInfo as $key => &$item) {
            switch($item['type']) {
                case Dict::USER_FORM_TYPE_AREA_PICKER:
                    $areaNew = model('app\common\model\AreaNew')->column('id,name');
                    $areaPath = model('app\common\model\AreaNew')->where('id', $item['real_value'])->value('path');
                    $item['formatter_value'] = DMUserArea($areaNew, $areaPath);break;
                case Dict::USER_FORM_TYPE_SELECT:
                    $content = $item['content'] ?: [];
                    $selectOptions = array_column($content, 'value');
                    $searchKey = array_search($item['real_value'], $selectOptions);
                    $item['formatter_value'] = $content[$searchKey]['title'] ?? "";break;
                case Dict::USER_FORM_TYPE_MULTI_SELECT:
                    $content = $item['content'] ?: [];
                    $selectOptions = array_column($content, 'value');
                    $realValue = explode(",", $item['real_value'] ?: []);
                    $formatterValue = [];
                    foreach($realValue as $_rkey => $_ritem) {
                        $searchKey = array_search($_ritem, $selectOptions);
                        array_push($formatterValue, $content[$searchKey]['title'] ?? "");
                    }
                    $item['formatter_value'] = implode(',', $formatterValue);
                    break;
                default:
                    $item['formatter_value'] = $item['real_value'] ?? "";
            }
        }
        array_walk($extraInfo, function(&$item) {
            $item = array_intersect_key($item, array_flip(["type", "key", 'real_value', 'formatter_value']));
        });
        $this->extra_info = $extraInfo;
        $this->age = null;
        if($this->birth) {
            $this->age = DMCalculateAge($this->birth);
        }
        $this->visible([
            'id', 'nickname', 'mobile', 'gender', 'birth', 'age', 'height', 'weight', 'work_type', 'salary', 'new_fans_num',
            'follow_num', 'fans_num', 'new_message_num', 'school', 'education_type','birth_year', 'area', 'hometown', 'area_id', 'howntown_last_id',
            'is_improve', 'is_member', 'is_check_avatar','is_check_nickname','is_check_intro', 'constellation', 'birth',
            'complete_ratio','is_follow_wechat', 'is_cert_realname', 'is_cert_work', 'is_cert_education', 'intro', 'myExpect',  'label',
            'last_checked_avatar','last_checked_nickname','last_checked_intro', 'extra_info','active_point_text','contact_mobile','red_envelope_balance','email'
        ]);

        $this->append([
            'avatar_text', 'albums_text', 'gender_text', 'member_expire_text', 'education_type_text', 'work_type_text', 'constellation_text',
            'cert' => [
                'education_images_text', 'work_images_text', 'education_status_text', 'work_status_text', 'realname_status_text'
            ]
        ]);
        return $this;
    }

    /**
     * 获取token
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * 生成用户认证的token
     * @param $openid
     * @return string
     */
    private function token($openid)
    {
        // 生成一个不会重复的随机字符串
        $guid = \getGuidV4();
        // 当前时间戳 (精确到毫秒)
        $timeStamp = microtime(true);
        // 自定义一个盐
        $salt = 'token_salt';
        return md5("user_{$timeStamp}_{$openid}_{$guid}_{$salt}");
    }

    /**
     * 用户列表 - 分页
     * @param array $where
     * @param int $limit
     * @param null $order
     * @return \think\Paginator
     */
    public function list($where = [], $limit = 20, $order = null,$position=null)
    {
        // 首页增加距离显示
        $lon = 117.2227267;//经度
        $lat = 31.820567;//纬度
        if(isset($position) && false !== strpos($position, ',')) {
            list($lon, $lat) = explode(',', $position);
        }
        $order = $order ?: "create_time desc";
        $orderFunc = is_string($order) ? "orderRaw" : "order";
        $list = $this
            ->field("*, (st_distance_sphere(point(".$lon.",".$lat."), `active_point`)/1000) as distance, ST_AsText(`active_point`) as point_text")
            ->with(['cert'])
            ->where($where)
            ->where(['is_improve' => Dict::IS_TRUE,'status'=>'normal'])
            ->{$orderFunc}($order)
            ->paginate($limit);
        $areaNew = model('app\common\model\AreaNew')->column('id,name');
        foreach($list as $key => $item)
        {
            $areaPath = $item->area_path;
            $item->area = DMUserArea($areaNew, $areaPath);
            $item->append(['area', 'work_type_text']);
        }

        return $list;

    }

    /**
     * 修改用户资料
     * @param array $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function edit($data = [])
    {
        Db::startTrans();
        try {
            if($this->is_check_avatar != Dict::IS_TRUE) {
                if (isset($data['avatar']) && !empty($data['avatar'])) { //头像
                    (new UserChange)->generate($this, ['avatar' => $data['avatar']]);
                }
            }
        
            if (isset($data['email']) && !empty($data['email'])) {
                if(!Validate::is($data['email'], "email")){
                    throw new \Exception('邮箱格式不正确');
                }
            }
            if(isset($data['email_code'])){
                $ret = \app\common\library\Ems::check($data['email'], $data['email_code'], 'improve');
                if (!$ret){
                    throw new \Exception(__('邮箱验证码不正确'));
                }
            }
            
            $active_point = isset($data['active_point']) ? $data['active_point'] : null; // 增加活跃区域
            unset($data['active_point']);
            $ret = $this->allowField([
                "avatar","nickname","intro","birth", "age", "weight", "area_path", "area_id", "hometown", "constellation", "school", "education_type"
                , "height", "myExpect", "extra_info", "work_type", "salary",'email','contact_mobile','active_point_text'
            ])->save($data);
            if($ret === false) {
                throw new \Exception("变更失败");
            }
            if($active_point) {
                $_ia = explode(',', $active_point);
                if(isset($_ia[0]) && isset($_ia[1])) {
                    $cord = $_ia[0].",".$_ia[1];
                    Db::execute('update DM_user set `active_point` = point('.$cord.') where id ='.$this->id);
                }
            }
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw new \Exception($e->getMessage());
        }

        return true;
    }

    /**
     * 编辑标签
     * @param array $label
     * @return false|int
     */
    public function editLabel($label = [])
    {
        array_walk($label, function(&$item) {
            $item = ['id' => $item];
        });
        return $this->allowField(['label'])->save(['label' => $label]);
    }

    /**
     * 计算完成度
     */
    public function calCompleteRatio()
    {
        $all = 0;
        $has = 0;
        $field = [
            "avatar","nickname","gender","height","school",'area_id',
            "education_type","birth","weight","constellation","hometown","work_type","salary",
            "intro"
        ];
        foreach($field as $key => $item)
        {
            ++$all;
            if(!empty($this->{$item})) ++$has;
        }
//        //固定字段
        $regularField = (new UserForm)->regularFieldList();
//        foreach($regularField as $key => $item)
//        {
//            ++$all;
//            if(!empty($this->{$item->key})) ++$has;
//        }

//        //自定义字段
//        $selfField = (new UserForm)->selfFieldList();
//        $extraInfo = $this->extra_info ?: [];
////        var_dump($extraInfo);exit;
//        $extraAssoc = [];
//        foreach($extraInfo as $_ekey => $_eitem)
//        {
//            $extraAssoc[$_eitem['key']] = $_eitem['real_value'];
//        }
//
//        foreach($selfField as $_sk => $_sitem)
//        {
//            ++$all;
//            if(isset($extraAssoc[$_sitem->key]) && !empty($extraAssoc[$_sitem->key])) ++$has;
//        }

        return $all ? bcmul(bcdiv($has, $all, 5), 100, 2) : "0.00";
    }

    /**
     * 回显用户提交的完善信息资料
     */
    public function getImproveInfo()
    {
        $areaNew = model('app\common\model\AreaNew')->column('id,name');
        $arr = [
            "avatar" => [],
            "nickname"=> "",
            "gender"=> (string)$this->gender ?: "",
            "permanent_area"=> trim($this->area_path, ","),
            "height"=> (string)$this->height,
            "school"=> (string)$this->school,
            "education_type"=> (string)$this->education_type,
            "birth"=> (string)$this->birth,
            "weight"=> (string)$this->weight,
            "constellation"=> (string)$this->constellation,
            "hometown"=> trim($this->hometown, ","),
            "work_type"=> (string)$this->work_type,
            "salary"=> (string)$this->salary,
            "hometown_name" => $this->hometown ? DMUserAreaNo($areaNew, $this->hometown) : "",
            "permanent_area_name" => $this->area_path ? DMUserAreaNo($areaNew, $this->area_path) : "",
        ];

        //如果有审核记录，提取最新一条
        $lastChange = model('app\common\model\UserChange')->where('user_id', $this->id)->order('create_time', 'desc')->find();
        if($lastChange) {
            if($lastChange->avatar) {
                $albums = $lastChange->albums ?: [];
                array_unshift($albums, $lastChange->avatar);
                $arr['avatar'] = $albums;
            }

            if($lastChange->nickname) {
                $arr['nickname'] = $lastChange->nickname;
            }
        }
        return $arr;

    }

}
