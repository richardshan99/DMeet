<?php

namespace app\admin\controller\finance\activity;

use app\common\controller\Backend;
use app\common\library\Dict;
use app\common\model\ActivityUser;
use app\common\model\ShopUser;
use Exception;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\Queue;
use think\Request;

/**
 * 财务管理 - 活动管理 - 活动管理
 *
 * @icon   fa fa-dashboard
 * @remark
 */
class Management extends Backend
{
    /**
     * @var \app\common\model\Activity
     */
    protected $model = null;
    protected $activityTypeList = null;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->model = model('app\common\model\Activity');

        /** @var 活动类型 activityTypeList */
        $this->activityTypeList = model('app\common\model\ActivityType')
            ->where('is_delete', Dict::IS_FALSE)
            ->column('name', 'id');
    }

    /**
     * 查看
     */
    public function index()
    {
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {

            $filter = $this->request->get("filter", '');
            $op = $this->request->get("op", '', 'trim');
            $filter = (array)json_decode($filter, true);
            $op = (array)json_decode($op, true);
            $andWhere = "1=1";

            //筛选 -- 报名状态
            if(array_key_exists('sign_status', $filter) > 0) {
                $_value =  $filter['sign_status'];
                unset($filter['sign_status']);
                unset($op['sign_status']);
                \request()->get(['filter' => json_encode($filter)]);
                \request()->get(['op' => json_encode($op)]);

                $_symbol = '>';
                if($_value == 1){ //人数已够

                    $_symbol = '<=';
                }
                $andWhere .= " and min_num {$_symbol} (select count(`id`) from ".config('database.prefix')."activity_user where activity_id = activity.id and status <> ".Dict::ACTIVITY_USER_STATUS_REFUND.")";
            }

            //筛选 -- 区域
            if(array_key_exists('area', $filter) > 0) {
                $area =  trim($filter['area']);
                unset($filter['area']);
                unset($op['area']);
                \request()->get(['filter' => json_encode($filter)]);
                \request()->get(['op' => json_encode($op)]);

                $area = model('app\common\model\AreaNew')->where('name', 'like', "{$area}")->value('id');
                $area = $area ?: -1;
                $andWhere .= " and find_in_set({$area}, `activity`.`area_path`)";
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams(null);

            $list = $this->model->with(['activityType'])
                ->where($where)
                ->whereRaw($andWhere)
                ->where("activity.is_delete", Dict::IS_FALSE)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->paginate($limit);
//            echo $this->model->with(['activityType'])->getLastSql();exit;

            //参加人数
            $count = model('app\common\model\ActivityUser')
                ->whereIn('activity_id', array_column($list->items(), 'id'))
                ->where('status', '<>', DICT::ACTIVITY_USER_STATUS_REFUND)
                ->group('activity_id')
                ->column('count(`id`) as _count, sum(`price`) as total_price', 'activity_id');

            $areaNew = model('app\common\model\AreaNew')->column('id,name');

            foreach($list as $key => $item)
            {
                //进行中的到期活动，变更为已结束
                if($item->status == Dict::ACTIVITY_STATUS_INPROGRESS && $item->end_time < time()) {
                    $item->save(['status' => Dict::ACTIVITY_STATUS_FINISH]);

                    //更新用户参与状态
                    model('app\common\model\ActivityUser')->allowField(true)->save([
                        'status' => Dict::ACTIVITY_USER_STATUS_FINISH
                    ], [
                        'activity_id' => $item->id,
                        'status' => Dict::ACTIVITY_USER_STATUS_SIGN
                    ]);
                }
                //地区
                $area = DMUserAreaNo($areaNew, $item->area_path, -3);
                $item->area = $area;
                $item->user_count = $count[$item->id]['_count'] ?? 0;
                $item->total_price = $count[$item->id]['total_price'] ?? "0.00";
                $item->append(['area', 'status_text' , 'begin_time_text', 'end_time_text', "user_count", 'total_price']);
            }
            /**
             * 成功举办的活动
             */
            $calTotal = $this->model
                ->field('count(`id`) as total_num')
                ->where([
                    'status'    => Dict::ACTIVITY_STATUS_FINISH,
                    'is_delete' => Dict::IS_FALSE,
                ])->find();
            //总场次
            $totalNum = $calTotal['total_num'];

            $calTotalPrice = model('app\common\model\ActivityUser')->alias('au')
                ->join("__ACTIVITY__ activity", 'au.activity_id = activity.id')
                ->where([
                    'activity.status'    => Dict::ACTIVITY_STATUS_FINISH,
                    'activity.is_delete' => Dict::IS_FALSE,
                    'au.status'          => ['in', [Dict::ACTIVITY_USER_STATUS_SIGN, Dict::ACTIVITY_USER_STATUS_FINISH]],
                ])->group('au.pay_type')->column('au.pay_type, sum(`au`.`price`) as total_price');
            //微信收入
            $totalWechatPrice = $calTotalPrice[Dict::PAY_TYPE_WECHAT] ?? 0.00;
            //余额收入
            $totalBalancePrice = $calTotalPrice[Dict::PAY_TYPE_BALANCE] ?? 0.00;
            $totalPrice = bcadd($totalWechatPrice, $totalBalancePrice, 2);
            //总报名人数
            $calUser = model('app\common\model\ActivityUser')->alias('activity_user')
                ->join('__ACTIVITY__ activity', 'activity.id = activity_user.activity_id')
                ->where([
                    'activity.status'      => Dict::ACTIVITY_STATUS_FINISH,
                    'activity.is_delete'   => Dict::IS_FALSE,
                    'activity_user.status' => Dict::ACTIVITY_USER_STATUS_FINISH,
                ])->count();

            $result = array(
                "total"  => $list->total(),
                "rows"   => $list->items(),
                "extend" => [
                    'total_num'   => $totalNum,
                    'total_price' => $totalPrice,
                    'total_price_wechat' => $totalWechatPrice,
                    'total_price_balance' => $totalBalancePrice,
                    'total_user'  => $calUser
                ]
            );


            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 添加活动
     */
    public function add()
    {
        if (false === $this->request->isPost()) {
            $this->view->assign("activityTypeList", $this->activityTypeList);
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);
        $result = false;

        //分析活动地区
        $areaId = 0;
        $areaPath = [];
        if(isset($params['country']) && !empty($params['country'])) { //国家
            $areaId = $params['country'];
            array_push($areaPath, $params['country']);
        }
        if(isset($params['province']) && !empty($params['province'])) {//省份
            $areaId = $params['province'];
            array_push($areaPath, $params['province']);
        }
        if(isset($params['city']) && !empty($params['city'])) {//地市
            $areaId = $params['city'];
            array_push($areaPath, $params['city']);
        }
        $params['area_id'] = $areaId;
        $params['area_path'] = implode(',', $areaPath);
        Db::startTrans();
        try {
            $result = $this->model->together('content')->allowField(true)->save($params);
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($result === false) {
            $this->error(__('No rows were inserted'));
        }
        $this->success();
    }

    /**
     * 编辑活动
     * @param null $ids
     * @return string
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }

        //活动是否进行中
        if($row->status == Dict::ACTIVITY_STATUS_INPROGRESS) {
            $this->error("活动进行中，无法编辑");
        }
        if (false === $this->request->isPost()) {
            $row->appendRelationAttr('content', ['content']);
            $this->view->assign("activityTypeList", $this->activityTypeList);
            $this->view->assign('row', $row);
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);
        //分析活动地区
        $areaId = 0;
        $areaPath = [];
        if(isset($params['country']) && !empty($params['country'])) { //国家
            $areaId = $params['country'];
            array_push($areaPath, $params['country']);
        }
        if(isset($params['province']) && !empty($params['province'])) {//省份
            $areaId = $params['province'];
            array_push($areaPath, $params['province']);
        }
        if(isset($params['city']) && !empty($params['city'])) {//地市
            $areaId = $params['city'];
            array_push($areaPath, $params['city']);
        }
        $params['area_id'] = $areaId;
        $params['area_path'] = implode(',', $areaPath);
        $result = false;
        Db::startTrans();
        try {
            $result = $row->together('content')->allowField(true)->save($params);
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if (false === $result) {
            $this->error(__('No rows were updated'));
        }
        $this->success();
    }

    /**
     * 删除活动
     * @param null $ids
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function del($ids = null)
    {
        if (false === $this->request->isPost()) {
            $this->error(__('Invalid parameters'));
        }
        $ids = $ids ?: $this->request->post('ids');
        if (empty($ids)) {
            $this->error(__('Parameter %s can not be empty', 'ids'));
        }

        $row = $this->model->where(['id' => $ids])->find();
        if($row->status == Dict::ACTIVITY_STATUS_INPROGRESS) {
            $this->error("活动进行中，无法删除");
        }

        Db::startTrans();
        try {
            $ret = $row->allowField(true)->save(['is_delete' => Dict::IS_TRUE]);
            Db::commit();
        } catch (PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($ret !== false) {
            $this->success();
        }
        $this->error(__('No rows were updated'));
    }

    /**
     * 结束活动
     * @param null $ids
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function cancel($ids = null)
    {
        if (false === $this->request->isPost()) {
            $this->error(__('Invalid parameters'));
        }
        $ids = $ids ?: $this->request->post('ids');
        if (empty($ids)) {
            $this->error(__('Parameter %s can not be empty', 'ids'));
        }

        $row = $this->model->where(['id' => $ids, 'is_delete' => Dict::IS_FALSE])->find();
        if($row->status != Dict::ACTIVITY_STATUS_INPROGRESS) {
            $this->error('当前状态不能结束活动');
        }
        if($row->end_time < time()) {
            $this->error('当前状态不能结束活动');
        }
        Db::startTrans();
        try {
            $ret = $row->allowField(true)->save(['status' => Dict::ACTIVITY_STATUS_CANCEL]);
            Db::commit();
        } catch (PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($ret !== false) {
            //队列处理退费
            //查看有效的报名用户
            $activityUser = model('app\common\model\ActivityUser')->where([
                "activity_id" => $ids,
                "status" => Dict::ACTIVITY_USER_STATUS_SIGN
            ])->select();
            foreach($activityUser as $key => $item) {
                Queue::push('app\job\CancelActivity', [
                    'activity_id' => $ids,
                    'activity_user_id' => $item->id,
                    'user_id' => $item->user_id,
                    'price' => $item->price
                ], 'activity');
            }

            $this->success();
        }
        $this->error(__('No rows were updated'));
    }

    /**
     * 查看报名人员
     * @param null $ids
     * @return string|\think\response\Json
     * @throws \think\Exception
     */
    public function user($ids = null)
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        /** @var ActivityUser model */
        $this->model = model('app\common\model\ActivityUser');
        if ($this->request->isAjax()) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams(null);
            $list = $this->model->with(['user'])
                ->where($where)
                ->where([
                    "activity_id"   => $ids,
                ])
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->paginate($limit);
//            echo $this->projectManagerModel->getLastSql();exit;
            foreach($list as $key => $item) {
                 $item->hidden(['user.active_point']);// 隐藏活跃区域
                $item->append(["status_text"]);
            }

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }

        $this->assignconfig('activity_id', $ids);
        return $this->view->fetch();
    }

}
