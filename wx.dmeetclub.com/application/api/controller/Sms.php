<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\library\Sms as Smslib;
use app\common\model\User;
use think\Hook;

/**
 * 手机短信接口
 * 该类主要处理与手机短信相关的操作，包括发送验证码和检测验证码。
 */
class Sms extends Api
{
    // 表示该接口不需要登录即可访问
    protected $noNeedLogin = '*';
    // 表示该接口不需要权限验证即可访问
    protected $noNeedRight = '*';

    /**
     * 发送验证码
     *
     * @ApiMethod (POST)
     * @param string $mobile 手机号
     * @param string $event 事件名称
     */
    public function send()
    {
        // 获取客户端 POST 请求中的手机号
        $mobile = $this->request->post("mobile");
        // 获取客户端 POST 请求中的事件名称，默认为 'register'
        $event = $this->request->post("event") ?: 'register';

        // 验证手机号格式是否正确
        if (!$this->validateMobile($mobile)) {
            $this->error(__('手机号不正确'));
        }

        // 获取该手机号和事件的最后一次发送记录
        $last = Smslib::get($mobile, $event);
        // 检查是否发送频繁（距离上次发送时间小于 60 秒）
        if ($last && time() - $last['createtime'] < 60) {
            $this->error(__('发送频繁'));
        }

        // 统计该 IP 地址在过去一小时内的发送次数
        $ipSendTotal = $this->getIpSendTotal();
        if ($ipSendTotal >= 5) {
            $this->error(__('发送频繁'));
        }

        // 根据事件名称进行用户信息验证
        if ($event && !$this->validateUserInfoForEvent($mobile, $event)) {
            return;
        }

        // 调用 Smslib 类的 send 方法发送验证码
        $ret = Smslib::send($mobile, null, $event);
        if ($ret['code'] == 0) {
            $this->success(__('发送成功'));
        } else {
            $this->error(__($ret['msg']));
        }
    }

    /**
     * 检测验证码
     *
     * @ApiMethod (POST)
     * @param string $mobile 手机号
     * @param string $event 事件名称
     * @param string $captcha 验证码
     */
    public function check()
    {
        // 获取客户端 POST 请求中的手机号
        $mobile = $this->request->post("mobile");
        // 获取客户端 POST 请求中的事件名称，默认为 'register'
        $event = $this->request->post("event") ?: 'register';
        // 获取客户端 POST 请求中的验证码
        $captcha = $this->request->post("captcha");

        // 验证手机号格式是否正确
        if (!$this->validateMobile($mobile)) {
            $this->error(__('手机号不正确'));
        }

        // 根据事件名称进行用户信息验证
        if ($event && !$this->validateUserInfoForEvent($mobile, $event)) {
            return;
        }

        // 调用 Smslib 类的 check 方法验证验证码
        $ret = Smslib::check($mobile, $captcha, $event);
        if ($ret) {
            $this->success(__('成功'));
        } else {
            $this->error(__('验证码不正确'));
        }
    }

    /**
     * 验证手机号格式是否正确
     * @param string $mobile 手机号
     * @return bool
     */
    private function validateMobile($mobile)
    {
        return $mobile && \think\Validate::regex($mobile, "^1\d{10}$");
    }

    /**
     * 获取该 IP 地址在过去一小时内的发送次数
     * @return int
     */
    private function getIpSendTotal()
    {
        try {
            return \app\common\model\Sms::where(['ip' => $this->request->ip()])
                ->whereTime('createtime', '-1 hours')
                ->count();
        } catch (\Exception $e) {
            // 记录日志或其他异常处理
            $this->error(__('系统错误'));
            return 0;
        }
    }

    /**
     * 根据事件名称进行用户信息验证
     * @param string $mobile 手机号
     * @param string $event 事件名称
     * @return bool
     */
    private function validateUserInfoForEvent($mobile, $event)
    {
        $userinfo = User::getByMobile($mobile);
        if ($event == 'register' && $userinfo) {
            // 已被注册
            $this->error(__('已被注册'));
            return false;
        } elseif (in_array($event, ['changemobile']) && $userinfo) {
            // 被占用
            $this->error(__('已被占用'));
            return false;
        } elseif (in_array($event, ['changepwd', 'resetpwd']) && !$userinfo) {
            // 未注册
            $this->error(__('未注册'));
            return false;
        } elseif (in_array($event, ['forget_pwd']) && !$userinfo) {
            // 未注册
            $this->error(__('未注册'));
            return false;
        }
        return true;
    }
}