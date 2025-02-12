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
 * 消息管理
 *
 * @icon   fa fa-dashboard
 * @remark
 */
class Message extends Backend
{
    /**
     * @var \app\common\model\Message
     */
    protected $model = null;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->model = model('app\common\model\Chat');
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
            $list = $this->model->with(['fromuser', 'touser'])
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->paginate($limit);
            foreach($list as $key => $item)
            {
                $item->hidden(['touser.active_point']);// 隐藏活跃区域
                $item->hidden(['fromuser.active_point']);// 隐藏活跃区域
            }    
            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }

}
