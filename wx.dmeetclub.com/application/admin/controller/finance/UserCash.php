<?php

namespace app\admin\controller\finance;


use app\common\controller\Backend;
use app\common\library\Dict;
use app\common\library\pay\Wechat;
use app\common\model\User;
use app\common\model\UserRedEnvelopeBalance;
use think\Db;
use think\Exception;
use think\exception\DbException;
use think\Request;
use Yansongda\Pay\Pay;

/**
 * 用户见面红包提现
 *
 * @icon   fa fa-dashboard
 * @remark
 */
class UserCash extends Backend
{
    /**
     * @var \app\common\model\UserRedBalanceCash
     */
    protected $model = null;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->model = model('app\common\model\UserRedBalanceCash');
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
            list($where, $sort, $order, $offset, $limit) = $this->buildparams(null);
            $list = $this->model->with(['user'])
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->paginate($limit);
            foreach($list as $key => $item)
            {
                $item->hidden(['shop' => ['position']]);
                $item->visible([
                    "id", "money", "status","reject_reason","create_time",
                    "user" => ["mobile"],
                ]);
            }
            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }


    /**
     * 通过
     * @param $ids
     * @return string
     * @throws Exception
     * @throws DbException
     */
    public function pass_through($ids): string
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }

        if ($this->request->isPost()) {
            Db::startTrans();
            try {
                if ($row['status'] != $this->model::CASH_STATUS_1) {
                    exception('只有待处理的数据能操作');
                }
                $info = $this->model->where(['id'=>$ids])->lock(true)->find();
                if(empty($info)){
                    exception(__('No Results were found'));
                }
                if(empty($info['trade_no'])){
                    exception(__('提现记录生成有误'));
                }

                $withdrawal_arr = array(
                    'status' => $this->model::CASH_STATUS_2,
                    'trade_time' => date('Y-m-d H:i:s')
                );

                //补偿被取消人30元现金
                $wechat = new Wechat($info);
                $result = $wechat->cashTransfer($row['money'],$row['trade_no']);
           
                if($result['code'] != 'ok'){
                    exception($result['message']);
                }
                $res = $row->save($withdrawal_arr);
                if (!$res)exception('通过失败');
                // Db::commit();
            }  catch (\Throwable $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }
            $this->success();
        }

        return $this->view->fetch();
    }

    /**
     * 驳回
     * @param $ids
     * @return string
     * @throws DbException
     * @throws Exception
     */
    public function reject($ids = null): string
    {
        $row = $this->model->get(['id' => $ids]);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        if ($this->request->isPost()) {
            $this->token();
            $params = $this->request->post("row/a");
            if ($params) {
                Db::startTrans();
                try {
                    if(empty($params['reject_reason'])){
                        exception('请输入驳回原因');
                    }
                    if ($row['status'] != $this->model::CASH_STATUS_1) {
                        exception('只有待处理的数据能操作');
                    }
                    $arr = array('status' => $this->model::CASH_STATUS_3,'reject_reason'=>$params['reject_reason']);

                    $user_info = (new User)->where(['id'=>$row['user_id']])->lock(true)->find();
                    (new UserRedEnvelopeBalance)->generate($user_info, Dict::USER_RED_BALANCE_3, $row['money']);
                    $res = $row->save($arr);
                    if(!$res)exception('驳回失败');
                    Db::commit();
                } catch (\Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                $this->success();
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

}
