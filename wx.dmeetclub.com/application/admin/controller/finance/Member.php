<?php

namespace app\admin\controller\finance;

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
 * 会员购买
 *
 * @icon   fa fa-dashboard
 * @remark
 */
class Member extends Backend
{
    /**
     * @var \app\common\model\UserMember
     */
    protected $model = null;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->model = model('app\common\model\UserMember');
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
            $list = $this->model->with(['user', 'member'])
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->paginate($limit);

            foreach($list as $key => $item)
            {
                $item->append(['pay_type_text']);
                $item->hidden(['user.active_point']);// 隐藏活跃区域
            }

            $sum = $this->model->with(['user', 'member'])->where($where)->group('pay_type')->column('sum(`user_member`.`price`) as sum_price', 'pay_type');

            $result = array("total" => $list->total(), "rows" => $list->items(), "extend" => ['total_wechat' => $sum[Dict::PAY_TYPE_WECHAT] ??0, 'total_balance' => $sum[Dict::PAY_TYPE_BALANCE] ??0]);

            return json($result);
        }
        return $this->view->fetch();
    }

}
