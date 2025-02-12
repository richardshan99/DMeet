<?php

namespace app\common\model;

use app\common\exception\BaseException;
use app\common\library\Dict;
use think\Model;

/**
 * 用户动态 --点赞榜
 */
class BlogLikes Extends Model
{

    protected $name = 'blog_likes';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = false;

    protected $insert = [

    ];

    protected $type = [

    ];

    public function user()
    {
        return $this->belongsTo('app\common\model\User', 'user_id')->setEagerlyType(0);
    }

    /**
     * 用户是否点赞某动态
     * @param $user
     * @param $blog
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function isExist($user, $blog)
    {
        $like = $this->where([
            'user_id' => $user->id,
            'blog_id' => $blog->id,
        ])->find();

        return $like ?: false;
    }

    /**
     * 用户点赞动态
     * @return int
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function remove()
    {
        $blog = Blog::get($this->blog_id);
        $blog->likes < 1 || $blog->setDec('likes');
        return self::delete();
    }

    /**
     * 用户点赞动态
     * @param $user
     * @param $blog
     * @return BlogLikes
     */
    public function generate($user, $blog)
    {
        $blog->setInc('likes');

        return self::create([
            "user_id" => $user->id,
            "blog_id" => $blog->id,
        ], true);
    }

}
