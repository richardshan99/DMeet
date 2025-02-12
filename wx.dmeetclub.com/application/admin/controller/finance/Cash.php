<?php

namespace app\admin\controller\finance;


use app\common\controller\Backend;
use app\common\library\Dict;
use think\Request;

/**
 * 门店提现
 *
 * @icon   fa fa-dashboard
 * @remark
 */
class Cash extends Backend
{
    /**
     * @var \app\common\model\ShopCash
     */
    protected $model = null;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->model = model('app\common\model\ShopCash');
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
            $list = $this->model->with(['user', 'shop'])
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->paginate($limit);
            foreach($list as $key => $item)
            {
                $item->hidden(['shop' => ['position']]);
                $item->visible([
                   "id", "price", "remark", "create_time", "pay_time", "status","reject_reason", "is_pay",
                   "user" => ["mobile"],
                   "shop" => ["cash_account", 'name', 'cash_type']
                ]);
                $item->append([
                    'status_text', 'is_pay_text',
                    "shop" => ['cash_type_text']
                ]);
            }
//            var_dump($list);exit;

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 审核
     * @param null $ids
     * @param null $type
     * @return string
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function audit($ids = null, $type = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }

        if (false === $this->request->isPost()) {
            $this->view->assign('row', $row);
            return $this->view->fetch($type);
        }

        $params = $this->request->post('row/a');
        $params = $this->preExcludeFields($params?:[]);

        try {
            // 提交审核
            $result = $row->audit($type, $params);
        } catch (\Exception $ex) {
            $this->error($ex->getMessage());
        }

        if (false !== $result) {
            $this->success();
        }
        $this->error("审核失败");
    }

    /**
     * 审核
     * @param null $ids
     * @param null $type
     * @return string
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function pay($ids = null, $type = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }

        if (false === $this->request->isPost()) {
            $this->view->assign('row', $row);
            return $this->view->fetch($type);
        }

        $params = $this->request->post('row/a');

        try {

            $result = $row->allowField(true)->save([
                "pay_image" => $params['pay_image'],
                "pay_time"  => time(),
                "is_pay"    => Dict::IS_TRUE
            ]);
        } catch (\Exception $ex) {
            $this->error($ex->getMessage());
        }

        if (false !== $result) {
            $this->success();
        }
        $this->error("审核失败");
    }

}
