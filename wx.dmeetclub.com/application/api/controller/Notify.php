<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\library\Commission;
use app\common\library\Dict;
use app\common\library\Message;
use app\common\library\pay\Alipay;
use app\common\library\pay\Wechat;
use app\common\library\wechat\WxUser;
use app\common\model\BalanceLog;
use app\common\model\CreditLog;
use app\common\model\Order;
use app\common\model\Payment;
use app\common\model\ProjectFileSignment;
use app\common\model\UserWechat;
use app\index\controller\Pay;
use Exception;
use think\Db;
use think\Env;
use think\Log;
use Yansongda\Supports\Collection;

/**
 * 回调接口
 */
class Notify extends BaseApi
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    //微信
    public function wechat()
    {
        $wechat = new Wechat();
        return $wechat->notify();
    }

    /**
     * 公众号关注事件
     */
    public function message()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = "xinglangdmeet";
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        if( $tmpStr == $signature ){
            $xmltext = file_get_contents('php://input');
            libxml_disable_entity_loader(true);
            try {
                $xml = new \DOMDocument();
                $xml->loadXML($xmltext);
                $userOpenId = $xml->getElementsByTagName('FromUserName');
                $msgType = $xml->getElementsByTagName('MsgType');
                $event = $xml->getElementsByTagName('Event');
                $userOpenId = $userOpenId->item(0)->nodeValue;
                $msgType = $msgType->item(0)->nodeValue;
                $event = $event->item(0)->nodeValue;
                //关注公众号时间获取用户openid
                if(trim($event) == 'subscribe' && trim($msgType) == "event") {
                    //通过公众号openid获取用户信息
                    $accessToken = (new WxUser(Env::get('mini.wechat_appid'),Env::get('mini.wechat_appsecret')))->wechatAccessToken();
                    if(!$accessToken || $accessToken === false) {
                        echo 'success';exit;
                    }
                    $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$accessToken."&openid=".trim($userOpenId)."&lang=zh_CN";
                    $content = file_get_contents($url);
                    $data = json_decode($content, true);
                    $wechat = model('app\common\model\UserWechat')->where('unionid', $data['unionid'])->find();
                    if(!$wechat) {
                        UserWechat::create([
                            "unionid" => $data['unionid'],
                            "openid" => $data['openid'],
                            "subscribe_time" => $data['subscribe_time']
                        ]);
                    } else {
                        $wechat->allowField(true)->save([
                            "unionid" => $data['unionid'],
                            "openid" => $data['openid'],
                            "subscribe_time" => $data['subscribe_time']
                        ]);
                    }
                }
            } catch (Exception $e) {}
        }
        echo "success";exit;
    }
}
