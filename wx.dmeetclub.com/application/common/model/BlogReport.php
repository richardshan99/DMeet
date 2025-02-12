<?php

namespace app\common\model;

use app\common\exception\BaseException;
use app\common\library\Dict;
use Exception;
use think\Model;

/**
 * 用户动态 --举报
 */
class BlogReport Extends Model
{

    protected $name = 'blog_report';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = false;

    protected $insert = [
        "is_handle" => Dict::IS_FALSE
    ];

    protected $type = [
        "images" => "json"
    ];

    /**
     * @return \think\model\relation\BelongsTo
     */
    public function blog()
    {
        return $this->belongsTo('app\common\model\Blog', 'blog_id')->setEagerlyType(0);
    }

    /**
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('app\common\model\User', 'user_id')->setEagerlyType(0);
    }

    /**
     * 是否处理
     * @param $value
     * @param $row
     * @return mixed|string
     */
    public function getIsHandleTextAttr($value, $row)
    {
        return $row['is_handle'] == Dict::IS_TRUE ? '已处理' : '待处理';
    }

    /**
     * 用户点赞动态
     * @param $user
     * @param $blog
     * @param array $params
     * @return BlogReport
     */
    public function generate($user, $params = [])
    {
        if(isset($params['images']) && empty($params['images'])) $params['images'] = null;
        return self::create(array_merge($params, [
            "user_id" => $user->id,
        ]), true);
    }

}
