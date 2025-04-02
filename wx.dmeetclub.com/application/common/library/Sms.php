<?php

namespace app\common\library;

use fast\Random;
use think\Hook;

/**
 * 短信验证码类
 * 该类主要负责处理短信验证码相关的操作，包括发送验证码、校验验证码、发送通知以及清空指定手机号验证码等功能。
 */
class Sms
{
    protected static $expire = 120;     //验证码有效时长（秒）
    protected static $maxCheckNums = 10;    //最大允许检测的次数

    /**
     * 获取最后一次手机发送的数据
     * 根据手机号和事件名称查询数据库，获取最后一次发送的短信验证码记录。
     * @param   int    $mobile 手机号
     * @param   string $event  事件名称，默认为 'default'
     * @return  \app\common\model\Sms|null 返回最后一次发送的记录对象，如果没有则返回 null
     */
    public static function get($mobile, $event = 'default')
    {
        // 从数据库中查询符合条件的记录，按 id 倒序排序，取第一条记录
        $sms = \app\common\model\Sms::where(['mobile' => $mobile, 'event' => $event])
            ->order('id', 'DESC')
            ->find();
        // 触发 sms_get 钩子事件
        Hook::listen('sms_get', $sms, null, true);
        return $sms ? $sms : null;
    }

    /**
     * 发送验证码
     * 生成验证码并保存到数据库，同时调用短信发送接口发送验证码。
     *
     * @param int $mobile 手机号
     * @param int|null $code 验证码，为空时将自动生成 4 位数字
     * @param string $event 事件名称，默认为 'default'
     * @param null $sms_code 短信模板代码，为空时从配置中获取
     * @return  int[] 发送结果数组，包含 'code' 字段表示发送状态
     */
    public static function send($mobile, int $code = null, string $event = 'default', $sms_code = null)
    {
        // 如果验证码为空，则生成随机验证码 
        $code = is_null($code) ? Random::numeric(config('captcha.length')) : $code;
        // 如果短信模板代码为空，则从配置中获取
        $sms_code = is_null($sms_code) ? config('sms.template_arr')['code'] : $sms_code;

        // 获取当前时间戳和客户端 IP 地址
        $time = time();
        $ip = request()->ip();

        // 将验证码相关信息保存到数据库
        $sms = \app\common\model\Sms::create([
            'event' => $event,
            'mobile' => $mobile,
            'code' => $code,
            'ip' => $ip,
            'createtime' => $time
        ]);

        // 模拟发送短信结果，实际使用时应调用真实的发送接口
        // $result = self::send_sms($sms_code, $mobile, $code);
        $result = array('code' => 0);

        // 如果发送失败，删除刚刚保存的验证码记录
        if ($result['code'] != 0) {
            $sms->delete();
        }

        return $result;
    }

    /**
     * 发送通知
     * 触发 sms_notice 钩子事件，发送通知短信。
     *
     * @param   mixed  $mobile   手机号，多个手机号以逗号分隔
     * @param   string $msg      消息内容，默认为空
     * @param   string $template 消息模板，默认为 null
     * @return  boolean 发送是否成功
     */
    public static function notice($mobile, $msg = '', $template = null)
    {
        // 准备钩子事件的参数
        $params = [
            'mobile'   => $mobile,
            'msg'      => $msg,
            'template' => $template
        ];

        // 触发 sms_notice 钩子事件并获取结果
        $result = Hook::listen('sms_notice', $params, null, true);

        return $result ? true : false;
    }

    /**
     * 校验验证码
     * 根据手机号、验证码和事件名称，校验验证码是否正确。
     *
     * @param   int    $mobile 手机号
     * @param   int    $code   验证码
     * @param   string $event  事件名称，默认为 'default'
     * @return  boolean 验证码是否正确
     */
    public static function check($mobile, $code, $event = 'default')
    {
        // 计算验证码过期时间
        $time = time() - self::$expire;

        // 从数据库中查询符合条件的记录，按 id 倒序排序，取第一条记录
        $sms = \app\common\model\Sms::where(['mobile' => $mobile, 'event' => $event])
            ->order('id', 'DESC')
            ->find();

        if ($sms) {
            // 检查验证码是否在有效期内且未超过最大检测次数
            if ($sms['createtime'] > $time && $sms['times'] <= self::$maxCheckNums) {
                // 比较输入的验证码和数据库中的验证码是否一致
                $correct = $code == $sms['code'];
                if (!$correct) {
                    // 验证码不正确，增加检测次数并保存到数据库
                    $sms->times = $sms->times + 1;
                    $sms->save();
                    return false;
                } else {
                    return true;
                }
            } else {
                // 验证码已过期，清空该手机验证码
                self::flush($mobile, $event);
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 清空指定手机号验证码
     * 根据手机号和事件名称，删除数据库中对应的验证码记录。
     *
     * @param   int    $mobile 手机号
     * @param   string $event  事件名称，默认为 'default'
     * @return  boolean 是否删除成功
     */
    public static function flush($mobile, $event = 'default')
    {
        // 从数据库中删除符合条件的记录
        \app\common\model\Sms::where(['mobile' => $mobile, 'event' => $event])
            ->delete();

        // 触发 sms_flush 钩子事件
        Hook::listen('sms_flush');

        return true;
    }

    /**
     * 发送验证码（实际发送接口）
     * 通过 HTTP 请求调用第三方短信发送接口，发送验证码短信。
     *
     * @param $template_id 短信模板 ID
     * @param $mobile 手机号
     * @param $content 短信内容，默认为空
     * @return bool|mixed 发送结果，成功返回解析后的 JSON 数据，失败返回 false
     */
    public static function send_sms($template_id, $mobile, $content)
    {
        // 从配置中获取短信接口的访问密钥、签名 ID 等信息
        $accesskey = config('sms.accesskey');
        $secret = config('sms.secret');
        $signId = config('sms.signId');

        // 短信发送接口的 URL
        $url = 'https://api.1cloudsp.com/api/v2/single_send';

        // 准备请求参数
        $params = array(
            'accesskey' => $accesskey,
            'secret' => $secret,
            'sign' => $signId,
            'templateId' => $template_id,
            'mobile' => $mobile,
            'content' => $content ?: '',
        );

        // 发送 HTTP 请求并解析返回结果
        return json_decode(
            httpRequest(
                $url, [], 'GET', $params,  0
            ), true
        );
    }
}