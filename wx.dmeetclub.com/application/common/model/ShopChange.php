<?php

namespace app\common\model;

use app\common\library\Dict;
use Exception;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\Model;

/**
 * 门店维护数据
 */
class ShopChange Extends Model
{

    protected $name = 'shop_change';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = false;
    // 追加属性
    protected $append = [
    ];

    protected $insert = [
        "status" => Dict::SHOP_INFO_STATUS_WAIT
    ];

    protected $type = [
        "images"    => "json",
        "package1"  => "json",
        "package2"  => "json",
        "cash_account" => "json",
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
        return Dict::getShopInfoStatus($row['status']);
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
     * 提现账户格式化
     * @param $value
     * @param $row
     * @return mixed|string
     */
    public function getCashTypeTextAttr($value, $row)
    {
        return Dict::getCashType($row['cash_type']);
    }



    /**===========================
     *   RELATION
    =============================*/

    /**
     * 门店信息扩展表
     * @return \think\model\relation\HasOne
     */
    public function content()
    {
        return $this->hasOne("app\common\model\ShopChangeContent", 'shop_change_id', 'id');
    }



    /**========================
     *   EXTEND
    ==========================*/

    /**
     * 获取最新维护信息
     * 1、如果有提交记录， 则回显提交记录
     * 2、如果没有提交记录，则回显入驻信息
     * @param $shopId
     * @return array|false|\PDOStatement|string|Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getLastInfo($shopId)
    {
        $info = $this->where('shop_id', $shopId)->order('create_time', 'desc')->find();
        if(!$info) {
            $info = model('app\common\model\ShopApply')->where('shop_id', $shopId)->order('create_time', 'desc')->find();
            $info->cash_type = "";
            $info->cash_account = [
                "name" => "",
                "account" => "",
                "deposit" => "",
            ];
            $info->package1 = [
                'name' => "",
                'intro' => "",
                'price' => "",
                'service' => [],
            ];
            $info->package2 = [
                'name' => "",
                'intro' => "",
                'price' => "",
                'service' => [],
            ];
            $content = $info->content;
            $contentImages = $info->content_images;
            $info->content = new ShopChangeContent;
            $info->content->content = $content;
            $info->content->content_images = $contentImages;
        }

        $info->visible([
            'shop_name', 'address', 'business_time',
            'cash_type', 'cash_account', 'package1', 'package2', 'content'=> ['content']
        ]);
        $info->append(['thumb_text', 'images_text', 'content' => ['content_images_text']]);
        return $info;
    }

    /**
     * 提交变更
     * @param $shopId
     * @param $data
     * @return ShopChange
     * @throws \think\exception\DbException
     */
    public function submit($shopId, $data)
    {
        //判断是否有正在审核的提交
        $isExists = $this->get([
            "shop_id" => $shopId,
            "status"  => Dict::SHOP_INFO_STATUS_WAIT
        ]);
        if($isExists) {
            throw new \Exception("正在审核中，无法提交");
        }

        //变更门店审核状态
        $shop = model('app\common\model\Shop')->get($shopId);
        if($shop) {
            $shop->allowField(true)->save(['info_status' => Dict::SHOP_INFO_STATUS_WAIT, 'info_reject_reason' => null]);
        }

        return $this->together('content')->allowField(true)->save(array_merge($data, [
                "shop_id" => $shopId,
                "content" => [
                    "content"        => $data['content'],
                    "content_images" => $data['content_images'],
                ]
            ]));
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
                "status"     => Dict::SHOP_INFO_STATUS_APPROVE
            ]));

            //变更门店信息
            $shop = model('app\common\model\Shop')->get($this->shop_id);
            $result = $shop->together('content')->allowField(true)->save(array_merge(
                $this->visible([
                    'thumb', 'images', 'address', 'business_time',
                    'cash_type', 'cash_account','package1', 'package2'
                ])->toArray(),
                [
                    "info_status" => $this->status,
                    "is_new"      => Dict::IS_FALSE,
                    "name"        => $this->shop_name,
                    "content"     => [
                        "content"            => $this->content->content,
                        "content_images"     => $this->content->content_images
                    ]
                ]
            ));
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
        Db::startTrans();
        try {
            //更新记录
            $this->allowField(true)->save(array_merge($params ?: [], [
                "audit_time" => time(),
                "status"     => Dict::SHOP_INFO_STATUS_REJECT
            ]));

            //变更门店信息
            $shop = model('app\common\model\Shop')->get($this->shop_id);
            $result = $shop->allowField(true)->save([
                    "info_status" => $this->status,
                    "info_reject_reason" => $params['reject_reason'],
                ]
            );
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            return "操作失败";
        }
        if($result !== true) return "操作失败";
        return true;

    }
}
