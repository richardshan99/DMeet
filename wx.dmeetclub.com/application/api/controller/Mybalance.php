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
 * 我的 - 我的余额接口
 */
class Mybalance extends BaseApi
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
        $list = (new UserBalance)->getList($this->user);
        $balance = $this->user->balance;
        $this->renderSuccess(compact('balance', 'list'));
    }
}
