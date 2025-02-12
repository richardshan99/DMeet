<?php

namespace app\admin\controller\cert;

use app\admin\model\Admin;
use app\admin\model\User;
use app\common\controller\Backend;
use app\common\library\Dict;
use app\common\model\Attachment;
use app\common\model\Config as ConfigModel;
use Exception;
use fast\Date;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\Request;

/**
 * 认证审核 - 工作
 *
 * @icon   fa fa-dashboard
 * @remark
 */
class Work extends Backend
{
    /**
     * @var \app\common\model\CertWork
     */
    protected $model = null;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->model = model('app\common\model\CertWork');
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
                ->with(['user'])
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->paginate($limit);

            foreach($list as $key => $item)
            {
                $item->append(["status_text", "trade_text"]);
                $item->hidden(['user.active_point']);// 隐藏活跃区域
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
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }

        if (false === $this->request->isPost()) {
            $this->view->assign('row', $row);
            return $this->view->fetch();
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
