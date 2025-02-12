<?php

namespace app\admin\controller\dynamic;

use app\admin\model\Admin;
use app\admin\model\User;
use app\common\controller\Backend;
use app\common\library\Dict;
use app\common\model\Attachment;
use app\common\model\Config as ConfigModel;
use app\common\model\ShopUser;
use DI\CompiledContainer;
use Exception;
use fast\Date;
use think\Db;
use think\exception\DbException;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\Request;

/**
 * 动态管理
 *
 * @icon   fa fa-dashboard
 * @remark
 */
class Management extends Backend
{
    /**
     * @var \app\common\model\Blog
     */
    protected $model = null;
    protected $shopCategoryList = null;
    protected $accountTypeList = null;
    protected $noNeedRight = ['multiapprove', 'multireject'];


    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->model = model('app\common\model\Blog');
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
                ->where('style',Dict::BLOG_STYLE_NORMAL)
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->paginate($limit);

            foreach($list as $key => $item)
            {
                $item->append(["status_text"]);
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
     * 批量通过
     *
     * @param $ids
     * @return void
     * @throws DbException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     */
    public function multiapprove($ids = null)
    {
        if (false === $this->request->isAjax()) {
            $this->error(__("Invalid parameters"));
        }
        $ids = $ids ?: $this->request->post("ids");
        if (empty($ids)) {
            $this->error(__('Parameter %s can not be empty', 'ids'));
        }

        Db::startTrans();
        try {
            $ret = $this->model->allowField(true)->save([
                "status" => Dict::BLOG_STATUS_APPROVE
            ], [
                "id"     => ["in", $ids],
                "status" => Dict::BLOG_STATUS_WAIT
            ]);

            Db::commit();
        } catch (PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($ret !== false) {
            $this->success();
        }
        $this->error(__('No rows were deleted'));
    }

    /**
     * 批量驳回
     *
     * @param $ids
     * @return void
     * @throws DbException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     */
    public function multireject($ids = null)
    {
        if (false === $this->request->isAjax()) {
            $this->error(__("Invalid parameters"));
        }
        $ids = $ids ?: $this->request->post("ids");
        if (empty($ids)) {
            $this->error(__('Parameter %s can not be empty', 'ids'));
        }

        Db::startTrans();
        try {
            $ret = $this->model->allowField(true)->save([
                "status" => Dict::BLOG_STATUS_REJECT,
                "reject_reason" => $this->request->post("reject_reason", null, 'trim')
            ], [
                "id"     => ["in", $ids],
                "status" => ['in', [Dict::BLOG_STATUS_WAIT, Dict::BLOG_STATUS_APPROVE]]
            ]);

            Db::commit();
        } catch (PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($ret !== false) {
            $this->success();
        }
        $this->error(__('No rows were deleted'));
    }

}
