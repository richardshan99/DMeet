<?php

namespace app\common\model;

use app\common\library\Dict;
use Exception;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\Model;

/**
 * 认证审核 - 学历
 */
class CertEducation Extends Model
{

    protected $name = 'cert_education';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = false;
    // 追加属性
    protected $append = [
    ];

    protected $insert = [
        "status" => Dict::CERT_STATUS_WAIT
    ];

    protected $type = [
        "images" => "json"
    ];

    public function getStatusTextAttr($value, $row)
    {
        return Dict::getCertStatus($row['status']);
    }

    public function user()
    {
        return $this->belongsTo('app\common\model\User', 'user_id')->setEagerlyType(0);
    }

    /**
     * 提交认证
     * @param User $user
     * @param $data
     * @return CertEducation
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function submit(User $user, $data)
    {
        //判断当前用户是否重复提交
        $userCert =  model('app\common\model\UserCert')
            ->where('user_id', $user->id)
            ->find();
        if(in_array($userCert->education_status, [
            Dict::CERT_STATUS_WAIT,
            Dict::CERT_STATUS_APPROVE
        ])) {
            throw new \Exception("请勿重复提交认证");
        }

        //更新教育认证状态
        $userCert->education_status = Dict::CERT_STATUS_WAIT;
        $userCert->cert_reject_education = null;//清空驳回意见
        $userCert->save();

        //新增教育认证审核记录
        return self::create(array_merge($data, [
            "status"  => Dict::CERT_STATUS_WAIT,
            "user_id" => $user->id
        ]), true);
    }

    /**
     * 认证审核
     * @param $type
     * @param $params
     * @return bool|false|int
     * @throws Exception
     */
    public function audit($type, $params)
    {
        try {
            if($type == 'approve') { //通过
                $result =  $this->auditApprove($params);
            } elseif($type == 'reject') { //驳回
                $result =  $this->auditReject($params);
            } else {
                throw new \Exception("不支持的类型");
            }
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }


        return $result;
    }

    /**
     * 审核通过
     * @param $params
     * @return bool|string
     * @throws Exception
     */
    public function auditApprove($params)
    {
        $result = false;
        Db::startTrans();
        try {
            $result = $this->allowField(true)->save(array_merge($params ?: [], [
                "audit_time" => time(),
                "status"     => Dict::CERT_STATUS_APPROVE
            ]));

            //更新用户认证信息
            $userCert = model('app\common\model\UserCert')->where('user_id', $this->user_id)->find();
            if($userCert) {
                $userCert->allowField(true)->save([
                    "education_status" => Dict::CERT_STATUS_APPROVE,
                    "school"           => $this->school,
                    "degree"           => $this->degree,
                    "education_images" => $this->images,
                ]);
            }

            //更新用户认证状态
            $user = model('app\common\model\User')->where('id', $this->user_id)->find();
            if($user) {
                $user->allowField(true)->save(['is_cert_education' => Dict::IS_TRUE]);
            }

            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            throw new \Exception($e->getMessage());
        }

        return $result;

    }

    /**
     * 审核驳回
     * @param $params
     * @return bool|string
     */
    public function auditReject($params)
    {
        $result = false;
        Db::startTrans();
        try {
            $result = $this->allowField(true)->save(array_merge($params ?: [], [
                "audit_time" => time(),
                "status"     => Dict::CERT_STATUS_REJECT
            ]));

            //更新用户认证信息
            $userCert = model('app\common\model\UserCert')->where('user_id', $this->user_id)->find();
            if($userCert) {
                $userCert->allowField(true)->save([
                    "education_status" => Dict::CERT_STATUS_REJECT,
                    "cert_reject_education" => $this->reject_reason
                ]);
            }

            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            return "操作失败";
        }
        if($result !== true) return "操作失败";
        return true;

    }


}
