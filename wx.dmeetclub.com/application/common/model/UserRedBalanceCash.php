<?php

namespace app\common\model;

use app\common\library\Dict;
use app\common\model\Config as ConfigModel;
use fast\Tree;
use think\Config;
use think\Db;
use think\Loader;
use think\Model;

/**
 * 用户见面红包提现
 */
class UserRedBalanceCash Extends Model
{

    protected $name = 'user_red_balance_cash';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = false;
    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    // 追加属性
    protected  $insert = [
    ];

    // 追加属性
    protected $append = [
        'status_text',
    ];

    const CASH_STATUS_1 = 1;
    const CASH_STATUS_2 = 2;
    const CASH_STATUS_3 = 3;

    public function getStatus(): array
    {
        return [
            '1' => '待处理',
            '2' => '已通过',
            '3' => '已驳回',
        ];
    }

    public function getStatusTextAttr($value, $data): string
    {
        $value = $value ? $value : ($data['status'] ?? '');
        $list = $this->getStatus();
        return $list[$value] ?? '';
    }

    /**
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo("app\common\model\User", 'user_id')->setEagerlyType(0);
    }

    /**
     * 提现
     * @param $user
     * @param $params
     * @return array
     */
    public function cashAdd($user,$params): array
    {
        Db::startTrans();
        try{
            $money = sprintf("%.2f",($params['money']));
            if(!$user->openid){
                exception('您账户有误，请联系客服');
            }
            //见面红包提现最小金额
            $cash_mix_balance = Config::get('site.cash_mix_balance');
            if($money < $cash_mix_balance){
                exception('见面红包提现'.$cash_mix_balance.'起提');
            }

            $user_info = (new User)->where(['id'=>$user->id])->lock(true)->find();
            if($user_info['red_envelope_balance'] < $money){
                exception('您的见面红包余额不足');
            }

            $add_arr = [
                'user_id' =>  $user->id,
                'money' => $money,
                'true_money' => $money,
                'openid' =>  $user->openid,
                'ratio' => 1,
                'trade_no' => getNumbers('withdrawal',10,1),
            ];

            $res = self::allowField(true)->save($add_arr);
            if(empty($res)){
                exception('提现失败');
            }
            (new UserRedEnvelopeBalance)->generate($user, Dict::USER_RED_BALANCE_2, $money);

            Db::commit();
        }catch (\Throwable $e){
            Db::rollback();
            return ['code'=>0,'msg'=>$e->getMessage()];
        }
        return ['code'=>1,'msg'=>'ok'];
    }
}
