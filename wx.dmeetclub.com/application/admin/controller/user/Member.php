<?php

namespace app\admin\controller\user;

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
 * 会员管理
 *
 * @icon   fa fa-dashboard
 * @remark
 */
class Member extends Backend
{
    /**
     * @var \app\common\model\Member
     */
    protected $model = null;
    protected $noNeedRight = ['setting'];

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->model = model('app\common\model\Member');
    }

    /**
     * 查看
     */
    public function index()
    {

        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams(null);
            $list = $this->model
                ->where($where)
                ->where('is_delete', Dict::IS_FALSE)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->paginate($limit);
            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 添加虚拟数据
     */
    public function add()
    {
        if (false === $this->request->isPost()) {
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);
        $result = false;
        Db::startTrans();
        try {
            $result = $this->model->allowField(true)->save($params);
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
     * 编辑虚拟数据
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

        if (false === $this->request->isPost()) {
            $this->view->assign('row', $row);
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);
        $result = false;
        Db::startTrans();
        try {


            $result = $row->allowField(true)->save($params);
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
     * 删除
     * @param null $ids
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

        $count = 0;
        Db::startTrans();
        try {
            $list = $this->model->where('id', 'in', $ids)->select();
            foreach ($list as $item) {
                $count += $item->allowField(true)->save(['is_delete' => Dict::IS_TRUE]);
            }
            Db::commit();
        } catch (PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($count) {
            $this->success();
        }
        $this->error(__('No rows were updated'));
    }

    /**
     * 设置
     * @return string
     * @throws \think\Exception
     */
    public function setting()
    {
        if (false === $this->request->isPost()) {
            $row = model('app\common\model\Config')->whereIn('name', ['member_desc', 'member_invite_fee'])->column('value', 'name');
            $this->view->assign('row', $row);
            return $this->view->fetch();
        }

        $row = $this->request->post("row/a", [], 'trim');
        if ($row) {
            try {
                foreach($row as $key => $item) {
                    model('app\common\model\Config')->allowField(true)->save([
                        "value" => $item
                    ], [
                        "name"  => $key
                    ]);
                }
            } catch (Exception $e) {
                $this->error($e->getMessage());
            }

            try {
                ConfigModel::refreshFile();
            } catch (Exception $e) {
                $this->error($e->getMessage());
            }
            $this->success();
        }
        $this->error(__('Parameter %s can not be empty', ''));
    }

}
