<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\exception\BaseException;
use app\common\library\Dict;
use app\common\model\CallLog;
use app\common\model\Driver;
use app\common\model\Job;
use app\common\model\Recruit;
use app\common\model\UserCredit;
use Exception;
use think\Cache;
use think\Db;

/**
 * api基类
 */
class BaseApi extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    const JSON_SUCCESS_STATUS = 1;
    const JSON_ERROR_STATUS  = -1;

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 获取当前用户信息
     * @return mixed
     * @throws BaseException
     * @throws \think\exception\DbException
     */
    protected function getUser()
    {
        if (!$token = $this->request->header('token')) {
            throw new BaseException(['code' => -99, 'msg' => '缺少必要的参数：token']);
        }
        if (!$user = $this->auth->getUser()) {
            throw new BaseException(['code' => -99, 'msg' => '没有找到用户信息']);
        }

        if($user->status != 'normal') {
            throw new BaseException(['code' => -98, 'msg' => '用户被冻结']);
        }

         //获取当前路由
        $controller = request()->controller();
        $action = request()->action(true);

        $route =  $controller. '/'. $action;
 /*       if($user->is_improve != Dict::USER_IMPROVE_TRUE && in_array($route, $this->userImproveRoute())) {
            throw new BaseException(['code' => -1, 'msg' => '请先完善个人信息']);
        }*/
        return $user;
    }

    public function renderSuccess($data = [], $msg = '')
    {
        if(!$msg) $msg = '请求成功';

        $data = $this->null_filter($data);
        $this->success($msg, $data, self::JSON_SUCCESS_STATUS);
    }

    public function renderError($msg = '', $code = null)
    {
        if(!$msg) $msg = '请求失败';
        if(!$code) $code = self::JSON_ERROR_STATUS;
        $this->error($msg, [], $code);
    }

    protected function null_filter($data = [])
    {
        if(empty($data)) return [];

        $data = json_encode($data);
        $data = json_decode($data, 1);
        array_walk_recursive($data,function(&$item){
            if(is_null($item)) {
                $item = "";
            }
        });
        if(empty($data)) $data = [];
        return $data;
    }

    /**
     * 路由控制 -- 用户完善状态 拦截
     * @return array
     */
    protected function userImproveRoute()
    {
        return [
            "Index/follow", //首页 - 推荐 - 关注用户
            "Index/followWechat", //首页 - 关注公众号
            "Blog/publish", //首页 - 动态 - 发布
            "Blog/like", //首页 - 动态 - 点赞
            "Blog/report", //首页 - 动态 - 举报
            "Invitation/pay", //首页 - 发起邀约 - 支付
            "Invitation/suggestNewAddress", //首页 - 发起邀约 - 新地址建议
            "Call/initiate", //见面 - 发起召集
            "Call/concern", //见面 - 感兴趣
            "Activity/pay", //活动 - 支付
            "Message/send", //我的 - 消息 - 发送消息
            "User/editLabel", //我的 - 个人名片 - 编辑标签
            "User/edit", //我的 - 个人名片 - 编辑资料
            "User/editIntro", //我的 - 个人名片 - 编辑自我介绍
            "User/editAvatar", //我的 - 个人名片 - 编辑头像
            "Mycert/education", //我的 - 学历认证 - 提交审核
            "Mycert/work", //我的 -工作认证 - 提交审核
            "Mycert/realname", //我的 -实名认证 - 提交审核
            "Mymember/pay", //我的 - 会员购买 - 支付
            "Myshop/apply", //我的 - 申请入驻 - 提交
        ];
    }
}
