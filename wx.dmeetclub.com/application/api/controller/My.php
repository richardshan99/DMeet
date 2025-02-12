<?php

namespace app\api\controller;

use app\common\library\Dict;
use app\common\model\CertEducation;
use app\common\model\CertWork;
use app\common\model\User as UserModel;
use app\common\model\UserCert;
use think\Request;


/**
 * 我的 - 身份认证接口
 */
class My extends BaseApi
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
     * 我的关注列表
     */
    public function followList()
    {
        $list = model('app\common\model\UserFollow')->with(['followuser'])
            ->where('user_follow.user_id', $this->user->id)
            ->paginate(20);

        $areaNew = model('app\common\model\AreaNew')->column('id,name');
        //关注我的
        $fans = model('app\common\model\UserFollow')->where('follow_user_id', $this->user->id)->column('user_id');

        foreach($list as $key => $item)
        {
            $followType = 2;
            if(in_array($item->user_id, $fans)) {
                $followType = 3;
            }
            $item->follow_type = $followType;
            $item->followuser->area = DMUserArea($areaNew, $item->user->area_path);
            $item->visible([
                "user_id", "follow_user_id", "follow_type", "followuser" => ['id', 'nickname', 'gender', 'height']
            ]);
            $item->append([
                "followuser" => ['avatar_text', 'birth_text', 'area']
            ]);
        }
        $this->renderSuccess($list);

    }

    /**
     * 关注我的（我的粉丝）列表
     */
    public function fansList()
    {
        //更新我的查看时间
        model("app\common\model\User")->allowField(true)->save(['last_fans_time' => time()], ['id' => $this->user->id]);

        $list = model('app\common\model\UserFollow')->with(['user'])
            ->where('user_follow.follow_user_id', $this->user->id)
            ->paginate(20);

        $areaNew = model('app\common\model\AreaNew')->column('id,name');
        //我关注的
        $follow = model('app\common\model\UserFollow')->where('user_id', $this->user->id)->column('follow_user_id');

        foreach($list as $key => $item)
        {
            $followType = 1;
            if(in_array($item->user_id, $follow)) {
                $followType = 3;
            }
            $item->follow_type = $followType;
            $item->user->area = DMUserArea($areaNew, $item->user->area_path);
            $item->visible([
                "user_id", "follow_user_id", "follow_type", "user" => ['id', 'nickname', 'gender', 'height']
            ]);


            $item->append([
                "user" => ['avatar_text', 'birth_text', 'area']
            ]);
        }

        $this->renderSuccess($list);
    }
}
