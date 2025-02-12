<?php

namespace app\common\library;

use fast\Random;
use think\Hook;

/**
 * 短信验证码类
 */
class Sms
{

    /**
     * 验证码有效时长
     * @var int
     */
    protected static $expire = 120;

    /**
     * 最大允许检测的次数
     * @var int
     */
    protected static $maxCheckNums = 10;

    /**
     * 获取最后一次手机发送的数据
     *
     * @param   int    $mobile 手机号
     * @param   string $event  事件
     * @return  Sms
     */
    public static function get($mobile, $event = 'default')
    {
        $sms = \app\common\model\Sms::
        where(['mobile' => $mobile, 'event' => $event])
            ->order('id', 'DESC')
            ->find();
        Hook::listen('sms_get', $sms, null, true);
        return $sms ? $sms : null;
    }

    /**
     * 发送验证码
     *
     * @param int $mobile 手机号
     * @param int|null $code 验证码,为空时将自动生成4位数字
     * @param string $event 事件
     * @param null $sms_code
     * @return  int[]
     */
    public static function send($mobile, int $code = null, string $event = 'default', $sms_code = null)
    {
//        $code = is_null($code) ? Random::numeric(config('captcha.length')) : $code;
        $code = is_null($code) ? 123456 : $code;
        $sms_code = is_null($sms_code) ? config('sms.template_arr')['code'] : $sms_code;
        $time = time();
        $ip = request()->ip();
        $sms = \app\common\model\Sms::create(['event' => $event, 'mobile' => $mobile, 'code' => $code, 'ip' => $ip, 'createtime' => $time]);
//        $result = self::send_sms($sms_code,$mobile,$code);
        $result = array('code'=>0);
        if ($result['code'] != 0){
            $sms->delete();
        }
        return $result;
    }

    /**
     * 发送通知
     *
     * @param   mixed  $mobile   手机号,多个以,分隔
     * @param   string $msg      消息内容
     * @param   string $template 消息模板
     * @return  boolean
     */
    public static function notice($mobile, $msg = '', $template = null)
    {
        $params = [
            'mobile'   => $mobile,
            'msg'      => $msg,
            'template' => $template
        ];
        $result = Hook::listen('sms_notice', $params, null, true);
        return $result ? true : false;
    }

    /**
     * 校验验证码
     *
     * @param   int    $mobile 手机号
     * @param   int    $code   验证码
     * @param   string $event  事件
     * @return  boolean
     */
    public static function check($mobile, $code, $event = 'default')
    {
        $time = time() - self::$expire;
        $sms = \app\common\model\Sms::where(['mobile' => $mobile, 'event' => $event])
            ->order('id', 'DESC')
            ->find();
        if ($sms) {
            if ($sms['createtime'] > $time && $sms['times'] <= self::$maxCheckNums) {
                $correct = $code == $sms['code'];
                if (!$correct) {
                    $sms->times = $sms->times + 1;
                    $sms->save();
                    return false;
                } else {
                    return true;
                }
            } else {
                // 过期则清空该手机验证码
                self::flush($mobile, $event);
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 清空指定手机号验证码
     *
     * @param   int    $mobile 手机号
     * @param   string $event  事件
     * @return  boolean
     */
    public static function flush($mobile, $event = 'default')
    {
        \app\common\model\Sms::
        where(['mobile' => $mobile, 'event' => $event])
            ->delete();
        Hook::listen('sms_flush');
        return true;
    }

    /**
     * 发送验证码
     * @param $template_id
     * @param $mobile
     * @param $content
     * @return bool|mixed
     */
    public static function send_sms($template_id,$mobile,$content)
    {
        $accesskey = config('sms.accesskey');
        $secret = config('sms.secret');
        $signId = config('sms.signId');
        $url = 'https://api.1cloudsp.com/api/v2/single_send';
        $params = array(
            'accesskey' => $accesskey,
            'secret' => $secret,
            'sign' => $signId,
            'templateId' => $template_id,
            'mobile' => $mobile,
            'content' => $content ?: '',
        );

        return json_decode(
            httpRequest(
                $url, [], 'GET', $params,  0
            ), true
        );
    }
}
