<?php

namespace app\api\controller;

use app\common\library\Dict;
use app\common\model\CertEducation;
use app\common\model\CertWork;
use app\common\model\Member;
use app\common\model\User as UserModel;
use app\common\model\UserCert;
use think\Config;
use think\Request;


/**
 * 我的 - 会员购买接口
 */
class Mymember extends BaseApi
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
     */
    public function list()
    {
        $list = model('app\common\model\Member')
            ->where('is_delete', Dict::IS_FALSE)
            ->order('expire', 'asc')
            ->select();

        foreach($list as $key => $item)
        {
            $item->visible(['id', 'name', 'price']);
        }

        $desc = Config::get('site.member_desc');
        $this->renderSuccess(compact('list', 'desc'));
    }

    /**
     * 支付
     * @param Request $request
     */
    public function pay(Request $request)
    {
        $data = $request->post();
        $result = $this->validate($data, 'member.pay');

        if($result !== true) {
            $this->renderError($result);
        }
        //2024-9-27补充逻辑：用户必须在完成实名认证后才可购买会员
        if($this->user->is_improve != Dict::IS_TRUE) {
            $this->renderError("请先完成实名认证");
        }

        try {
            $result = (new Member)->pay($this->user, $data);
        }catch (\Exception $ex) {
            $this->renderError($ex->getMessage());
        }

        $this->renderSuccess($result);
    }
}
