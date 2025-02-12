<?php


namespace app\common\library;



class Dict
{
    const EMAIL_TEXT = "<br><img src='https://qiniu.dmeetclub.com/uploads/20241211/FpXswMi5P3j_dUtbtOmkkXw0qOow.png' style='max-width: 100%; height: auto;'><br>一切真实的了解从见面开始";

    /** @var int 用户状态：正常 */
    const USER_NORMAL = "normal";
    /** @var int 用户状态：冻结 */
    const USER_HIDDEN = "hidden";

    /** @var int 邀约用户类型：邀请人 */
    const INVITATION_USER_TYPE_INVITER = "inviter";
    /** @var int 邀约用户类型：被邀请人 */
    const INVITATION_USER_TYPE_INVITEE = "invitee";

    /** @var int 支付方式：微信 */
    const PAY_TYPE_WECHAT = 1;
    /** @var int 支付方式：余额 */
    const PAY_TYPE_BALANCE = 2;

    protected static $payTypeAssoc = [
        self::PAY_TYPE_WECHAT => '微信',
        self::PAY_TYPE_BALANCE => '余额',
    ];

    /**
     * 支付方式
     * @param null $key
     * @return mixed|string
     */
    public static function getPayType($key = null)
    {
        if(array_key_exists($key, static::$payTypeAssoc)) {
            return static::$payTypeAssoc[$key];
        }
        return '';
    }

    /** @var int 支付服务：活动 */
    const SERVICE_TYPE_ACTIVITY = 1;
    /** @var int 支付服务：邀约 */
    const SERVICE_TYPE_INVITATION = 2;
    /** @var int 支付服务：同意邀约 */
    const SERVICE_TYPE_APPROVE_INVITATION = 3;
    /** @var int 支付服务：发起召集 */
    const SERVICE_TYPE_INVITE_CALL = 4;
    /** @var int 支付服务：购买会员 */
    const SERVICE_TYPE_BUY_MEMBER = 5;

    /** @var int 动态类型：普通 */
    const BLOG_STYLE_NORMAL = 1;
    /** @var int 动态类型：邀约见面 */
    const BLOG_STYLE_INVITATION_MEET = 2;
     /** @var int 动态类型：同步分享 */
    const BLOG_STYLE_SHARE = 3;

    const BLOG_STYLE_SHARE_CONTENT = [
        '通过DMeet认识的他，今天在咖啡店见面，感觉比在线上更真实。虽然有些紧张，但聊得很愉快，发现我们有很多相似的兴趣。希望以后还能有更多的机会深入了解彼此',
        '第一次通过DMeet见面，他给人的印象很好。我们聊得自然，话题也挺有趣的。虽然有些小尴尬，但整体气氛轻松愉快。期待未来能更多了解彼此，看看是否有机会继续发展',
        '今天和他在咖啡店见面，感觉挺自然的。他很会聊天，话题很丰富，气氛也很轻松。虽然我们有些话不太投机，但整体来说还是很愉快的一次见面，希望下次可以更了解对方',
        '第一次和他见面，整体感觉不错。我们聊了很多，有些共鸣，也有些分歧。尽管如此，我觉得彼此都很真诚，没什么刻意的伪装。不管怎样，真实就好！',
        '柔和的灯光，空气中弥漫着浓郁的咖啡香。初次见面，彼此间的拘谨在轻松的对话中渐渐消融。对方的幽默感让我感到愉悦，而他认真倾听的姿态又让我觉得被尊重。虽然只是短暂的时光，但这次约会让我对他的印象分外深刻。。',
        '在咖啡店的角落，我们初次相遇。他比我想象中更加健谈，我们分享了彼此的故事和梦想。虽然我们之间还有很长一段距离，但这次见面让我对未来充满期待。谢谢dmeet～',
        '通过DMeet认识的他，见面后感觉还不错。聊天很轻松，有些话题挺有意思。虽然有点紧张，但总体氛围还好，看看是否能更深入了解彼此。',
        '今天和DMeet认识的人见面，聊得挺愉快的。虽然刚开始有些尴尬，但很快就放松了。期待以后有更多机会交流。',
        '今天和他见面，聊得挺开心的。虽然有些小紧张，但气氛轻松愉快。期待下次再见，进一步了解彼此。',
        '第一次见面感觉不错，话题很自然。虽然有点尴尬，但整体很愉快，希望以后能多交流。',
        '感谢DMeet让我们有机会见面。今天的约会很愉快，聊天轻松自然，期待未来能更深入了解彼此，继续保持联系。',
        '感谢DMeet的机会，今天和他见面很愉快。虽然有点紧张，但整体气氛不错，希望能继续聊下去，看看能否进一步发展。'
    ];

    /** @var int 见面数据：自动生成 */
    const MEET_DATA_SOURCE_AUTO = 1;
    /** @var int 见面数据：后台虚拟 */
    const MEET_DATA_SOURCE_VIRT = 2;

    /** @var int 开关：是 */
    const IS_TRUE = 1;
    /** @var int 开关：否 */
    const IS_FALSE = -1;

    /** @var int 性别：男 */
    const GENDER_MALE   = 1;
    /** @var int 性别：女 */
    const GENDER_FEMALE = 2;

    protected static $genderAssoc = [
        self::GENDER_MALE => '男',
        self::GENDER_FEMALE => '女',
    ];

    /** @var int 用户完善状态：已完善 */
    const USER_IMPROVE_TRUE = 1;
    /** @var int 用户完善状态：未完善 */
    const USER_IMPROVE_FALSE = -1;
    /** @var int 用户完善状态：审核中 */
    const USER_IMPROVE_AUDIT = -2;

    /**
     * 性别
     * @param null $key
     * @return mixed|string
     */
    public static function getGender($key = null)
    {
        if(array_key_exists($key, static::$genderAssoc)) {
            return static::$genderAssoc[$key];
        }
        return '';
    }

    /** @var int 认证审核状态：未认证 */
    const CERT_STATUS_UN     = -1;
    /** @var int 认证审核状态：待审核 */
    const CERT_STATUS_WAIT   = 1;
    /** @var int 认证审核状态：通过 */
    const CERT_STATUS_APPROVE = 2;
    /** @var int 认证审核状态：驳回 */
    const CERT_STATUS_REJECT  = 3;

    protected static $certStatus = [
        self::CERT_STATUS_UN       => '未认证',
        self::CERT_STATUS_WAIT     => '待审核',
        self::CERT_STATUS_APPROVE  => '通过',
        self::CERT_STATUS_REJECT   => '驳回',
    ];

    /**
     * 性别
     * @param null $key
     * @return mixed|string
     */
    public static function getCertStatus($key = null)
    {
        if(array_key_exists($key, static::$certStatus)) {
            return static::$certStatus[$key];
        }
        return '';
    }

    /** @var int 余额变更原因：支付减少 */
    const USER_BALANCE_TYPE_PAY_DECR             = 1;
    /** @var int 余额变更原因：邀约退还增加 */
    const USER_BALANCE_TYPE_INVITE_INCR          = 2;
    /** @var int 余额变更原因：邀约分享增加 */
    const USER_BALANCE_TYPE_INVITE_SHARE_INCR    = 3;
    /** @var int 余额变更原因：活动退款增加 */
    const USER_BALANCE_TYPE_ACTIVITY_REFUND_INCR = 4;

    /** @var array 余额变更原因  */
    protected static $userBalanceType = [
        self::USER_BALANCE_TYPE_PAY_DECR                => '支付减少',
        self::USER_BALANCE_TYPE_INVITE_INCR             => '邀约退还增加',
        self::USER_BALANCE_TYPE_INVITE_SHARE_INCR       => '邀约分享增加',
        self::USER_BALANCE_TYPE_ACTIVITY_REFUND_INCR    => '活动退款增加',
    ];

    /**
     * 余额变更原因
     * @param null $key
     * @return mixed|string
     */
    public static function getUserBalanceType($key = null)
    {
        if(array_key_exists($key, static::$userBalanceType)) {
            return static::$userBalanceType[$key];
        }
        return '';
    }
    
     // 见面红包
    /** @var int 见面红包变更原因：见面获得 */
    const USER_RED_BALANCE_1         = 1;
    /** @var int 见面红包变更原因：提现减少 */
    const USER_RED_BALANCE_2          = 2;
    /** @var int 见面红包变更原因：提现驳回增加 */
    const USER_RED_BALANCE_3          = 3;

    protected static $userRedBalanceType = [
        self::USER_RED_BALANCE_1                => '见面获得',
        self::USER_RED_BALANCE_2             => '提现减少',
        self::USER_RED_BALANCE_3       => '提现驳回增加',
    ];

    /**
     * 见面红包变更原因
     * @param null $key
     * @return mixed|string
     */
    public static function getUserRedBalanceType($key = null): string
    {
        if(array_key_exists($key, static::$userRedBalanceType)) {
            return static::$userRedBalanceType[$key];
        }
        return '';
    }
    


    /** @var int 申请入驻状态：审核中 */
    const SHOP_APPLY_STATUS_WAIT   = 1;
    /** @var int 申请入驻状态：通过 */
    const SHOP_APPLY_STATUS_APPROVE = 2;
    /** @var int 申请入驻状态：驳回 */
    const SHOP_APPLY_STATUS_REJECT  = 3;

    protected static $shopApplyStatus = [
        self::SHOP_APPLY_STATUS_WAIT     => '待审核',
        self::SHOP_APPLY_STATUS_APPROVE  => '通过',
        self::SHOP_APPLY_STATUS_REJECT   => '驳回',
    ];

    /**
     * 申请入驻状态
     * @param null $key
     * @return mixed|string
     */
    public static function getShopApplyStatus($key = null)
    {
        if(array_key_exists($key, static::$shopApplyStatus)) {
            return static::$shopApplyStatus[$key];
        }
        return '';
    }

    /** @var int 门店信息状态：待完善 */
    const SHOP_INFO_STATUS_UN       = -1;
    /** @var int 门店信息状态：审核中 */
    const SHOP_INFO_STATUS_WAIT     = 1;
    /** @var int 门店信息状态：通过 */
    const SHOP_INFO_STATUS_APPROVE = 2;
    /** @var int 门店信息状态：驳回 */
    const SHOP_INFO_STATUS_REJECT  = 3;

    protected static $shopInfoStatus = [
        self::SHOP_INFO_STATUS_UN           => '待审核',
        self::SHOP_INFO_STATUS_WAIT         => '审核中',
        self::SHOP_INFO_STATUS_APPROVE      => '通过',
        self::SHOP_INFO_STATUS_REJECT       => '驳回',
    ];

    /** @var int 门店类型：餐厅 */
    const SHOP_TYPE_RESTAURANT = 1;
    /** @var int 门店类型：非餐厅 */
    const SHOP_TYPE_NO_RESTAURANT = 2;

   
    /**
     * 门店信息状态
     * @param null $key
     * @return mixed|string
     */
    public static function getShopInfoStatus($key = null)
    {
        if(array_key_exists($key, static::$shopInfoStatus)) {
            return static::$shopInfoStatus[$key];
        }
        return '';
    }

    /** @var int 门店人员角色：店长 */
    const SHOP_USER_ROLE_MANAGE       = 1;
    /** @var int 门店人员角色：店员 */
    const SHOP_USER_ROLE_CLERK        = 2;


    /** @var int 提现账户：微信 */
    const CASH_TYPE_WECHAT       = 1;
    /** @var int 提现账户：支付宝 */
    const CASH_TYPE_ALIPAY       = 2;
    /** @var int 提现账户：银行卡 */
    const CASH_TYPE_BANK         = 3;

    /** @var array 提现账户 */
    protected static $cashType = [
        self::CASH_TYPE_WECHAT       => '微信',
        self::CASH_TYPE_ALIPAY       => '支付宝',
        self::CASH_TYPE_BANK         => '银行卡',
    ];
    /**
     * 提现账户
     * @param null $key
     * @return mixed|string
     */
    public static function getCashType($key = null)
    {
        if(array_key_exists($key, static::$cashType)) {
            return static::$cashType[$key];
        }
        return '';
    }

    /**
     * 提现账户
     * @return mixed|string
     */
    public static function getCashTypeList()
    {
        return static::$cashType;
    }

    /** @var int 活动报名状态：人数不够 */
    const ACTIVITY_SIGN_STATUS_LACK   = -1;
    /** @var int 活动报名状态：人数已够 */
    const ACTIVITY_SIGN_STATUS_ENOUGH = 1;

    /** @var array 活动报名状态 */
    protected static $activitySignStatus = [
        self::ACTIVITY_SIGN_STATUS_LACK       => '人数不够',
        self::ACTIVITY_SIGN_STATUS_ENOUGH     => '人数已够',
    ];

    /**
     * 活动报名状态
     * @param null $key
     * @return mixed|string
     */
    public static function getActivitySignStatus($key = null)
    {
        if(array_key_exists($key, static::$activitySignStatus)) {
            return static::$activitySignStatus[$key];
        }
        return '';
    }

    /** @var int 活动状态：进行中 */
    const ACTIVITY_STATUS_INPROGRESS   = 1;
    /** @var int 活动状态：已结束 */
    const ACTIVITY_STATUS_FINISH       = 2;
    /** @var int 活动状态：已取消 */
    const ACTIVITY_STATUS_CANCEL       = 3;

    /** @var array 活动状态 */
    protected static $activityStatus = [
        self::ACTIVITY_STATUS_INPROGRESS       => '进行中',
        self::ACTIVITY_STATUS_FINISH           => '已结束',
        self::ACTIVITY_STATUS_CANCEL           => '已取消',
    ];
    /**
     * 活动状态
     * @param null $key
     * @return mixed|string
     */
    public static function getActivityStatus($key = null)
    {
        if(array_key_exists($key, static::$activityStatus)) {
            return static::$activityStatus[$key];
        }
        return '';
    }

    /**
     * 活动状态
     * @return mixed|string
     */
    public static function getActivityStatusList()
    {
        return static::$activityStatus;
    }


    /** @var int 活动报名人状态：已报名 */
    const ACTIVITY_USER_STATUS_SIGN   = 1;
    /** @var int 活动报名人状态：已完成 */
    const ACTIVITY_USER_STATUS_FINISH = 2;
    /** @var int 活动报名人状态：已退款 */
    const ACTIVITY_USER_STATUS_REFUND = 3;

    /** @var array 活动报名人状态 */
    protected static $activityUserStatus = [
        self::ACTIVITY_USER_STATUS_SIGN       => '已报名',
        self::ACTIVITY_USER_STATUS_FINISH     => '已完成',
        self::ACTIVITY_USER_STATUS_REFUND     => '已退款',
    ];

    /**
     * 活动报名人状态
     * @param null $key
     * @return mixed|string
     */
    public static function getActivityUserStatus($key = null)
    {
        if(array_key_exists($key, static::$activityUserStatus)) {
            return static::$activityUserStatus[$key];
        }
        return '';
    }

    /** @var int 用户表单类型：文件上传 */
    const USER_FORM_TYPE_FILE = 1;
    /** @var int 用户表单类型：单行文本 */
    const USER_FORM_TYPE_TEXT = 2;
    /** @var int 用户表单类型：多行文本 */
    const USER_FORM_TYPE_MULTI_TEXT = 3;
    /** @var int 用户表单类型：单选 */
    const USER_FORM_TYPE_RADIO = 4;
    /** @var int 用户表单类型：日期选择 */
    const USER_FORM_TYPE_DATE = 5;
    /** @var int 用户表单类型：数字 */
    const USER_FORM_TYPE_NUMBER = 6;
    /** @var int 用户表单类型：数字选择器 */
    const USER_FORM_TYPE_NUMBER_PICKER = 7;
    /** @var int 用户表单类型：地区选择器 */
    const USER_FORM_TYPE_AREA_PICKER = 8;
    /** @var int 用户表单类型：下拉 */
    const USER_FORM_TYPE_SELECT = 9;
    /** @var int 用户表单类型：下拉（多选） */
    const USER_FORM_TYPE_MULTI_SELECT = 10;

    /** @var array 用户表单类型 */
    protected static $userFormType = [
        self::USER_FORM_TYPE_FILE           => '文件上传',
        self::USER_FORM_TYPE_TEXT           => '单行文本',
        self::USER_FORM_TYPE_MULTI_TEXT     => '多行文本',
        self::USER_FORM_TYPE_RADIO          => '单选',
        self::USER_FORM_TYPE_DATE           => '日期选择',
        self::USER_FORM_TYPE_NUMBER         => '数字',
        self::USER_FORM_TYPE_NUMBER_PICKER  => '数字选择器',
        self::USER_FORM_TYPE_AREA_PICKER    => '地区选择器',
        self::USER_FORM_TYPE_SELECT         => '下拉',
        self::USER_FORM_TYPE_MULTI_SELECT   => '下拉（多选）',
    ];

    /**
     * 用户表单类型
     * @param null $key
     * @return mixed|string
     */
    public static function getUserFormType($key = null)
    {
        if(array_key_exists($key, static::$userFormType)) {
            return static::$userFormType[$key];
        }
        return '';
    }


    /** @var int 邀约类型： 我邀约的 */
    const INVITE_TYPE_MY_INVITE = 1;
    /** @var int 邀约类型： 邀约我的 */
    const INVITE_TYPE_INVITE_ME = 2;

    /** @var array 邀约类型 */
    protected static $inviteTypeAssoc = [
        self::INVITE_TYPE_INVITE_ME    => '邀约我的',
        self::INVITE_TYPE_MY_INVITE    => '我邀约的',
    ];

    /**
     * 邀约类型
     * @param null $key
     * @return mixed|string
     */
    public static function getInviteType($key = null)
    {
        if(array_key_exists($key, static::$inviteTypeAssoc)) {
            return static::$inviteTypeAssoc[$key];
        }
        return '';
    }

    /** @var int 邀约付款类型： 我付 */
    const INVITE_PAY_MODE_MY      = 1;
    /** @var int 邀约付款类型： 你付 */
    const INVITE_PAY_MODE_YOU     = 2;
    /** @var int 邀约付款类型： AA */
    const INVITE_PAY_MODE_HALF    = 3;

    /** @var array 邀约付款类型 */
    protected static $invitePayModeAssoc = [
        self::INVITE_PAY_MODE_MY      => '我付',
        self::INVITE_PAY_MODE_YOU     => '你付',
        self::INVITE_PAY_MODE_HALF    => 'AA',
    ];

    /**
     * 邀约付款类型
     * @param null $key
     * @return mixed|string
     */
    public static function getInvitePayMode($key = null)
    {
        if(array_key_exists($key, static::$invitePayModeAssoc)) {
            return static::$invitePayModeAssoc[$key];
        }
        return '';
    }


    /** @var int 邀约状态：待确认 */
    const INVITE_STATUS_WAIT_CONFIRM   = 1;
    /** @var int 邀约状态：待见面 */
    const INVITE_STATUS_WAIT_MEET   = 2;
    /** @var int 邀约状态：已完成 */
    const INVITE_STATUS_FINISH = 3;
    /** @var int 邀约状态：已取消 */
    const INVITE_STATUS_CANCEL = 4;

    /** @var array 邀约状态 */
    protected static $inviteStatusAssoc = [
        self::INVITE_STATUS_WAIT_CONFIRM    => '待确认',
        self::INVITE_STATUS_WAIT_MEET       => '待见面',
        self::INVITE_STATUS_FINISH          => '已完成',
        self::INVITE_STATUS_CANCEL          => '已取消',
    ];
    
    
    // 邀约修改见面信息状态 - wgl
    /** @var int 邀约修改见面信息状态：待处理 */
    const INVITE_CHANGE_STATUS_1   = 1;
    /** @var int 邀约修改见面信息状态：已确认 */
    const INVITE_CHANGE_STATUS_2   = 2;
    /** @var int 邀约修改见面信息状态：已拒绝 */
    const INVITE_CHANGE_STATUS_3   = 3;

    protected static $inviteChangeStatus = [
        self::INVITE_CHANGE_STATUS_1    => '待处理',
        self::INVITE_CHANGE_STATUS_2    => '已确认',
        self::INVITE_CHANGE_STATUS_3    => '已拒绝',
    ];

    /**
     * 邀约见面修改状态
     * @param null $key
     * @return mixed|string
     */
    public static function getInviteChangeStatus($key = null)
    {
        if(array_key_exists($key, static::$inviteChangeStatus)) {
            return static::$inviteChangeStatus[$key];
        }
        return '';
    }

    /**
     * 邀约状态
     * @param null $key
     * @return mixed|string
     */
    public static function getInviteStatus($key = null)
    {
        if(array_key_exists($key, static::$inviteStatusAssoc)) {
            return static::$inviteStatusAssoc[$key];
        }
        return '';
    }

    /** @var int 动态状态：审核中 */
    const BLOG_STATUS_WAIT     = 1;
    /** @var int 动态状态：通过 */
    const BLOG_STATUS_APPROVE  = 2;
    /** @var int 动态状态：驳回 */
    const BLOG_STATUS_REJECT   = 3;

    protected static $blogStatusAssoc = [
        self::BLOG_STATUS_WAIT      => '审核中',
        self::BLOG_STATUS_APPROVE      => '通过',
        self::BLOG_STATUS_REJECT       => '驳回',
    ];

    /**
     * 动态状态
     * @param null $key
     * @return mixed|string
     */
    public static function getBlogStatus($key = null)
    {
        if(array_key_exists($key, static::$blogStatusAssoc)) {
            return static::$blogStatusAssoc[$key];
        }
        return '';
    }

    /** @var int 召集状态：未支付 */
    const INVITE_CALL_STATUS_UN      = -1;
    /** @var int 召集状态：进行中 */
    const INVITE_CALL_STATUS_PROCESS = 1;
    /** @var int 召集状态：邀约失败 */
    const INVITE_CALL_STATUS_FAILURE = 2;
    /** @var int 召集状态：邀约成功 */
    const INVITE_CALL_STATUS_SUCCESS = 3;

    /**
     * 召集状态
     * @var array
     */
    protected static $inviteCallStatusAssoc = [
        self::INVITE_CALL_STATUS_UN       => '未支付',
        self::INVITE_CALL_STATUS_PROCESS  => '进行中',
        self::INVITE_CALL_STATUS_FAILURE  => '邀约失败',
        self::INVITE_CALL_STATUS_SUCCESS  => '邀约成功',
    ];

    /**
     * 召集状态
     * @param null $key
     * @return mixed|string
     */
    public static function getInviteCallStatus($key = null)
    {
        if(array_key_exists($key, static::$inviteCallStatusAssoc)) {
            return static::$inviteCallStatusAssoc[$key];
        }
        return '';
    }

    /** @var int 门店金额类型：营收增加 */
    const SHOP_BALANCE_TYPE_REVENUE_INCR = 1;
    /** @var int 门店金额类型：提现成功减少 */
    const SHOP_BALANCE_TYPE_CASH_DECR = 2;
    /** @var int 门店金额类型：提现失败返回增加 */
    const SHOP_BALANCE_TYPE_CASH_FAILURE_INCR = 3;

    /**
     * 门店金额类型
     * @var array
     */
    protected static $shopBalanceTypeAssoc = [
        self::SHOP_BALANCE_TYPE_REVENUE_INCR       => '营收增加',
        self::SHOP_BALANCE_TYPE_CASH_DECR          => '提现成功减少',
        self::SHOP_BALANCE_TYPE_CASH_FAILURE_INCR  => '提现失败返回增加',
    ];

    /**
     * 门店金额类型
     * @param null $key
     * @return mixed|string
     */
    public static function getShopBalanceType($key = null)
    {
        if(array_key_exists($key, static::$shopBalanceTypeAssoc)) {
            return static::$shopBalanceTypeAssoc[$key];
        }
        return '';
    }

    /** @var int 门店提现状态：待审核 */
    const SHOP_CASH_STATUS_WAIT = 1;
    /** @var int 门店提现状态：通过 */
    const SHOP_CASH_STATUS_APPROVE = 2;
    /** @var int 门店提现状态：驳回 */
    const SHOP_CASH_STATUS_REJECT = 3;

    /**
     * 门店提现状态
     * @var array
     */
    protected static $shopCashStatusAssoc = [
        self::SHOP_CASH_STATUS_WAIT       => '待审核',
        self::SHOP_CASH_STATUS_APPROVE    => '通过',
        self::SHOP_CASH_STATUS_REJECT     => '驳回',
    ];

    /**
     * 门店提现状态
     * @param null $key
     * @return mixed|string
     */
    public static function getShopCashStatus($key = null)
    {
        if(array_key_exists($key, static::$shopCashStatusAssoc)) {
            return static::$shopCashStatusAssoc[$key];
        }
        return '';
    }


    /** @var int 工作认证行业类型：农林牧渔 */
    const CERT_WORK_TRADE_TYPE_NLMY = 1;
    /** @var int 工作认证行业类型：采矿 */
    const CERT_WORK_TRADE_TYPE_CK = 2;
    /** @var int 工作认证行业类型：制造 */
    const CERT_WORK_TRADE_TYPE_ZZ = 3;
    /** @var int 工作认证行业类型：电力 */
    const CERT_WORK_TRADE_TYPE_DL = 4;
    /** @var int 工作认证行业类型：建筑 */
    const CERT_WORK_TRADE_TYPE_JZ = 5;
    /** @var int 工作认证行业类型：批发零售 */
    const CERT_WORK_TRADE_TYPE_PFLS = 6;
    /** @var int 工作认证行业类型：仓储运输 */
    const CERT_WORK_TRADE_TYPE_CCYS = 7;
    /** @var int 工作认证行业类型：餐饮 */
    const CERT_WORK_TRADE_TYPE_CY = 8;
    /** @var int 工作认证行业类型：信息技术 */
    const CERT_WORK_TRADE_TYPE_XXJS = 9;
    /** @var int 工作认证行业类型：金融 */
    const CERT_WORK_TRADE_TYPE_JR = 10;
    /** @var int 工作认证行业类型：房地产 */
    const CERT_WORK_TRADE_TYPE_DC = 11;
    /** @var int 工作认证行业类型：服务 */
    const CERT_WORK_TRADE_TYPE_FW = 12;
    /** @var int 工作认证行业类型：教育 */
    const CERT_WORK_TRADE_TYPE_JY = 13;
    /** @var int 工作认证行业类型：文体 */
    const CERT_WORK_TRADE_TYPE_WT = 14;
    /** @var int 工作认证行业类型：其他 */
    const CERT_WORK_TRADE_TYPE_QT = 15;

    /**
     * 工作认证行业类型
     * @var array
     */
    protected static $certWorkTradeTypeAssoc = [
        self::CERT_WORK_TRADE_TYPE_NLMY     => '农林牧渔',
        self::CERT_WORK_TRADE_TYPE_CK       => '采矿',
        self::CERT_WORK_TRADE_TYPE_ZZ       => '制造',
        self::CERT_WORK_TRADE_TYPE_DL       => '电力',
        self::CERT_WORK_TRADE_TYPE_JZ       => '建筑',
        self::CERT_WORK_TRADE_TYPE_PFLS     => '批发零售',
        self::CERT_WORK_TRADE_TYPE_CCYS     => '仓储运输',
        self::CERT_WORK_TRADE_TYPE_CY       => '餐饮',
        self::CERT_WORK_TRADE_TYPE_XXJS     => '信息技术',
        self::CERT_WORK_TRADE_TYPE_JR       => '金融',
        self::CERT_WORK_TRADE_TYPE_DC       => '房地产',
        self::CERT_WORK_TRADE_TYPE_FW       => '服务',
        self::CERT_WORK_TRADE_TYPE_JY       => '教育',
        self::CERT_WORK_TRADE_TYPE_WT       => '文体',
        self::CERT_WORK_TRADE_TYPE_QT       => '其他',
    ];

    /**
     * 工作认证行业类型
     * @param null $key
     * @return mixed|string
     */
    public static function getCertWorkTradeType($key = null)
    {
        if(array_key_exists($key, static::$certWorkTradeTypeAssoc)) {
            return static::$certWorkTradeTypeAssoc[$key];
        }
        return '';
    }

    /**
     * 工作认证行业类型列表
     * @param null $key
     * @return mixed|string
     */
    public static function getCertWorkTradeTypeList()
    {
        return static::$certWorkTradeTypeAssoc;
    }

 
    /** @var int 学历类型：专科 */
    const EDUCATION_TYPE_COLLEGE = 1;
    /** @var int 学历类型：本科 */
    const EDUCATION_TYPE_UNDERGRADUATE = 2;
    /** @var int 学历类型：硕士 */
    const EDUCATION_TYPE_POSTGRADUATE = 3;
    /** @var int 学历类型：博士 */
    const EDUCATION_TYPE_DOCTOR = 4;

    /**
     * 学历类型
     * @var array
     */
    protected static $educationTypeAssoc = [
        self::EDUCATION_TYPE_COLLEGE           => '专科',
        self::EDUCATION_TYPE_UNDERGRADUATE     => '本科',
        self::EDUCATION_TYPE_POSTGRADUATE      => '硕士',
        self::EDUCATION_TYPE_DOCTOR      => '博士',
    ];

    /**
     * 学历类型
     * @param null $key
     * @return mixed|string
     */
    public static function getEducationType($key = null)
    {
        if(array_key_exists($key, static::$educationTypeAssoc)) {
            return static::$educationTypeAssoc[$key];
        }
        return '';
    }

    /**
     * 学历类型
     * @param null $key
     * @return mixed|string
     */
    public static function getEducationTypeList()
    {
        return static::$educationTypeAssoc;
    }

    /**
     * 学历类型 - 二维
     * @param null $key
     * @return mixed|string
     */
    public static function getEducationTypeListForIndex()
    {
        $list = static::$educationTypeAssoc;
        foreach($list as $key => &$item) {
            $item = [
                "value" => $key,
                "name"  => $item,
            ];
        }

        return array_values($list);
    }

    /** @var int 星座类型：水瓶Aquarius */
    const CONSTELLATION_TYPE_AQUARIUS = 1;
    /** @var int 星座类型：双鱼Pisces */
    const CONSTELLATION_TYPE_PISCES = 2;
    /** @var int 星座类型：白羊Aries */
    const CONSTELLATION_TYPE_ARIES = 3;
    /** @var int 星座类型：金牛Taurus */
    const CONSTELLATION_TYPE_TAURUS = 4;
    /** @var int 星座类型：双子Gemini */
    const CONSTELLATION_TYPE_GEMINI = 5;
    /** @var int 星座类型：巨蟹Cancer */
    const CONSTELLATION_TYPE_CANCER = 6;
    /** @var int 星座类型：狮子Leo */
    const CONSTELLATION_TYPE_LEO = 7;
    /** @var int 星座类型：处女Virgo */
    const CONSTELLATION_TYPE_VIRGO = 8;
    /** @var int 星座类型：天平Libra */
    const CONSTELLATION_TYPE_LIBRA = 9;
    /** @var int 星座类型：天蝎Scorpio */
    const CONSTELLATION_TYPE_SCORPIO = 10;
    /** @var int 星座类型：射手Sagittarius */
    const CONSTELLATION_TYPE_SAGITTARIUS = 11;
    /** @var int 星座类型：摩羯Capricornus */
    const CONSTELLATION_TYPE_CAPRICORNUS = 12;

    /**
     * 星座类型
     * @var array
     */
    protected static $constellationTypeAssoc = [
        self::CONSTELLATION_TYPE_AQUARIUS         => '水瓶座',
        self::CONSTELLATION_TYPE_PISCES           => '双鱼座',
        self::CONSTELLATION_TYPE_ARIES            => '白羊座',
        self::CONSTELLATION_TYPE_TAURUS           => '金牛座',
        self::CONSTELLATION_TYPE_GEMINI           => '双子座',
        self::CONSTELLATION_TYPE_CANCER           => '巨蟹座',
        self::CONSTELLATION_TYPE_LEO              => '狮子座',
        self::CONSTELLATION_TYPE_VIRGO            => '处女座',
        self::CONSTELLATION_TYPE_LIBRA            => '天平座',
        self::CONSTELLATION_TYPE_SCORPIO          => '天蝎座',
        self::CONSTELLATION_TYPE_SAGITTARIUS      => '射手座',
        self::CONSTELLATION_TYPE_CAPRICORNUS      => '摩羯座',
    ];

    /**
     * 星座类型
     * @param null $key
     * @return mixed|string
     */
    public static function getConstellationType($key = null)
    {
        if(array_key_exists($key, static::$constellationTypeAssoc)) {
            return static::$constellationTypeAssoc[$key];
        }
        return '';
    }

    /**
     * 星座类型
     * @param null $key
     * @return mixed|string
     */
    public static function getConstellationTypeList()
    {
        return static::$constellationTypeAssoc;
    }

    /**
     * 星座类型 - 二维
     * @param null $key
     * @return mixed|string
     */
    public static function getConstellationTypeListForIndex()
    {
        $list = static::$constellationTypeAssoc;
        foreach($list as $key => &$item) {
            $item = [
                "value" => $key,
                "name"  => $item,
            ];
        }

        return array_values($list);
    }


    /** @var int 工作类型：学生 */
    const WORK_TYPE_1 = 1;
    /** @var int 工作类型：IT/互联网/通信 */
    const WORK_TYPE_2 = 2;
    /** @var int 工作类型：房地产/金融 */
    const WORK_TYPE_3 = 3;
    /** @var int 工作类型：销售/市场/贸易 */
    const WORK_TYPE_4 = 4;
    /** @var int 工作类型：教育/科研/医疗 */
    const WORK_TYPE_5 = 5;
    /** @var int 工作类型：酒店/餐饮/旅游 */
    const WORK_TYPE_6 = 6;
    /** @var int 工作类型：生产/制造/物流 */
    const WORK_TYPE_7 = 7;
    /** @var int 工作类型：艺术/传媒/广告 */
    const WORK_TYPE_8 = 8;
    /** @var int 工作类型：财务/行政/人事 */
    const WORK_TYPE_9 = 9;
    /** @var int 工作类型：法律/咨询/顾问 */
    const WORK_TYPE_10 = 10;
    /** @var int 工作类型：服务业 */
    const WORK_TYPE_11 = 11;
    /** @var int 工作类型：公务员 */
    const WORK_TYPE_12 = 12;
    /** @var int 工作类型：创业者 */
    const WORK_TYPE_13 = 13;
    /** @var int 工作类型：高级管理 */
    const WORK_TYPE_14 = 14;
    /** @var int 工作类型：自由职业 */
    const WORK_TYPE_15 = 15;
    /** @var int 工作类型：待业 */
    const WORK_TYPE_16 = 16;
    /** @var int 工作类型：其它 */
    const WORK_TYPE_17 = 17;

    /**
     * 工作类型
     * @var array
     */
    protected static $workTypeAssoc = [
        self::WORK_TYPE_1     => '学生',
        self::WORK_TYPE_2     => 'IT/互联网/通信',
        self::WORK_TYPE_3     => '房地产/金融',
        self::WORK_TYPE_4     => '销售/市场/贸易',
        self::WORK_TYPE_5     => '教育/科研/医疗',
        self::WORK_TYPE_6     => '酒店/餐饮/旅游',
        self::WORK_TYPE_7     => '生产/制造/物流',
        self::WORK_TYPE_8     => '艺术/传媒/广告',
        self::WORK_TYPE_9     => '财务/行政/人事',
        self::WORK_TYPE_10     => '法律/咨询/顾问',
        self::WORK_TYPE_11     => '服务业',
        self::WORK_TYPE_12     => '公务员',
        self::WORK_TYPE_13     => '创业者',
        self::WORK_TYPE_14     => '高级管理',
        self::WORK_TYPE_15     => '自由职业',
        self::WORK_TYPE_16     => '待业',
        self::WORK_TYPE_17     => '其它'
    ];

    /**
     * 工作类型
     * @param null $key
     * @return mixed|string
     */
    public static function getWorkType($key = null)
    {
        if(array_key_exists($key, static::$workTypeAssoc)) {
            return static::$workTypeAssoc[$key];
        }
        return '';
    }

    /**
     * 工作类型
     * @param null $key
     * @return mixed|string
     */
    public static function getWorkTypeList()
    {
        return static::$workTypeAssoc;
    }

    /**
     * 工作类型 - 二维
     * @param null $key
     * @return mixed|string
     */
    public static function getWorkTypeListForIndex()
    {
        $list = static::$workTypeAssoc;
        foreach($list as $key => &$item) {
            $item = [
                "value" => $key,
                "name"  => $item,
            ];
        }

        return array_values($list);
    }

    
    /** @var string 微信模版消息类型：关注 */
    const SEND_WECHAT_MESSAGE_TYPE_FOLLOW = 'SEND_WECHAT_MESSAGE_TYPE_FOLLOW';
    /** @var string 微信模版消息类型：邀约我的 */
    const SEND_WECHAT_MESSAGE_TYPE_INVITE = 'SEND_WECHAT_MESSAGE_TYPE_INVITE';
    /** @var string 微信模版消息类型：同意我的邀约 */
    const SEND_WECHAT_MESSAGE_TYPE_INVITATION_APPROVE = 'SEND_WECHAT_MESSAGE_TYPE_INVITATION_APPROVE';
    /** @var string 微信模版消息类型：拒绝我的邀约 */
    const SEND_WECHAT_MESSAGE_TYPE_INVITATION_REFUSE = 'SEND_WECHAT_MESSAGE_TYPE_INVITATION_REFUSE';
    /** @var string 微信模版消息类型：系统消息 */
    const SEND_WECHAT_MESSAGE_TYPE_SYSTEM = 'SEND_WECHAT_MESSAGE_TYPE_SYSTEM';
    /** @var string 微信模版消息类型：消息留言 */
    const SEND_WECHAT_MESSAGE_TYPE_CHAT = 'SEND_WECHAT_MESSAGE_TYPE_CHAT';

    protected static $wechatMessageIdAssoc = [
        self::SEND_WECHAT_MESSAGE_TYPE_FOLLOW => 'tyrYbrzJu-NKisWaGAo50zQY00kQuRi55dtCfdrf4sc',
        self::SEND_WECHAT_MESSAGE_TYPE_INVITE => '5j7J4CZUotAC4txMC7nZNknZo_rZKfjCTnx-IcJUk2I',
        self::SEND_WECHAT_MESSAGE_TYPE_INVITATION_APPROVE => 'yyZkX14ssUWwG70oole3mI3SRuvnRXjFR15L97e8xME',
        self::SEND_WECHAT_MESSAGE_TYPE_INVITATION_REFUSE => 'YcyK9hdE8-6GZCSuRV5UdhUeHifRyYCwt1hRI_12o7g',
        self::SEND_WECHAT_MESSAGE_TYPE_SYSTEM => 'tyrYbrzJu-NKisWaGAo50zQY00kQuRi55dtCfdrf4sc',
        self::SEND_WECHAT_MESSAGE_TYPE_CHAT => 'tyrYbrzJu-NKisWaGAo50zQY00kQuRi55dtCfdrf4sc',

    ];

    /**
     * 模版消息id
     * @param $messageId
     * @return array
     */
    public static function getWechatMessageId($messageId)
    {
        return Dict::$wechatMessageIdAssoc[$messageId] ?? "not found";
    }

    protected static $wechatMiniUrlAssoc = [
        self::SEND_WECHAT_MESSAGE_TYPE_FOLLOW => '/pages/follow_me/follow_me',
        self::SEND_WECHAT_MESSAGE_TYPE_INVITE => '/pages/invitation/invitation',
        self::SEND_WECHAT_MESSAGE_TYPE_INVITATION_APPROVE => '/pages/invitation/invitation',
        self::SEND_WECHAT_MESSAGE_TYPE_INVITATION_REFUSE => '/pages/invitation/invitation',
        self::SEND_WECHAT_MESSAGE_TYPE_SYSTEM => '/pages/follow_me/follow_me',
        self::SEND_WECHAT_MESSAGE_TYPE_CHAT => '/pages/follow_me/follow_me',

    ];

    /**
     * 模版消息id
     * @param $messageId
     * @return array
     */
    public static function getWechatMiniUrl($messageId)
    {
        return Dict::$wechatMiniUrlAssoc[$messageId] ?? "not found";
    }
}