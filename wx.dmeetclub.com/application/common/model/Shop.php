<?php

namespace app\common\model;

use app\common\library\Dict;
use DI\CompiledContainer;
use Exception;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\Model;

/**
 * 门店数据
 */
class Shop Extends Model
{

    protected $name = 'shop';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = false;
    // 追加属性
    protected $append = [
    ];

    protected $insert = [
        "info_status" => Dict::SHOP_INFO_STATUS_UN,
        "status"      => Dict::USER_NORMAL,
        "is_new"      => Dict::IS_TRUE,
        'balance'     => 0
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
        return $row['status'] == Dict::USER_NORMAL ? "正常" : '冻结';
    }

    /**
     * @param $value
     * @param $row
     * @return mixed
     */
    public function getThumbTextAttr($value, $row)
    {
        return $row['thumb'] ? cdnurl($row['thumb'], true) : "";
    }

    /**
     * @param $value
     * @param $row
     * @return mixed
     */
    public function getImagesTextAttr($value, $row)
    {
        $images = $row['images'] ? json_decode($row['images'], true) : [];

        foreach($images as $key => &$item)
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
     * 门店扩展信息
     * @return \think\model\relation\HasOne
     */
    public function content()
    {
        return $this->hasOne("app\common\model\ShopContent", 'shop_id');
    }
    
   
    /**
     * 门店类别
     * @return \think\model\relation\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo("app\common\model\ShopCategory", 'shop_category_id')->setEagerlyType(0);
    }


    /**========================
     *   EXTEND
     ==========================*/

    /**
     * 初始化创建门店
     * @param $data
     * @return Shop
     */
    public function generate($data)
    {
         $this->together('content')->allowField(true)->save(array_merge($data, [
                    "id"   => null,
                    "balance" => 0,
                    "name" => $data['shop_name'],
                    "images" => json_decode($data['images'], true),
                    "cash_account" => [
                        "name" => "",
                        "account" => "",
                        "deposit" => "",
                    ],
                    "content" => [
                        "content" => $data['content'],
                        "content_images" => json_decode($data['content_images'], true)
                    ]
                ]));
         return $this;
    }

    /**
     * 通过手机号获取门店信息
     * @param $mobile
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getInfoByMobile($mobile)
    {
        $has_shop = false;
        //根据手机号确认门店情况
        $shopUser = model('app\common\model\ShopUser')
            ->where("mobile", $mobile)
            ->where("status", Dict::USER_NORMAL)
            ->order('id', 'desc')
            ->find();
        if(!$shopUser) { //没有门店
            return compact('has_shop');
        }

        //查询门店信息
        $shop = $this->get($shopUser->shop_id);
        //门店名称
        $shop_name        = $shop['name'];
        //门店状态
        $shop_status      = $shop->status;
        //是否有门店
        $has_shop         = !$has_shop;
        //门店信息状态
        $shop_info_status = $shop->info_status;
        //门店信息驳回原因
        $reject_reason    = $shop->info_status == Dict::SHOP_INFO_STATUS_REJECT ? $shop->info_reject_reason : "";
        //用户角色
        $user_role        = $shopUser->role;
        //入驻时间
        $create_date      = date("Y-m-d", $shop->create_time);
        $shop_id          = $shop->id;
        $balance          = $shop->balance;
        return compact('has_shop', 'shop_name', 'shop_status', 'shop_info_status', 'reject_reason', 'user_role', 'create_date', 'shop_id', 'balance');
    }

    /**
     * 门店列表
     * @param $city
     * @param $position
     * @param array $where
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function list($city, $position, $where = [])
    {

        $lon = 117.227267;//经度
        $lat = 31.820567;//纬度
        if(isset($position) && false !== strpos($position, ',')) {
            list($lon, $lat) = explode(',', $position);
        }
        if($city) {
            $city = explode('-', $city);
            $city = array_pop($city);
            $area = model('app\common\model\AreaNew')->where('name', $city)->value('id');
            if(!$area) unset($area);
        }
        $list = model('app\common\model\Shop')
            ->field("*, (st_distance_sphere(point(".$lon.",".$lat."), `point`)/1000) as distance, ST_AsText(`point`) as point_text")
            ->where(array_merge(['status' => Dict::USER_NORMAL], $where))
            ->whereRaw(isset($area) ? "find_in_set({$area}, `area_path`)": "1=-1")
            ->orderRaw('recommend ASC,distance ASC')
            ->paginate(20);

        $category = model('app\common\model\ShopCategory')
            ->whereIn('id', array_column($list->items(), 'shop_category_id'))
            ->column('name', 'id');
        foreach($list as $key => $item)
        {
            $item->distance = round($item->distance, 2);
            $item->category_text = $category[$item->shop_category_id] ?? "";
            $item->visible(['id', 'distance', 'name', 'address']);
            $item->append(['thumb_text', 'category_text']);
        }
        return $list;
    }

    /**
     * 门店详情
     */
    public function detail()
    {
        $this->contents = $this->content->content;
        $this->content_images = $this->content->content_images_text;

        if($this->type == Dict::SHOP_TYPE_RESTAURANT  ){
            //套餐1
            $package1 = $this->package1;
            //套餐价格
            $package1['price'] = $package1['price'] ? (string)$package1['price'] : "";
            //合计价值
            $package1['service'] = $package1['service'] ?: [];
            $package1['total_price'] = (string)array_sum(array_column($package1['service'], 'price'));
            $this->package1 = $package1;

            //套餐2
            $package2 = $this->package2;
            //套餐价格
            $package2['price'] = $package2['price'] ? (string)$package2['price']: "";
            //合计价格
            $package2['service'] = $package2['service'] ?: [];
            $package2['total_price'] = (string)array_sum(array_column($package2['service'] ?: [], 'price'));
            $this->package2 = $package2; 
        }
        
        unset($this->point);
        if(false !== strpos($this->point_text,"POINT")) {
            preg_match('/\((.*?)\)/', $this->point_text, $matches);
            $value = $matches[1];
            $this->point_text = str_replace(" ",",", $value);
        } else {
            $this->point_text = null;
        }
        $this->latitude = $this->longitude = 0;
        if($this->point_text){
            $this->point_text = explode(',',$this->point_text);
            $this->longitude = $this->point_text[0];
            $this->latitude = $this->point_text[1];
        }

        $this->visible(['id', 'distance', 'name', 'address', 'business_time', 'contents', 'content_images','longitude','latitude','type']);
        $this->append(['thumb_text', 'images_text', 'package1', 'package2']);
        return $this;
    }
}
