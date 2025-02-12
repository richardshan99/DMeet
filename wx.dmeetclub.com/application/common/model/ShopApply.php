<?php

namespace app\common\model;

use app\common\library\Dict;
use Exception;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\Model;

/**
 * 入驻申请数据
 */
class ShopApply Extends Model
{

    protected $name = 'shop_apply';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = false;
    // 追加属性
    protected $append = [
    ];

    protected $hidden = [
        "point"
    ];

    protected $insert = [
        "status" => Dict::SHOP_APPLY_STATUS_WAIT
    ];

    protected $type = [
        "other_images" => "json",
        "images" => "json",
        "content_images" => "json",
    ];

    public function category()
    {
        return $this->belongsTo('app\common\model\ShopCategory', 'shop_category_id')->setEagerlyType(0);
    }

    /**
     * 门店信息扩展表
     * @return \think\model\relation\HasOne
     */
    public function content()
    {
        return $this->hasOne("app\common\model\ShopApplyContent", 'shop_apply_id', 'id');
    }

    /**
     * 门店缩略图格式化
     * @param $value
     * @param $row
     * @return string
     */
    public function getThumbTextAttr($value, $row)
    {
        return $row['thumb'] ? cdnurl($row['thumb'], true) : "";
    }

    /**
     * 门店轮播图格式化
     * @param $value
     * @param $row
     * @return array|mixed
     */
    public function getImagesTextAttr($value, $row)
    {
        $images = $row['images'] ? json_decode($row['images'], true) : [];
        foreach ($images as $key => &$item)
        {
            $item = cdnurl($item, true);
        }
        return $images;
    }

    /**
     * 提交申请
     * @param User $user
     * @param $data
     * @return ShopApply
     * @throws \Exception
     */
    public function submit(User $user, $data)
    {
        //判断最新提交记录是否审核中或已通过
        $shop = $this
            ->where('user_id', $user->id)
            ->order('create_time', 'desc')
            ->find();
        if($shop && in_array($shop->status, [Dict::SHOP_APPLY_STATUS_WAIT, Dict::SHOP_APPLY_STATUS_APPROVE])) {
            throw new \Exception("您的申请正在审核或已通过，请勿重复提交");
        }

        //arepath
        $areaPath = model('app\common\model\AreaNew')->where('id', $data['area_id'])->value('path');
        $data['area_path'] = $areaPath;
        $apply = self::create(array_merge($data, [
            "user_id" => $user->id,
            "mobile"  => $user->mobile,
            "contact"  => $data['mobile'],
        ]), true);
        if(false !== $apply) {
            if(isset($data['position'])) {
                $_ia = explode(',', $data['position']);
                if(isset($_ia[0]) && isset($_ia[1])) {
                    $cord = $_ia[0].",".$_ia[1];
                    Db::execute('update DM_shop_apply set `point` = point('.$cord.') where id ='.$apply->id);
                }
            }
        }
        return true;
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
        Db::startTrans();
        try {
            $result = $this->allowField(true)->save(array_merge($params ?: [], [
                "audit_time" => time(),
                "status"     => Dict::SHOP_APPLY_STATUS_APPROVE
            ]));

            $data = $this->getData();
            //创建待完善的门店
            $shop = (new Shop)->generate(array_merge($params, $data));

            //创建店长账号
            (new ShopUser)->generateManager([
                "name"   => $data['name'],
                "mobile" => $data['mobile'],
                "shop_id" => $shop->id,
            ]);

            //回存shop_id
            $this->allowField(true)->save(["shop_id" => $shop->id]);
            Db::commit();
        } catch (\Exception $e) {
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
        Db::startTrans();
        try {
            $result = $this->allowField(true)->save(array_merge($params ?: [], [
                "audit_time" => time(),
                "status"     => Dict::SHOP_APPLY_STATUS_REJECT
            ]));
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            return "操作失败";
        }
        if($result !== true) return "操作失败";
        return true;

    }

    /**
     * 查询用户提交的入驻申请状态
     * @param User $user
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function check(User $user)
    {
        $apply = $this->where('user_id', $user->id)->order('create_time', 'desc')->find();

        return [
            "status" => $apply ? $apply->status : Dict::IS_FALSE,
            "message" => $apply ? Dict::getShopApplyStatus($apply->status) : "暂无申请记录",
            "reject" => $apply ? ($apply->status == Dict::SHOP_APPLY_STATUS_REJECT ? $apply->reject_reason : "") : "",
        ];
    }
}
