<?php

namespace app\common\library\wechat;

use fast\Http;
use think\Cache;
use think\Env;
use think\Request;

/**
 * 微信小程序用户管理类
 * Class WxUser
 * @package app\common\library\wechat
 */
class WxUser
{
    private $appId;
    private $appSecret;

    private $error;

    /**
     * 构造方法
     * WxUser constructor.
     * @param $appId
     * @param $appSecret
     */
    public function __construct($appId, $appSecret)
    {
        $this->appId = $appId;
        $this->appSecret = $appSecret;
    }

    /**
     * 获取session_key
     * @param $code
     * @return array|mixed
     */
    public function sessionKey($code)
    {
        /**
         * code 换取 session_key
         * ​这是一个 HTTPS 接口，开发者服务器使用登录凭证 code 获取 session_key 和 openid。
         * 其中 session_key 是对用户数据进行加密签名的密钥。为了自身应用安全，session_key 不应该在网络上传输。
         */
        $url = 'https://api.weixin.qq.com/sns/jscode2session';
        $result = json_decode(curl($url, [
            'appid' => $this->appId,
            'secret' => $this->appSecret,
            'grant_type' => 'authorization_code',
            'js_code' => $code
        ]), true);
        if (isset($result['errcode'])) {
            $this->error = $result['errmsg'];
            return false;
        }
        return $result;
    }

    public function getError()
    {
        return $this->error;
    }

    /**
     * 小程序获取access_token
     *
     * @return bool|mixed
     */
    public function accessToken()
    {
        if(Cache::get('wx_access_token')) {
            return Cache::get('wx_access_token');
        }
        /**
         * 获取小程序全局唯一后台接口调用凭据（access_token）。
         * 调用绝大多数后台接口时都需使用 access_token，开发者需要进行妥善保存。
         */
        $url = 'https://api.weixin.qq.com/cgi-bin/token';
        $result = json_decode(curl($url, [
            'appid' => $this->appId,
            'secret' => $this->appSecret,
            'grant_type' => 'client_credential',
        ]), true);
        if (isset($result['errcode'])) {
            $this->error = $result['errmsg'];
            return false;
        }
        Cache::set('wx_access_token', $result['access_token'], $result['expires_in']);
        return $result['access_token'];
    }

 /**
 * 获取微信的 access_token * 
 * access_token 是调用微信 API 接口的全局唯一接口调用凭据，具有一定的有效期。
 * 该方法会优先从缓存中获取 access_token，如果缓存中不存在则向微信服务器请求新的 access_token 并进行缓存。 *
 * @return string|bool 返回获取到的 access_token，如果获取失败则返回 false
 */
    public function wechatAccessToken()
    {
        if(Cache::get('wechat_access_token')) {
            return Cache::get('wechat_access_token');
        }
        $url = 'https://api.weixin.qq.com/cgi-bin/token';
        $result = json_decode(curl($url, [
            'appid' => $this->appId,
            'secret' => $this->appSecret,
            'grant_type' => 'client_credential',
        ]), true);
        if (isset($result['errcode'])) {
            $this->error = $result['errmsg'];
            return false;
        }
        Cache::set('wechat_access_token', $result['access_token'], $result['expires_in']);
        return $result['access_token'];
    }

/**
 * 检查消息内容是否符合微信的安全规范
 *
 * @param string $openId 用户的微信开放ID
 * @param string $content 要检查的消息内容
 * @param string $accessToken 微信的访问令牌
 * @return string|bool 如果检查成功，返回检查结果中的建议；如果检查失败，返回 false 并设置错误信息
 */
    public function checkMsg($openId, $content, $accessToken)
    {
        $param = json_encode([
            "content" => $content,
            "version" => 2,
            "scene" => 4,
            "openid" => $openId,
        ], JSON_UNESCAPED_UNICODE);
        $url = 'https://api.weixin.qq.com/wxa/msg_sec_check?access_token='.$accessToken;
        $result = json_decode(Http::post($url, $param, [
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($param)
            ]
        ]), true);
        if (isset($result['errcode']) && $result['errcode'] === 0) {
            return $result['result']['suggest'];
        }
        $this->error = $result['errmsg'];
        return false;
    }

    public function phoneNumber($post, $accessToken)
    {
        $param = json_encode(['code' => $post['phone_code']]);
        $url = 'https://api.weixin.qq.com/wxa/business/getuserphonenumber?access_token='.$accessToken;
        $result = json_decode(Http::post($url, $param, [
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($param)
            ]
        ]), true);

        if (isset($result['errcode']) && $result['errcode'] === 0) {
            return $result['phone_info'];

        }
        $this->error = $result['errmsg'];
        return false;
    }

}