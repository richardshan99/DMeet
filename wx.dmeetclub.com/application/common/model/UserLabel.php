<?php

namespace app\common\model;

use app\common\library\Dict;
use fast\Tree;
use think\Model;

/**
 * 用户标签
 */
class UserLabel Extends Model
{

    protected $name = 'user_label';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = false;
    // 追加属性
    protected  $insert = [
        "is_delete" => Dict::IS_FALSE,
        "is_filter" => Dict::IS_FALSE,
    ];

    /**
     * 首页筛选标签 -- 仅2级
     */
    public function getIndexList()
    {
        $list = $this->field('id,pid,name')->where('is_filter', Dict::IS_TRUE)->where('is_delete', Dict::IS_FALSE)->select();
        foreach($list as $key => $item)
        {
            $item->visible(['id', 'name']);
        }
        return $list;
    }

    /**
     * 首页筛选标签 -- 1、2级
     */
    public function getIndexAllList()
    {
        $list = $this->field('id,pid,name')->where('(pid = 0 and is_delete ='.Dict::IS_FALSE.') or (pid !=0 and is_filter ='.Dict::IS_TRUE.' and is_delete ='.Dict::IS_FALSE.')')->select();
        $tree = Tree::instance()->init($list);
        return $tree->getTreeArray(0);
    }

    /**
     * 用户标签列表
     */
    public function getList()
    {
        $list = $this->field('id,pid,name')->where('is_delete', Dict::IS_FALSE)->select();
        $tree = Tree::instance()->init($list);
        return $tree->getTreeArray(0);
    }
}
