<?php

namespace app\api\controller;

use app\common\model\UserRedBalanceCash;
use app\common\library\Dict;
use app\common\model\UserRedEnvelopeBalance;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;


/**
 * 我的 - 我的见面红包接口
 */
class MyRedEnvelopeBalance extends BaseApi
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = '*';
    /** @var \app\common\model\User $user */
    protected $user;

    public function _initialize()
    {
        parent::_initialize();

        $this->user = $this->getUser();
    }

    /**
     * 见面红包明细列表
     */
    public function list()
    {
        $list = (new UserRedEnvelopeBalance)->getList($this->user);
        $red_envelope_balance = $this->user->red_envelope_balance;
        $this->renderSuccess(compact('red_envelope_balance', 'list'));
    }

    /**
     * 添加提现
     * @return void
     */
    public function add()
    {
        $params = $this->request->post();
        $withdrawal_res = (new UserRedBalanceCash)->cashAdd($this->user,$params);
        if($withdrawal_res['code'] != 1){
            $this->error($withdrawal_res['msg']);
        }
        $this->renderSuccess([],'提现申请成功，请等待审核');
    }

    /**
     * 提现列表
     * @return void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function cashList()
    {
        $list = (new UserRedBalanceCash())
            ->field('id,true_money,status,reject_reason,create_time')
            ->where(['user_id'=>$this->user->id])
            ->order('id','desc')
            ->paginate(20);
        $this->renderSuccess($list,'返回成功');
    }
}
