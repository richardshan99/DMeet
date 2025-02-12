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
class Mycert extends BaseApi
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
     * 身份认证
     *
     * @ApiMethod (POST)
     * @param Request $request
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function realname(Request $request)
    {
        $user = $this->getUser();

        $data = $request->post(null,'','trim,strip_tags');
        $result = $this->validate($data, 'UserCert.realname');
        if(true !== $result) {
            // 验证失败 输出错误信息
            $this->renderError($result);
        }

        $ret = (new UserCert)->realname($user, $data);
        if($ret !== false) {
            $this->renderSuccess([], "认证成功");
        }
        $this->renderError("认证失败");
    }

    /**
     * 教育认证
     *
     * @ApiMethod (POST)
     * @param Request $request
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function education(Request $request)
    {
        $user = $this->getUser();

        $data = $request->post(null,'','trim,strip_tags');
        $result = $this->validate($data, 'UserCert.education');
        if(true !== $result) {
            // 验证失败 输出错误信息
            $this->renderError($result);
        }

        try {
            $ret = (new CertEducation)->submit($user, $data);
        } catch (\Exception $ex ) {
            $this->renderError($ex->getMessage());
        }

        if($ret !== false) {
            $this->renderSuccess([], "提交成功,请等待审核");
        }
        $this->renderError("提交失败");
    }

    /**
     * 工作认证
     *
     * @ApiMethod (POST)
     * @param Request $request
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function work(Request $request)
    {
        $user = $this->getUser();

        $data = $request->post(null,'','trim,strip_tags');
        $result = $this->validate($data, 'UserCert.work');
        if(true !== $result) {
            // 验证失败 输出错误信息
            $this->renderError($result);
        }

        try {
            $ret = (new CertWork)->submit($user, $data);
        } catch (\Exception $ex ) {
            $this->renderError($ex->getMessage());
        }

        if($ret !== false) {
            $this->renderSuccess([], "提交成功,请等待审核");
        }
        $this->renderError("提交失败");
    }

    /**
     * 工作认证 - 行业列表
     */
    public function workTradeList()
    {
        $list = Dict::getCertWorkTradeTypeList();
        $assoc = [];
        foreach($list as $key => $item)
        {
            array_push($assoc, ['id' => $key, 'name' => $item]);
        }

        $this->renderSuccess($assoc);
    }
}
