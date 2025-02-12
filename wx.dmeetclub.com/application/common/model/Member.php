<?php

namespace app\common\model;

use app\common\library\Dict;
use think\Model;

/**
 * 会员管理
 */
class Member Extends Model
{

    protected $name = 'member';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = false;
    // 追加属性
    protected $append = [
    ];

    protected $insert = [
        "is_delete" => Dict::IS_FALSE
    ];

    /**
     * 支付
     * @param User $user
     * @param $data
     * @return array
     * @throws \think\exception\DbException
     */
    public function pay(User $user, $data)
    {
        //查询套餐是否存在
        $member = $this->get([
            "id"        => $data['id'],
            "is_delete" => Dict::IS_FALSE,
        ]);
        if(!$member) {
            throw new \Exception("您购买的套餐不存在");
        }

        try {
            //发起支付
            $payment = new \app\common\library\pay\Payment($user, $data['pay_type']);
            $result = $payment->buyMember($member);
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }

        return $result;
    }

}
