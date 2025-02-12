<?php

namespace app\api\controller;

use app\common\library\Dict;
use app\common\model\CertEducation;
use app\common\model\CertWork;
use app\common\model\User as UserModel;
use app\common\model\UserBalance;
use app\common\model\UserCert;
use think\Config;
use think\Request;


/**
 * 我的 - 我的动态接口
 */
class Mydynamic extends BaseApi
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = '*';
    /** @var \app\common\model\User $user */
    protected $user;

    public function _initialize()
    {
        parent::_initialize();

        $this->user = $this->getUser();
    }

    /**
     * 会员列表
     * @param Request $request
     * @throws \think\exception\DbException
     */
    public function list(Request $request)
    {
        $list = (new \app\common\model\Blog)->list($this->user);

        $areaNew = model('app\common\model\AreaNew')->column('id,name');

        foreach($list as $key => $item)
        {
            $item->user->birth_year = date('y', strtotime($item->user->birth));
            //地区
            $item->user->area = DMUserArea($areaNew, $item->user->area_path);
            $item->visible([
                'id', 'likes', 'status', 'reject_reason',
                'user' => [
                    "id", 'nickname', 'gender', 'birth_year', 'height', 'is_cert_education', 'is_member',
                    'is_cert_realname', 'area'
                ],
            ]);
            $item->append(['user' => ['avatar_text'], 'slice_images_text', 'slice_content', 'create_time_text', 'status_text', 'images_text']);
        }

        $this->renderSuccess($list);
    }

    /**
     * 动态详情
     * @param Request $request
     * @throws \app\common\exception\BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function detail(Request $request)
    {
        $blogId = $request->post('blog_id', null, 'trim,intval');
        $detail = (new \app\common\model\Blog)->detail([
                "blog.id"      => $blogId,
                "blog.user_id" => $this->user->id,
            ]
        );
        $this->renderSuccess($detail);
    }

    /**
     * 点赞用户列表
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function likeUser(Request $request)
    {
        $blog = model('app\common\model\Blog')
            ->where([
                "id"      => $request->post('blog_id', null , 'trim,intval'),
                "user_id" => $this->user->id,
            ])->find();

        if(!$blog) {
            $this->renderError("动态不存在");
        }

        $likes = model('app\common\model\BlogLikes')->with(['user'])
            ->where('blog_id', $blog->id)
            ->paginate(20);

        //我关注的
        $follow = model('app\common\model\UserFollow')->where('user_id', $this->user->id)->column('follow_user_id');
        //关注我的
        $fans = model('app\common\model\UserFollow')->where('follow_user_id', $this->user->id)->column('user_id');

        foreach($likes as $key => $item)
        {
            $followType = 1;
            if(in_array($item->user_id, $follow)) {
                $followType = 2;

                if(in_array($item->user_id, $fans)) {
                    $followType = 3;
                }
            }
            $item->follow_type = $followType;
            $item->visible([
                'follow_type', 'user' => [
                    "id" , "nickname", "gender", "height"
                ]
            ]);
            $item->append([
                "user" => [
                    "avatar_text", "birth_text"
                ]
            ]);
        }

        $this->renderSuccess($likes);
    }

    /**
     * 删除动态
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function del(Request $request)
    {
        $blog = model('app\common\model\Blog')
            ->where([
                "id"      => $request->post('blog_id', null , 'trim,intval'),
                "user_id" => $this->user->id,
            ])->find();

        if(!$blog) {
            $this->renderError("动态不存在");
        }

        $ret = $blog->allowField(true)->save(['is_delete' => Dict::IS_TRUE]);
        if($ret !== false) {
            $this->renderSuccess([], "删除成功");
        }

        $this->renderError("删除失败");
    }
}
