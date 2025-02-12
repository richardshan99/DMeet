<?php

namespace app\common\model;

use app\common\exception\BaseException;
use app\common\library\Dict;
use Exception;
use think\Db;
use think\Model;

/**
 * 用户动态
 */
class Blog Extends Model
{

    protected $name = 'blog';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = false;

    protected $insert = [
        "status" => Dict::BLOG_STATUS_WAIT,
        "likes"  => 0,
        "is_delete" => Dict::IS_FALSE
    ];

    protected $type = [
        "images" => 'json',
    ];

    /**
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('app\common\model\User', 'user_id')->setEagerlyType(0);
    }

    /**
     * 图片格式化
     * @param $value
     * @param $row
     * @return mixed
     */
    public function getImagesTextAttr($value, $row)
    {
        $images = json_decode($row['images'] ?: [], true);
        foreach($images as $key => &$item)
        {
            $item = cdnurl($item, true);
        }
        return $images;
    }

    /**
     * 列表 - 6张
     * @param $value
     * @param $row
     * @return array|mixed
     */
    public function getSliceImagesTextAttr($value, $row)
    {
        $images = json_decode($row['images'] ?: [], true);
        $images = array_slice($images, 0, 6);
        foreach($images as $key => &$item)
        {
            $item = cdnurl($item, true);
        }
        return $images;
    }

    /**
     * 列表 -- 100字
     * @param $value
     * @param $row
     * @return string
     */
    public function getSliceContentAttr($value, $row)
    {
        return mb_substr($row['content'], 0, 100, 'utf-8');
    }

    /**
     * 发布时间 格式化
     * @param $value
     * @param $row
     * @return false|string|null
     */
    public function getCreateTimeTextAttr($value, $row)
    {
        return $row['create_time'] ? date("Y-m-d H:i", $row['create_time']) : null;
    }

    /**
     * 状态格式化
     * @param $value
     * @param $row
     * @return mixed|string
     */
    public function getStatusTextAttr($value, $row)
    {
        return Dict::getBlogStatus($row['status']);
    }

    /**
     * 列表 -- 首页公共
     * @param array $where
     * @return Blog
     * @throws \think\exception\DbException
     */
    public function indexList($where = [])
    {
        return $this->with(['user' => ['cert']])->where(array_merge($where, [
                "blog.status"    => Dict::BLOG_STATUS_APPROVE,
                "blog.is_delete" => Dict::IS_FALSE,
            ]))->order('blog.create_time', 'desc')->paginate(20);
    }

    /**
     * 列表 -- 动态列表
     * @param User $user
     * @return Blog
     * @throws \think\exception\DbException
     */
    public function list(User $user)
    {
        return $this->with(['user' => ['cert']])->where([
            "blog.user_id"   => $user->id,
            "blog.is_delete" => Dict::IS_FALSE,
        ])->order('blog.create_time', 'desc')->paginate(20);
    }

    /**
     * 详情 -- 首页公共
     * @param $user
     * @param $blogId
     * @return array|false|\PDOStatement|string|Model
     * @throws BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function indexDetail($user, $blogId)
    {
        //能看自己或者别人已审核的
        $item = $this->with(['user' => ['cert']])->where([
            "blog.status" => Dict::CERT_STATUS_APPROVE
        ])->where('blog.id', $blogId)->find();

        if(!$item) {
            throw new BaseException(['msg' => "动态不存在"]);
        }
        $item->is_like = (new BlogLikes)->isExist($user, $item) ? Dict::IS_TRUE : Dict::IS_FALSE;
        $item->user->birth_year = date('y', strtotime($item->user->birth));

        $areaNew = model('app\common\model\AreaNew')->column('id,name');
        //地区
        $item->user->area = DMUserArea($areaNew, $item->user->area_path);

        $item->visible(['id', 'content', 'likes', 'is_like', 'user' => [
            "id", 'nickname', 'gender', 'birth_year', 'height', 'is_cert_education', 'is_member',
            'is_cert_realname', 'area'
        ],]);
        $item->append(['user' => ['avatar_text'], 'images_text', 'content', 'create_time_text']);
        return $item;
    }

    /**
     * 详情 -- 首页公共
     * @param $user
     * @param $blogId
     * @return array|false|\PDOStatement|string|Model
     * @throws BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function detail($where)
    {
        //能看自己或者别人已审核的
        $item = $this->with(['user' => ['cert']])->where($where)->find();

        if(!$item) {
            throw new BaseException(['msg' => "动态不存在"]);
        }
        $item->user->birth_year = date('y', strtotime($item->user->birth));
        $item->visible(['id', 'content', 'likes', 'user' => [
            "id", 'nickname', 'gender', 'birth_year', 'height', 'is_cert_education', 'is_member',
            'is_cert_realname'
        ],]);
        $item->append(['user' => ['avatar_text'], 'images_text', 'content', 'create_time_text']);
        return $item;
    }

    /**
     * 点赞 / 取消点赞
     * @param $user
     * @param $blogId
     * @return bool
     * @throws BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function like($user, $blogId)
    {
        $blog = $this->where([
            "id"     => $blogId,
            "status" => Dict::CERT_STATUS_APPROVE,
        ])->find();
        if(!$blog) {
            throw new BaseException(['msg' => "动态不存在"]);
        }

        if($blog->user_id == $user->id) {
            throw new BaseException(['msg' => "不能给自己发布的动态点赞"]);
        }

        $like = (new BlogLikes)->isExist($user, $blog);
        if($like !== false) { //存在点赞记录，取消点赞
            $result = $like->remove();
        } else { //未点赞过，新增点赞
            $result = (new BlogLikes)->generate($user, $blog);

            //站内信
            try {
                $blogImages = $blog->images ?:[];
                MessageLikes::create([
                    "user_id" => $blog->user_id,
                    "like_user_id" => $user->id,
                    "content" => "",
                    "image" => array_shift($blogImages),
                    "like_blog_id" => $blogId,
                ], true);
            }catch (\Exception $ex) {

            }
        }

        return $result !== false ? true : false;
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
            $result = $this->allowField(true)->save([
                "status"     => Dict::BLOG_STATUS_APPROVE,
                "style_level" => $params['style_level'] ?? null
            ]);

            if($this->style == Dict::BLOG_STYLE_INVITATION_MEET) { //见面分享，发放奖励
                $award = $params['style_level'] == 2 ? \think\Config::get('site.share_feature_award') : \think\Config::get('site.share_normal_reward');
                (new UserBalance)->generate(User::get($this->user_id), Dict::USER_BALANCE_TYPE_INVITE_SHARE_INCR, $award);
            }

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
     * @throws Exception
     */
    public function auditReject($params)
    {
        Db::startTrans();
        try {
            //更新记录
            $result = $this->allowField(true)->save(array_merge($params ?: [], [
                "status"     => Dict::BLOG_STATUS_REJECT
            ]));

            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            throw new \Exception($e->getMessage());
        }
        return $result;

    }
    
     /**
     * 签到同步分享
     * @param $invite
     * @param $userId
     * @param $inviteeUser
     * @param $inviterIsShare
     * @param $inviteeIsShare
     * @return void
     * @throws \think\Exception
     * @throws DbException
     */
    public function synchronousShare($invite,$userId,$inviteeUser,$inviterIsShare,$inviteeIsShare)
    {
        if($inviterIsShare == Dict::IS_TRUE && $inviteeIsShare == Dict::IS_TRUE){
            // 使用array_rand函数随机获取数组的一个键值
            $randomKey = array_rand(Dict::BLOG_STYLE_SHARE_CONTENT);

            // 通过随机获取的键值获取对应的数组元素
            $content = Dict::BLOG_STYLE_SHARE_CONTENT[$randomKey];
            $ret = Blog::create([
                "content" => $content,
                "images"  => [

                ],
                "user_id" => $userId,
                "style"   => Dict::BLOG_STYLE_SHARE,
                "invite_id" => $invite->id,
                "style_level" => 1
            ]);

            if(!$ret) {
                throw new \Exception("同步动态分享失败");
            }
            $ret->save([ "status"=> Dict::BLOG_STATUS_APPROVE]);
        }

    }
}
