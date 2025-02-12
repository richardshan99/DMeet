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
 * 见面邀约
 *
 * @icon   fa fa-dashboard
 * @remark
 */
class Invitation extends Backend
{
    /**
     * @var \app\common\model\Invite
     */
    protected $model = null;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->model = model('app\common\model\Invite');
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
            $list = $this->model->with(['user', 'inviteuser', 'shop'])
                ->field(
                    '(case when `invite`.`status`='.Dict::INVITE_STATUS_FINISH.' then commission else 0 end) as commission',
                )
                ->whereIn('invite.status', [
                    Dict::INVITE_STATUS_WAIT_MEET,
                    Dict::INVITE_STATUS_FINISH,
                    Dict::INVITE_STATUS_CANCEL,
                ])
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->paginate($limit);

            foreach($list as $key => $item)
            {
                $item->refund_salary = $item->invitee_cancel = $item->inviter_cancel = "0.00";
                if($item->status == Dict::INVITE_STATUS_CANCEL) {
                    $item->inviter_cancel = bcsub($item->inviter_paid, $item->inviter_refund, 2);
                    $item->invitee_cancel = bcsub($item->invitee_paid, $item->invitee_refund, 2);

                    //我付， 退单收入 = 邀请人取消金额 + 受邀人非余额的取消金额
                    if($item->pay_mode == Dict::INVITE_PAY_MODE_MY) {
                        $item->refund_salary = bcadd(
                            $item->inviter_cancel,
                            $item->deposit_pay_type == Dict::PAY_TYPE_BALANCE ?
                                $item->invitee_cancel : 0
                            , 2
                        );
                    }

                    //你付， 退单收入 = 邀请人非余额的取消金额 + 受邀人取消金额
                    if($item->pay_mode == Dict::INVITE_PAY_MODE_YOU) {
                        $item->refund_salary = bcadd(
                            $item->invitee_cancel,
                            $item->deposit_pay_type == Dict::PAY_TYPE_BALANCE ?
                                $item->inviter_cancel : 0
                            , 2
                        );
                    }

                    //AA, 退单收入 = 邀请人取消金额 + 受邀人取消金额
                    if($item->pay_mode == Dict::INVITE_PAY_MODE_HALF) {
                        $item->refund_salary = bcadd(
                            $item->invitee_cancel, $item->inviter_cancel, 2
                        );
                    }
                }

                $item->visible([
                    'id','address', 'pay_mode', 'price', 'package', 'deposit',
                    'status', 'inviter_is_verify','invitee_is_verify','inviter_sign_image','invitee_sign_image',
                    'meet_time', 'commission','inviter_cancel', 'invitee_cancel', 'refund_salary',
                    'user' => ['gender','nickname','avatar'],
                    'inviteuser' => ['gender','nickname','avatar'],
                    'shop'   => ['name', 'id']
                ]);

                $item->append([
                    'meet_time_text', 'status_text', 'pay_mode_text',
                    'user' => ['avatar_text'],
                    'inviteuser' => ['avatar_text']
                ]);
            }

            $statis = $this->model->with(['user', 'inviteuser', 'shop'])
                ->field('
                    count(`invite`.`id`) as _count, 
                    sum(`invite`.`price`) as _price, 
                    sum(
                        case 
                            when `invite`.`status`='.Dict::INVITE_STATUS_FINISH.' then `invite`.`commission` 
                            else 0 end
                         ) as _commission,
                    sum(
                        case 
                            when `invite`.`status`='.Dict::INVITE_STATUS_CANCEL.' and (`invite`.`pay_mode` ='.Dict::INVITE_PAY_MODE_MY.')
                                then (`invite`.`inviter_paid` - `invite`.`inviter_refund`) + (case when `deposit_pay_type`='.Dict::PAY_TYPE_WECHAT.' then (`invite`.`invitee_paid` - `invite`.`invitee_refund`) else 0 end)
                            when `invite`.`status`='.Dict::INVITE_STATUS_CANCEL.' and (`invite`.`pay_mode` ='.Dict::INVITE_PAY_MODE_YOU.')
                                then (`invite`.`invitee_paid` - `invite`.`invitee_refund`) + (case when `deposit_pay_type`='.Dict::PAY_TYPE_WECHAT.' then (`invite`.`inviter_paid` - `invite`.`inviter_refund`) else 0 end)
                            when `invite`.`status`='.Dict::INVITE_STATUS_CANCEL.' and (`invite`.`pay_mode` ='.Dict::INVITE_PAY_MODE_HALF.')
                                then (`invite`.`invitee_paid` - `invite`.`invitee_refund`) + (`invite`.`inviter_paid` - `invite`.`inviter_refund`)
                             else 0 end
                    ) as _cancel
                ')
                ->whereIn('invite.status', [
                    Dict::INVITE_STATUS_WAIT_MEET,
                    Dict::INVITE_STATUS_FINISH,
                    Dict::INVITE_STATUS_CANCEL,
                ])
                ->where($where)
                ->find();
            $chart = [
                ["name" => "邀约流水", "value" => $statis['_price'] ?: 0],
                ["name" => "邀约订单数", "value" => $statis['_count'] ?: 0],
                ["name" => "平台分成金额", "value" => bcsub($statis['_price'], $statis['_commission'], 2)],
                ["name" => "门店获得金额", "value" => $statis['_commission'] ?: "0.00"],
                ["name" => "退单收入", "value" => $statis['_cancel']?: "0.00"] ,
            ];
            $result = array("total" => $list->total(), "rows" => $list->items(), "extend" => ['chart' => $chart]);

            return json($result);
        }
        return $this->view->fetch();
    }

}
