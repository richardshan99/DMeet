<?php

namespace app\admin\controller\shop;

use app\admin\model\Admin;
use app\admin\model\User;
use app\common\controller\Backend;
use app\common\library\Dict;
use app\common\model\Attachment;
use app\common\model\Config as ConfigModel;
use app\common\model\ShopApply;
use Exception;
use fast\Date;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\Request;

/**
 * 门店入驻审核
 *
 * @icon   fa fa-dashboard
 * @remark
 */
class Apply extends Backend
{
    /**
     * @var \app\common\model\ShopApply
     */
    protected $model = null;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->model = model('app\common\model\ShopApply');
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
            $list = $this->model
                ->with(['category'])
                ->where($where)
                ->where('status', Dict::SHOP_APPLY_STATUS_WAIT)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->paginate($limit);

            $areaNew = model('app\common\model\AreaNew')->column('id,name');
            foreach($list as $key => $item)
            {
                $item->area = DMUserAreaNo($areaNew, $item->area_path, -3);
                $item->append(['area']);
            }

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
        /** @var ShopApply $row */
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
}
