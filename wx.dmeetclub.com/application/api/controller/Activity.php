<?php

namespace app\api\controller;

use app\admin\controller\dynamic\Report;
use app\common\library\Dict;
use app\common\model\ActivityType;
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
 * 活动
 */
class Activity extends BaseApi
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = '*';
    /** @var \app\common\model\User $user */
    protected $user;

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 活动分类列表
     */
    public function category()
    {
        $list = (new ActivityType)->getList();
        $this->renderSuccess($list);
    }

    /**
     * 活动列表
     * @param Request $request
     * @throws \think\exception\DbException
     */
    public function list(Request $request)
    {
        $this->user = $this->getUser();

        $filterArea = $request->post('filter_area_id', null, 'trim,intval');
        if($filterArea) {
            $where[] = function($query) use ($filterArea) {
                $filterArea = explode('-', $filterArea);
                $filterArea = array_pop($filterArea);
                $query->whereRaw("find_in_set({$filterArea}, `area_path`)");
            };
        }

        $filterCategory = $request->post('filter_category_id', null, 'trim,intval');
        if($filterCategory) {
            $where[] = function($query) use ($filterCategory) {
                $query->where("activity_type_id", $filterCategory);
            };
        }
        $list = (new \app\common\model\Activity)->getList($where ?? null, $this->user);
        $this->renderSuccess($list);
    }

    /**
     * 活动详情
     * @param Request $request
     * @throws \think\exception\DbException
     */
    public function detail(Request $request)
    {
        try {
            $this->user = $this->getUser();
        } catch (\Exception $ex) {
            $this->user = null;
        }
        $activityId = $request->post('activity_id', null, 'trim,intval');
        $activity = (new \app\common\model\Activity)->get(['id' => $activityId, 'is_delete' => Dict::IS_FALSE]);
        if(!$activity) {
            $this->renderError("活动不存在");
        }
        $detail = $activity->getDetail($this->user);
        $this->renderSuccess($detail);
    }

    /**
     * 付款
     * @param Request $request
     */
    public function pay(Request $request)
    {
        $this->user = $this->getUser();
        $activityId = $request->post('activity_id', null, 'trim,intval');
        $payType = $request->post('pay_type', null, 'trim,intval');
        if(!in_array($payType, [Dict::PAY_TYPE_BALANCE, DIct::PAY_TYPE_WECHAT])) {
            $this->renderError("不支持的支付方式");
        }
        try {
            $result = (new \app\common\model\Activity)->pay($this->user, $activityId, $payType);
        }catch (\Exception $ex) {
            $this->renderError($ex->getMessage());
        }

        $this->renderSuccess($result);
    }
}
