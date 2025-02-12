<?php

namespace app\api\controller;

use app\common\model\BlogReport;
use app\common\library\Dict;
use think\Request;

/**
 * 动态接口
 */
class Blog extends BaseApi
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];
    protected $user;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);

        /** @var \app\common\model\User user */
        $this->user = $this->getUser();
    }

    /**
     * 发布动态
     * @param Request $request
     */
    public function publish(Request $request)
    {
        $data = $request->post(null, null, "htmlspecialchars");
        $result = $this->validate($data, 'Blog.publish');
        if(true !== $result){
            // 验证失败 输出错误信息
            $this->renderError($result);
        }
        if($data['content']){
            list($status,$msg) = checkContent($this->user->openid,$data['content']);
            if(!$status){
                $this->renderError($msg);
            }
        }
       
        $ret = \app\common\model\Blog::create(array_merge($data, [
            "user_id" => $this->user->id,
            "style"   => Dict::BLOG_STYLE_NORMAL
        ]),true);

        if($ret !== false) {
            $this->renderSuccess([], "发布成功");
        }

        $this->renderError("发布失败");
    }

    /**
     * 动态列表
     * @param Request $request
     * @throws \think\exception\DbException
     */
    public function list(Request $request)
    {
        $userId = $request->post('user_id', null, 'trim,intval');
        if($userId) {
            $where['user_id'] = $userId;
        }

        $style = $request->post('style', null, 'trim,intval');
        if($style) {
            $where['style'] = ['in',[Dict::BLOG_STYLE_INVITATION_MEET,Dict::BLOG_STYLE_SHARE]];
        } else {
            $where['style'] = Dict::BLOG_STYLE_NORMAL;
        }
        $list = (new \app\common\model\Blog)->indexList($where ?? []);

        //当前用户点赞的动态
        $likeBlogIds = model('app\common\model\BlogLikes')
            ->where([
                'user_id' => $this->user->id,
                'blog_id' => ['in', array_column($list->items() ?: [] , 'id')]
            ])->column('blog_id');

        $areaNew = model('app\common\model\AreaNew')->column('id,name');
       foreach($list as $key => $item)
        {
            $item->is_like = in_array($item->id, $likeBlogIds) ? Dict::IS_TRUE : Dict::IS_FALSE;
            $item->user->birth_year = date('y', strtotime($item->user->birth));
            //地区
            $item->user->area = DMUserArea($areaNew, $item->user->area_path);

            //门店id
            $item->shop_id = $item->shop_address = $item->shop_area = "";
            if($where['style'] != Dict::BLOG_STYLE_NORMAL) {
                $inviteInfo = model('app\common\model\Invite')->where('id', $item->invite_id ?: -1)->find();
                if($inviteInfo){
                    $item->shop_id = $inviteInfo['shop_id'];
                    $item->shop_address = $inviteInfo['address'];
                    $shopInfo = model('app\common\model\Shop')->where('id', $inviteInfo['shop_id'] ?: -1)->find();
                    if($shopInfo){
                        $item->shop_area = DMUserArea($areaNew, $shopInfo['area_path']);
                    }
                }

            }
            $item->visible(['id', 'likes', 'is_like', 'user' => [
                "id", 'nickname', 'gender', 'birth_year', 'height', 'is_cert_education', 'is_member',
                'is_cert_realname', 'area'
            ],'shop_id','shop_address','shop_area']);
            $item->append(['user' => ['avatar_text'], 'images_text', 'slice_images_text', 'slice_content', 'create_time_text']);
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
        $row = (new \app\common\model\Blog)->indexDetail($this->user, $blogId);
        $this->renderSuccess($row);
    }

    /**
     * 点赞 / 取消点赞
     * @param Request $request
     * @throws \app\common\exception\BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function like(Request $request)
    {
        $blogId = $request->post('blog_id', null, 'trim,intval');
        $result = (new \app\common\model\Blog)->like($this->user, $blogId);
        if($result !== false) {
            $this->renderSuccess([], "操作成功");
        }
        $this->renderError("操作失败");
    }

    /**
     * 举报
     * @param Request $request
     * @throws \app\common\exception\BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function report(Request $request)
    {
        $data = $request->post();
        $result = $this->validate($data, 'blogReport.add');
        if(true !== $result){
            // 验证失败 输出错误信息
            $this->renderError($result);
        }
        $result = (new BlogReport)->generate($this->user, $data);
        if($result !== false) {
            $this->renderSuccess([], "已收到您的举报");
        }
        $this->renderError("举报失败");

    }
}
