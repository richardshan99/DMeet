<?php

namespace app\common\model;

use app\common\exception\BaseException;
use app\common\library\Dict;
use Exception;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\Model;

/**
 * 门店店员数据
 */
class ShopUser Extends Model
{

    protected $name = 'shop_user';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = false;
    // 追加属性
    protected $append = [
    ];

    protected $insert = [
        "status"    => Dict::USER_NORMAL,
        "is_delete" => Dict::IS_FALSE
    ];

    protected $type = [
        "images" => "json"
    ];

    /**===========================
     *   DATA FORMAT
    =============================*/

    /**
     * 状态格式化
     * @param $value
     * @param $row
     * @return string
     */
    public function getStatusTextAttr($value, $row)
    {
        return $row['status'] == Dict::USER_NORMAL ? "正常" : '冻结';
    }



    /**========================
     *   EXTEND
    ==========================*/

    /**
     * 创建店长
     * @param $data
     * @return ShopUser
     */
    public function generateManager($data)
    {
        return self::create([
            "mobile"   => $data['mobile'],
            "name"     => $data['name'] ?? "",
            "shop_id"  => $data['shop_id'],
            "role"     => Dict::SHOP_USER_ROLE_MANAGE,
        ], true);
    }

    /**
     * 创建店员
     * @param $data
     * @return ShopUser
     * @throws BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function addClerk($data)
    {
        //判断店员是否已存在其他门店
        $shopUser = model('app\common\model\ShopUser')->where([
            'mobile'    => $data['mobile'],
            'is_delete' => Dict::IS_FALSE
        ])->find();

        if($shopUser) {
            if($shopUser->shop_id != $data['shop_id']) {
                throw new BaseException(['msg' => "手机号已存在其他门店"]);
            }

            if($shopUser->shop_id == $data['shop_id']) {
                throw new BaseException(['msg' => "店员已存在"]);
            }
        }
        return self::create([
            "mobile"   => $data['mobile'],
            "name"     => $data['name'],
            "shop_id"  => $data['shop_id'],
            "role"     => Dict::SHOP_USER_ROLE_CLERK,
        ], true);
    }

    /**
     * 获取员工列表
     * @param $shopId
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getClerkList($shopId)
    {
        $list = $this->where([
            "shop_id" => $shopId,
            "is_delete" => Dict::IS_FALSE,
            "role"    => Dict::SHOP_USER_ROLE_CLERK
        ])->select();
        foreach($list as $key => $item)
        {
            $item->visible(['id', 'name', 'mobile', 'status']);
        }

        return $list;
    }

    /**
     * 编辑店员
     * @param $data
     * @return ShopUser
     * @throws BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function editClerk($data)
    {
        //判断店员是否已存在其他门店
        $shopUser = model('app\common\model\ShopUser')->where([
            'mobile'    => $data['mobile'],
            'id'        => ['<>', $this->id],
            'is_delete' => Dict::IS_FALSE
        ])->find();

        if($shopUser) {
            throw new BaseException(['msg' => "手机号已存在其他门店"]);
        }

        return $this->allowField(true)->save($data);
    }

    /**
     * 编辑店员
     * @param $shopId
     * @param $userId
     * @return false|int
     */
    public function delClerk()
    {
        return $this->allowField(true)->save(['is_delete' => DICT::IS_TRUE]);
    }

    /**
     * 冻结/解冻店员
     * @param $shopId
     * @param $userId
     * @return false|int
     */
    public function operateClerk()
    {
        return $this->allowField(true)->save([
            'status' => ($this->status == Dict::USER_NORMAL
                ? Dict::USER_HIDDEN : Dict::USER_NORMAL)
        ]);
    }
}
