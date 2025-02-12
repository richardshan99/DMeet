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
 * 用户管理 - 用户信息审核
 *
 * @icon   fa fa-dashboard
 * @remark
 */
class Change extends Backend
{
    /**
     * @var \app\common\model\UserChange
     */
    protected $model = null;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->model = model('app\common\model\UserChange');
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
                //20240710, 头像变为多图相册， 审核时把拆出去的头像放回相册，联合审核
                $avatar = $item->albums ?: [];
                array_unshift($avatar, $item->avatar);
                $item->avatar = $avatar;
                $item->append(['status_text']);
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
}
