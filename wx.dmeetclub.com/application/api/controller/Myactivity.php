<?php

namespace app\api\controller;

use app\common\library\Dict;
use app\common\model\Activity;
use app\common\model\ActivityUser;
use app\common\model\CertEducation;
use app\common\model\CertWork;
use app\common\model\Shop;
use app\common\model\ShopApply;
use app\common\model\ShopCategory;
use app\common\model\ShopChange;
use app\common\model\ShopChangeContent;
use app\common\model\User as UserModel;
use app\common\model\UserBalance;
use app\common\model\UserCert;
use tests\thinkphp\library\think\cache\driver\redisTest;
use think\Config;
use think\Request;


/**
 * 我的 - 活动
 */
class Myactivity extends BaseApi
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = '*';
    /** @var \app\common\model\User $user */
    protected $user;

    public function _initialize()
    {
        parent::_initialize();

        $this->user = $this->getUser();
    }

    /**
     * 列表
     * @param Request $request
     * @throws \think\exception\DbException
     */
   public function list(Request $request)
   {
        $status = $request->post('status', null, 'trim,intval');
        $list = (new ActivityUser)->getList($this->user, ['status' => $status]);
        $this->renderSuccess($list);
   }

    /**
     * 活动退款
     * @param Request $request
     * @throws \think\exception\DbException
     * @throws \app\common\exception\BaseException
     */
   public function refund(Request $request)
   {
       $activityUserId = $request->post('id', null, 'trim,intval');
       /** @var ActivityUser $activityUser */
       $activityUser = ActivityUser::get($activityUserId);
       $result = $activityUser->refund();

       if($result !== false) {
           $this->renderSuccess([], "操作成功");
       }
       $this->renderError("操作失败");
   }
}
