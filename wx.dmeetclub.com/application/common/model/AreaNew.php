<?php

namespace app\common\model;

use app\common\exception\BaseException;
use app\common\library\Dict;
use think\Model;

/**
 * 全球城市
 */
class AreaNew Extends Model
{

    protected $name = 'location';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = false;
    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;

    /**
     * 获取全省列表
     * @param int $pid
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getChinaList()
    {
        $list = $this->where(['pid'=>7,'status'=>'1'])->order(['sort'=>'desc'])->select();
        foreach ($list as $key => $item)
        {
            $item->visible(['id', 'pid', 'path', 'name', 'level']);
        }

        return $list;
    }

    /**
     * 海外国家列表
     * @param int $pid
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getOverseaList()
    {
        $list = $this->where(['level'=>2,'status'=>'1'])->where('id', "<>", 7)->order(['sort'=>'desc'])->select();
        foreach ($list as $key => $item)
        {
            $item->visible(['id', 'pid', 'path', 'name', 'level']);
        }

        return $list;
    }

    /**
     * 下级列表
     * @param int $pid
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getAreaList($pid = -1)
    {
        $list = $this->where(['pid'=>$pid,'status'=>1])->order(['sort'=>'desc'])->select();
        foreach ($list as $key => $item)
        {
            $item->visible(['id', 'pid', 'path', 'name', 'level']);
        }

        return $list;
    }
}
