<?php

namespace app\common\model;

use app\common\exception\BaseException;
use app\common\library\Dict;
use app\common\library\Tencloud;
use DI\CompiledContainer;
use think\Model;

/**
 * 用户认证信息
 */
class UserCert Extends Model
{

    protected $name = 'user_cert';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = false;
    // 追加属性
    protected $append = [
    ];

    protected $insert = [
        "realname_status" => -1,
        "education_status" => -1,
        "work_status" => -1,
    ];

    protected $type = [
        "education_images"  => "json",
        "work_images"       => "json",
    ];

    /**
     * 实名认证（格式化）
     * @param $value
     * @param $row
     * @return mixed|string
     */
    public function getRealnameStatusTextAttr($value, $row)
    {
        return Dict::getCertStatus($row['realname_status']);
    }

    /**
     * 教育认证（格式化）
     * @param $value
     * @param $row
     * @return mixed|string
     */
    public function getEducationStatusTextAttr($value, $row)
    {
        return Dict::getCertStatus($row['education_status']);
    }

    /**
     * 工作认证（格式化）
     * @param $value
     * @param $row
     * @return mixed|string
     */
    public function getWorkStatusTextAttr($value, $row)
    {
        return Dict::getCertStatus($row['work_status']);
    }

    /**
     * 教育认证图片（格式化）
     * @param $value
     * @param $row
     * @return array
     */
    public function getEducationImagesTextAttr($value, $row)
    {
        $images =  $row['education_images'] ? json_decode($row['education_images'], true): [];
        foreach($images as $key => &$item){
            $item = cdnurl($item, true);
        }
        return $images;
    }

    /**
     * 工作认证图片（格式化）
     * @param $value
     * @param $row
     * @return array
     */
    public function getWorkImagesTextAttr($value, $row)
    {
        $images =  $row['work_images'] ? json_decode($row['work_images'], true) : [];
        foreach($images as $key => &$item){
            $item = cdnurl($item, true);
        }
        return $images;
    }

    /**
     * 实名认证
     * @param User $user
     * @param $data
     * @return false|int
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\Exception
     */
    public function realname(User $user, $data)
    {
        //20240711身份证唯一 并且大于18岁
        $birth = strtotime(substr($data['idcard'], 6, 8));
        $diff = floor(bcdiv(bcsub(time(), $birth), 365*24*3600, 2));
        if($diff < 18) {
            throw new BaseException(['msg' => "身份证未满18岁，请更换后重新提交"]);
        }

        $isExists = model('app\common\model\UserCert')->where('idcard', $data['idcard'])->find();
        if($isExists) {
            throw new BaseException(['msg' => '身份证已存在，请更换后重新提交']);
        }
        //验证姓名和身份证号
        $cloud = new Tencloud($data['name'], $data['idcard']);
        $ret = $cloud->verify();
        if($ret === false) {
            throw new BaseException(['msg' => '实名认证不通过，请重新提交']);
        }
        $data['realname_status'] = $ret == true ? 2: null;

        //用户提交认证
        $row = $this->get(['user_id' => $user->id]);
        if(!$row) {
            $row = new self;
            $data['user_id'] = $user->id;
        }

        //分析身份证，更新生日和性别
        $idc = DMAnalysisIdcard($data['idcard']);
        $user->allowField(true)->save([
            "birth"  => $idc['birth'],
            "age"    => $idc['age'],
            "gender" => $idc['gender'],
            "name"   => $data['name'],
            "is_cert_realname" => $ret == true ? Dict::IS_TRUE : Dict::IS_FALSE,
        ]);

        //渠道统计
        (new Source)->statis($user);
        return $row->allowField(['name', 'idcard', 'realname_status', 'user_id'])->save(array_merge($data));
    }

    /**
     * 教育认证
     * @param User $user
     * @param $data
     * @return false|int
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function education(User $user, $data)
    {
        //新增审核记录
        CertEducation::create(array_merge($data, [
            "status"  => Dict::CERT_STATUS_WAIT,
            "user_id" => $user->id
        ]), true);
    }

    /**
     * 工作认证
     * @param User $user
     * @param $data
     * @return false|int
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function work(User $user, $data)
    {
        //用户提交认证
        $row = $this->get(['user_id' => $user->id]);
        if(!$row) {
            $row = new self;
        }

        //扩展字段
        $data['user_id'] = $user->id;
        $data['work_images'] = $data['images'];

        //新增审核记录
        CertWork::create(array_merge($data, [
            "status" => Dict::CERT_STATUS_WAIT
        ]), true);

        return $row->allowField(['company', 'position', 'work_images', 'user_id'])->save(array_merge($data));
    }
}
