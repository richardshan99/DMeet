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
 * 邀约推广
 *
 * @icon   fa fa-dashboard
 * @remark
 */
class Promotion extends Backend
{
    /**
     * @var \app\common\model\InvitePromotion
     */
    protected $model = null;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->model = model('app\common\model\InvitePromotion');
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
                $item->append(['pay_type_text']);
                $item->hidden(['user.active_point']);// 隐藏活跃区域
            }

            $totalBalancePrice = $this->model->where('pay_type', Dict::PAY_TYPE_BALANCE)->sum('price');
            $totalWechatPrice = $this->model->where('pay_type', Dict::PAY_TYPE_WECHAT)->sum('price');
            $result = array("total" => $list->total(), "rows" => $list->items(), 'extend' => [
                'total_price_wechat' => $totalWechatPrice,
                'total_price_balance' => $totalBalancePrice,
            ]);

            return json($result);
        }
        return $this->view->fetch();
    }

}
