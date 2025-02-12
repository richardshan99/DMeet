<?php

namespace app\common\model;

use think\Model;

/**
 * 公告
 */
class Notice Extends Model
{

    protected $name = 'notice';
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
        return date("Y-m-d H:i", $row['create_time']);
    }

    /**
     * 列表（分页）
     * @throws \think\exception\DbException
     */
    public function getList()
    {
        $list = $this->order('create_time', 'desc')->paginate(20);
        foreach($list as $key => $item)
        {
            $item->visible(["id", "intro"]);
            $item->append(['create_time_text']);
        }
        return $list;
    }

    /**
     * 详情
     * @param $id
     * @return bool
     * @throws \think\exception\DbException
     */
    public function getDetail($id)
    {
        $row = $this->get($id);
        if(!$row) return false;

        $row->visible(['id', 'intro', 'content']);
        $row->append(['create_time_text']);
        return $row;
    }
}
