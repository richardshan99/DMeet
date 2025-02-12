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
use fast\Tree;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\Request;

/**
 * 用户标签管理
 *
 * @icon   fa fa-dashboard
 * @remark
 */
class Label extends Backend
{
    /**
     * @var \app\common\model\Notice
     */
    protected $model = null;
    protected $rulelist = [];
    protected $multiFields = 'ismenu,status';

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->model = model('app\common\model\UserLabel');

        // 必须将结果集转换为数组
        $ruleList = \think\Db::name("user_label")->field('name,is_filter,id,pid')->where('is_delete', Dict::IS_FALSE)->select();
        Tree::instance()->init($ruleList);
        $this->rulelist = Tree::instance()->getTreeList(Tree::instance()->getTreeArray(0), 'name');

        $topLabel = [0 => __('None')];
        foreach ($this->rulelist as $k => &$v) {
            if ($v['pid']) {
                continue;
            }
            $topLabel[$v['id']] = $v['name'];
            unset($v['spacer']);
        }
        unset($v);
        $this->view->assign('topLabel', $topLabel);
    }

    /**
     * 查看
     */
    public function index()
    {

        if ($this->request->isAjax()) {
            $list = $this->rulelist;
            $total = count($this->rulelist);
            $result = array("total" => $total, "rows" => $list);

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
            $list = $this->model->where('id', 'in', $ids)->whereOr('pid', 'in', $ids)->select();
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

}
