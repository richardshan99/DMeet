<?php

namespace app\admin\controller\dynamic;

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
 * 动态举报管理
 *
 * @icon   fa fa-dashboard
 * @remark
 */
class Report extends Backend
{
    /**
     * @var \app\common\model\BlogReport
     */
    protected $model = null;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->model = model('app\common\model\BlogReport');
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
            $list = $this->model->with(['blog', 'user'])
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->paginate($limit);

            foreach($list as $key => $item)
            {
                $item->hidden(['user.active_point']);// 隐藏活跃区域
                $item->append(["is_handle_text"]);
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
    public function audit($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }

        if (false === $this->request->isPost()) {
            $this->error("拒绝访问");
        }

        try {
            // 提交审核
            $result = $row->allowField(true)->save(['is_handle' => Dict::IS_TRUE]);
        } catch (\Exception $ex) {
            $this->error($ex->getMessage());
        }

        if (false !== $result) {
            $this->success();
        }
        $this->error("审核失败");
    }
}
