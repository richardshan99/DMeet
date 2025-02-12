<?php

namespace app\common\model;

use app\common\library\Dict;
use think\Model;

/**
 * 我的动态
 */
class UserDynamic Extends Model
{

    protected $name = 'user_dynamic';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = false;
    // 追加属性
    protected $append = [
    ];

    public function getCreateTimeTextAttr($value, $row)
    {
        return $row['create_time'] ? date("Y-m-d H:i", $row['create_time']) : "";
    }

    public function getList(User $user)
    {
        if($user) {
            $where['user_id'] = $user->id;
        }
        $list =  $this->where($where ?: [])->order('create_time', 'desc')->paginate(20);
        foreach($list as $key => $item)
        {
//            $item->visible(['price', 'type']);
//            $item->append(['type_text', 'create_time_text']);
        }

        return $list;
    }

}
