<?php

namespace app\api\controller;

use app\common\library\Dict;
use app\common\model\CertEducation;
use app\common\model\CertWork;
use app\common\model\Shop;
use app\common\model\ShopApply;
use app\common\model\ShopBalance;
use app\common\model\ShopCash;
use app\common\model\ShopCategory;
use app\common\model\ShopChange;
use app\common\model\ShopChangeContent;
use app\common\model\ShopUser;
use app\common\model\User as UserModel;
use app\common\model\UserBalance;
use app\common\model\UserCert;
use DI\CompiledContainer;
use tests\thinkphp\library\think\cache\driver\redisTest;
use think\Config;
use think\Request;


/**
 * 我的 - 申请入驻/门店管理接口
 */
class Myshop extends BaseApi
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = '*';
    /** @var \app\common\model\User $user */
    protected $user;
    protected $shop;

    public function _initialize()
    {
        parent::_initialize();

        $this->user = $this->getUser();
        //门店情况
        $this->shop = (new Shop)->getInfoByMobile($this->user->mobile);
    }

    /**
     * 门店信息
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function info(Request $request)
    {
        $this->renderSuccess($this->shop);
    }

    /**
     * 检测入驻状态
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function checkApply(Request $request)
    {
        $this->renderSuccess((new ShopApply)->check($this->user));
    }

    /**
     * 申请入驻
     * @param Request $request
     */
    public function apply(Request $request)
    {
        //已有门店，不允许提交
        if($this->shop['has_shop'] === true) {
            $this->renderError("已有门店，无法申请");
        }

        $data = $request->post();
        $result = $this->validate($data, 'shop.apply');
        if(true !== $result) {
            // 验证失败 输出错误信息
            $this->renderError($result);
        }

        try {
            $ret = (new ShopApply)->submit($this->user, $data);
        } catch (\Exception $ex) {
            $this->renderError($ex->getMessage());
        }

        if($ret !== false) {
            $this->renderSuccess([], "申请成功，请等待审核");
        }
        $this->renderError("申请失败");
    }
    /**
     * 门店类别列表
     */
    public function category()
    {
        $list = (new ShopCategory())->getList();
        $this->renderSuccess($list);
    }

    /**
     * 门店信息管理 - 门店信息维护
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function change(Request $request)
    {
        //没有门店
        if($this->shop['has_shop'] === false) {
            $this->renderError("请申请入驻");
        }

        $ret = (new ShopChange)->getLastInfo($this->shop['shop_id']);
        $this->renderSuccess($ret);
    }

    /**
     * 门店信息管理 - 门店信息维护 - 提交
     * @param Request $request
     */
    public function submitChange(Request $request)
    {
        $data = $request->post();
        try {
            $ret = (new ShopChange)->submit($this->shop['shop_id'], $data);
        } catch (\Exception $ex) {
            $this->renderError($ex->getMessage());
        }

        if($ret !== false) {
            $this->renderSuccess([], "提交成功，请等待审核");
        }

        $this->renderError("提交失败");
    }

    /**
     * 提现
     * @param Request $request
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function cash(Request $request)
    {
        $price = $request->post('price', null, 'trim');
        $remark = $request->post('remark', null, 'trim');
        if(!$price) {
            $this->renderError("请填写提现金额");
        }

        //门店不存在
        if($this->shop['has_shop'] !== true) {
            $this->renderError("门店不存在无法操作");
        }

        $ret = (new ShopCash)->generate($this->shop, $this->user, ['price' => $price, 'remark' => $remark]);
        if($ret !== false) {
            $this->renderSuccess([], '提现成功，请等待后台审核');
        }
        $this->renderError("提现失败");
    }

    /**
     * 门店金额列表
     * @param Request $request
     * @throws \think\exception\DbException
     */
    public function balanceList(Request $request)
    {
        //门店不存在
        if($this->shop['has_shop'] !== true) {
            $this->renderError("门店不存在无法操作");
        }

        return $this->renderSuccess((new ShopBalance)->getList($this->shop));
    }

    /**
     * 门店提现列表
     * @param Request $request
     * @throws \think\exception\DbException
     */
    public function cashList(Request $request)
    {
        //门店不存在
        if($this->shop['has_shop'] !== true) {
            $this->renderError("门店不存在无法操作");
        }

        return $this->renderSuccess((new ShopCash)->getList($this->shop));
    }

    /**
     * 店员列表
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function clerkList(Request $request)
    {
        //门店不存在
        if($this->shop['has_shop'] !== true) {
            $this->renderError("门店不存在无法操作");
        }

        $this->renderSuccess((new ShopUser)->getClerkList($this->shop['shop_id']));
    }

    /**
     * 添加店员
     * @param Request $request
     * @throws \think\exception\DbException
     * @throws \app\common\exception\BaseException
     */
    public function addClerk(Request $request)
    {
        //门店不存在
        if($this->shop['has_shop'] !== true) {
            $this->renderError("门店不存在无法操作");
        }

        $data = $request->post();
        $result = $this->validate($data, 'ShopUser.add');
        if($result !== true) {
            $this->renderError($result);
        }

        //查看用户是否存在
        $user = \app\common\model\User::get(['mobile' => $data['mobile']]);
        if(!$user) {
            $this->renderError("手机号不存在，请先授权登录");
        }

        $ret = (new ShopUser)->addClerk(array_merge($data, [
            "shop_id" => $this->shop['shop_id']
        ]));
        if($ret !== false) {
            $this->renderSuccess([], "添加成功");
        }

        $this->renderError("添加失败");
    }

    /**
     * 编辑店员
     * @param Request $request
     * @throws \think\exception\DbException
     * @throws \app\common\exception\BaseException
     */
    public function editClerk(Request $request)
    {
        //门店不存在
        if($this->shop['has_shop'] !== true) {
            $this->renderError("门店不存在无法操作");
        }

        $data = $request->post();
        $result = $this->validate($data, 'ShopUser.add');
        if($result !== true) {
            $this->renderError($result);
        }

        //查看用户是否存在
        $user = \app\common\model\User::get(['mobile' => $data['mobile']]);
        if(!$user) {
            $this->renderError("手机号不存在，请先授权登录");
        }

        $row = ShopUser::get([
            'id' => $data['clerk_id'],
            'shop_id' => $this->shop['shop_id'],
            'is_delete' => Dict::IS_FALSE,
            'role'    => Dict::SHOP_USER_ROLE_CLERK
        ]);

        if(!$row) {
            $this->renderError("店员不存在");
        }

        $ret = $row->editClerk($data);
        if($ret !== false) {
            $this->renderSuccess([], "编辑成功");
        }

        $this->renderError("编辑失败");
    }

    /**
     * 删除店员
     * @param Request $request
     * @throws \app\common\exception\BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function delClerk(Request $request)
    {
        //门店不存在
        if($this->shop['has_shop'] !== true) {
            $this->renderError("门店不存在无法操作");
        }


        $row = ShopUser::get([
            'id' => $request->post('clerk_id', null, 'trim'),
            'shop_id' => $this->shop['shop_id'],
            'is_delete' => Dict::IS_FALSE,
            'role'    => Dict::SHOP_USER_ROLE_CLERK
        ]);

        if(!$row) {
            $this->renderError("店员不存在");
        }

        $ret = $row->delClerk();
        if($ret !== false) {
            $this->renderSuccess([], "删除成功");
        }

        $this->renderError("删除失败");
    }

    /**
     * 店员 -- 冻结/ 解冻
     * @param Request $request
     * @throws \think\exception\DbException
     */
    public function operateClerk(Request $request)
    {
        //门店不存在
        if($this->shop['has_shop'] !== true) {
            $this->renderError("门店不存在无法操作");
        }

        $row = ShopUser::get([
            'id' => $request->post('clerk_id', null, 'trim'),
            'shop_id' => $this->shop['shop_id'],
            'is_delete' => Dict::IS_FALSE,
            'role'    => Dict::SHOP_USER_ROLE_CLERK
        ]);
        if(!$row) {
            $this->renderError("店员不存在");
        }

        //冻结/解冻
        $ret = $row->operateClerk();
        if($ret !== false) {
            $this->renderSuccess(['status' => $row->status], "操作成功");
        }

        $this->renderError("操作失败");
    }

    /**
     * 核销
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function orderVerify(Request $request)
    {
        //门店不存在
        if($this->shop['has_shop'] !== true) {
            $this->renderError("门店不存在无法操作");
        }

        $text = $request->post('text', null, 'trim');
        parse_str($text, $data);

        //验签
        $sign = $data['sign'];
        unset($data['sign']);
        $_newText = http_build_query($data);
        $_newText = md5($_newText);
        if($_newText != $sign) {
            $this->renderError("二维码错误，无法核销");
        }

        //待见面的邀约
        $invite = model('app\common\model\Invite')->where(array_merge([
            "id" => $data['invite_id'],
            "shop_id" => $this->shop['shop_id'],
            "status" => Dict::INVITE_STATUS_WAIT_MEET
        ], $data['role'] == Dict::INVITATION_USER_TYPE_INVITER
            ? ['user_id' => $data['user_id']] : ["invite_user_id" => $data['user_id']]
        ))->find();
        if(!$invite) {
            $this->renderError("二维码错误，无法核销");
        }

        //二维码是否被核销过
        if(
            $data['role'] == Dict::INVITATION_USER_TYPE_INVITER && $invite['inviter_is_verify'] == Dict::IS_TRUE
         || $data['role'] == Dict::INVITATION_USER_TYPE_INVITEE && $invite['invitee_is_verify'] == Dict::IS_TRUE
        ) {
            $this->renderError("二维码已核销");
        }

        //核销
        $user = model('app\common\model\User')->where('id', $data['user_id'])->find();
        $library = new \app\common\library\Invite($user, $invite);

        try {
            $library->meetingVerify($data);
        } catch (\Exception $ex) {
            $this->renderError("核销失败");
        }
        $this->renderSuccess([], "核销成功");
    }

    /**
     * 订单列表
     * @param Request $request
     * @throws \think\exception\DbException
     */
    public function orderList(Request $request)
    {
        //门店不存在
        if($this->shop['has_shop'] !== true) {
            $this->renderError("门店不存在无法操作");
        }
        $shop = model('app\common\model\Shop')->get($this->shop['shop_id']);

        $data = $request->post();
        $list = (new \app\common\model\Invite)->orderList($shop, $data);
        $this->renderSuccess($list);
    }

}
