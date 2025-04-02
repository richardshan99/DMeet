<?php

namespace app\common\model;

use app\common\library\Dict;
use Exception;
use PhpOffice\PhpSpreadsheet\Calculation\Database\DCount;
use think\Db;
use think\Model;

/**
 * 用户信息审核管理
 */
class UserChange Extends Model
{

    protected $name = 'user_change';
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
        "albums" => "json"
    ];

    public function getCreateTimeTextAttr($value, $row)
    {
        return $row['create_time'] ? date("Y-m-d H:i", $row['create_time']) : "";
    }

    public function getStatusTextAttr($value, $row)
    {
        return Dict::getCertStatus($row['status']);
    }

    /**
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('app\common\model\User', 'user_id')->setEagerlyType(0);
    }
    /**
     * 生成审核记录
     * @param User $user
     * @param array $params
     * @return UserChange
     */
    public function generate(User $user, $params = [])
    {
        if(!$params) return ;
         if(isset($params['avatar']) && !empty($params['avatar'])) {//头像
            $data['is_check_avatar'] = DIct::IS_TRUE;  //头像改为多图相册，第一张图片为头像           
            $albums = $params['avatar'] ?: [];
            $params['avatar'] = array_shift($albums);
            $params['albums'] = $albums ?: [];
        }

        if(isset($params['nickname']) && !empty($params['nickname'])) { //昵称
            $data['is_check_nickname'] = Dict::IS_TRUE;
        }

        if(isset($params['intro']) && !empty($params['intro'])) {   //个人介绍
            $data['is_check_intro'] = Dict::IS_TRUE;
        }

        if(isset($data)) {
           $user->allowField(true)->save($data);
        }
        
        return self::create(array_merge($params,["user_id" => $user->id]), true);        
    }

    /**
     * 审核
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
        Db::startTrans();
        try {
            //变更维护记录
            $this->allowField(true)->save(array_merge($params ?: [], [
                "audit_time" => time(),
                "status"     => Dict::CERT_STATUS_APPROVE
            ]));

            //变更用户信息
            $user = model('app\common\model\User')->get($this->user_id);
            //变更类型
            if(!empty($this->avatar)) {//头像
                $data['avatar'] = $this->avatar;
                $data['albums'] = $this->albums;
                $data['is_check_avatar'] = DIct::IS_FALSE;
            }

            if(!empty($this->nickname)) {//昵称
                //判断昵称是否唯一
                $isNicknameExists = model('app\common\model\User')->where([
                    "nickname" => $this->nickname,
                    "id"       => ['<>', $this->user_id]
                ])->find();
                if($isNicknameExists) {
                    throw new \Exception("昵称已存在，请审核驳回");
                }
                $data['nickname'] = $this->nickname;
                $data['is_check_nickname'] = Dict::IS_FALSE;
            }

            if(!empty($this->intro)) {//个人介绍
                $data['intro'] = $this->intro;
                $data['is_check_intro'] = Dict::IS_FALSE;
            }

            if(isset($data)) {
                //2024-7-16 审核通过，完善状态变为已完善
                $data['is_improve'] = Dict::USER_IMPROVE_TRUE;
                $result = $user->allowField(true)->save($data);
            }
            
            if($user['email']){
                $subject = "个人资料审核通知";
                $body = "尊敬的#".$user['nickname']."，您的资料审核已通过，<br>请登录DMeet直面 微信小程序查看。".Dict::EMAIL_TEXT;
                (new \app\common\library\NewEmail)->send($user['email'],$subject,$body);
            }
            
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            throw new \Exception($e->getMessage());
        }

        try{
            //发送站内信
            $library = new \app\common\library\Message($user);
            $library->auditApproveUserInfo($this);
        } catch (\Exception $ex) {}

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
            //更新记录
            $this->allowField(true)->save(array_merge($params ?: [], [
                "audit_time" => time(),
                "status"     => Dict::SHOP_INFO_STATUS_REJECT
            ]));

            //变更用户信息
            $user = model('app\common\model\User')->get($this->user_id);
            //变更类型
            if(!empty($this->avatar)) {//头像
                $data['is_check_avatar'] = DIct::IS_FALSE;
            }

            if(!empty($this->nickname)) {//昵称
                $data['is_check_nickname'] = Dict::IS_FALSE;
            }

            if(!empty($this->intro)) {//个人介绍
                $data['is_check_intro'] = Dict::IS_FALSE;
            }

            if(isset($data)) {
                //审核驳回变为待审核，by Ricchard
                $data['is_improve'] = Dict::USER_IMPROVE_AUDIT;
                $result = $user->allowField(true)->save($data);
            }
            
            if($user['email']){
                $subject = "个人资料审核通知";
                $body = "尊敬的#".$user['nickname']."，您的资料审核未通过，<br>原因是'".$params['reject_reason']."'，<br>请登录DMeet直面 微信小程序修改后提交。".Dict::EMAIL_TEXT;
                (new \app\common\library\NewEmail)->send($user['email'],$subject,$body);
            }
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            return "操作失败";
        }

        if($result === false) return "操作失败";
        try{
            //发送站内信
            $library = new \app\common\library\Message($user);
            $library->auditRejectUserInfo($this);
        } catch (\Exception $ex) {
        }
        return true;

    }
}
