<?php

namespace app\api\controller;

use app\common\library\Code;
use app\common\model\BlogReport;
use app\common\library\Dict;
use app\common\model\Shop;
use app\common\model\UserAddressFeedback;
use think\Request;

/**
 * 首页个人详情 - 发起邀约接口
 */
class Invitation extends BaseApi
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];
    protected $user;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);

        /** @var \app\common\model\User user */
        $this->user = $this->getUser();
    }

    /**
     * 发起邀约前置检测
     * @param Request $request
     */
   public function check(Request $request)
   {
       //实名认证
       if($this->user->is_cert_realname != Dict::IS_TRUE) {
           $this->renderSuccess([
               "can_invite" => Dict::IS_FALSE,
               "code" => Code::USER_NOT_CERT_REALNAME,
               "message" => '用户未完成实名认证，无法发起邀约',
           ]);
       }

       //后台设置，是否需要会员才能发起邀约
    //   $isVipCanInvite = config('site.is_vip_invite');
    //   if($isVipCanInvite == Dict::IS_TRUE && $this->user->is_member != Dict::IS_TRUE) {
    //       $this->renderSuccess([
    //           "can_invite" => Dict::IS_FALSE,
    //           "code" => Code::USER_NOT_MEMBER,
    //           "message" => '仅会员可以发起邀约',
    //       ]);
    //   }

       $this->renderSuccess(['can_invite' => Dict::IS_TRUE, "code" => 1, "message" => '通过邀约检测']);
   }

    /**
     * 新地址建议
     * @param Request $request
     * @throws \think\exception\DbException
     */
   public function suggestNewAddress(Request $request)
   {
        $areaId = $request->post('area_id', null, 'trim');
        $location = model('app\common\model\AreaNew')->get($areaId);
        if(!$location) {
            $this->renderError("区域不存在");
        }

        $locationPath = explode(',', trim($location->path, ','));
        //去掉洲际
        array_shift($locationPath);

        $locationStr = model('app\common\model\AreaNew')->whereIn('id', $locationPath)->column('name');
        $result = UserAddressFeedback::create([
            "address" => implode('-', $locationStr),
            "area_path" => $location->path,
            "user_id" => $this->user->id,
        ], true);
        if($result !== false) {
            $this->renderSuccess([], "您的需求我们已收到，会尽快增加见面地点");
        }

        $this->renderError("操作失败");
   }

    /**
     * 门店列表
     * @param Request $request
     * @throws \think\exception\DbException
     */
   public function shopList(Request $request)
   {
        $city = $request->post('area', null, 'trim');
        $position = $request->post('position', null, 'trim');
        $shopCategoryId = $request->post('shop_category_id', null, 'trim,intval');
        $type = $request->post('type', 0, 'trim,intval');// 门店类型筛选

        //不展示待完善的门店
        $where['info_status'] = ['<>', Dict::SHOP_INFO_STATUS_UN];
        $where['is_new'] = Dict::IS_FALSE;
        if($shopCategoryId) {//筛选：门店类别
            $where['shop_category_id'] = $shopCategoryId;
        }
        if($type){
            $where['type'] = $type;
        }
        $list = (new Shop)->list($city, $position, $where);
        $this->renderSuccess($list);
   }

    /**
     * 门店详情
     * @param Request $request
     * @throws \think\exception\DbException
     */
   public function shopDetail(Request $request)
   {
       /** @var Shop $shop */
       $shop = model('app\common\model\Shop')
           ->field('*, ST_AsText(`point`) as point_text')
           ->where('id', $request->post('shop_id', null, 'trim,intval'))
           ->find();
           
       if(!$shop) {
           $this->renderError("门店不存在");
       }

       $this->renderSuccess($shop->detail());
   }

    /**
     * 第2-14天不可选择的见面日期
     * @param Request $request
     */
   public function disableMeetDate(Request $request)
   {
        $meets = model('app\common\model\Invite')
            ->where('user_id', $this->user->id)
            ->whereIn('status', [Dict::INVITE_STATUS_WAIT_CONFIRM, Dict::INVITE_STATUS_WAIT_MEET])
            ->whereBetween('meet_time', [strtotime("+1 day", strtotime(date("Y-m-d"))), strtotime("+14 days", strtotime(date("Y-m-d")))])
            ->column(['meet_time']);

        array_walk($meets , function(&$item) {
            $item = date("Y-m-d", $item);
        });

        $this->renderSuccess($meets);
   }

    /**
     * 确定支付
     * @param Request $request
     */
   public function pay(Request $request)
   {
       $data = $request->post();
       $result = $this->validate($data, 'Invitation.pay');

       if($result !== true) {
           $this->renderError($result);
       }

       try {
           $result = (new \app\common\model\Invite)->pay($this->user, $data ,$this->timezone);
       }catch (\Exception $ex) {
           $this->renderError($ex->getMessage());
       }

       $this->renderSuccess($result);
   }

}
