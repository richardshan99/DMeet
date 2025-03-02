<?php
namespace app\common\library\pay;

use app\common\library\Dict;
use app\common\model\Activity;
use app\common\model\ActivityUser;
use app\common\model\Invite;
use app\common\model\InviteCall;
use app\common\model\InviteLog;
use app\common\model\Member;
use app\common\model\Package;
use app\common\model\Payment;
use app\common\model\ProjectContractFile;
use app\common\model\ProjectStaff;
use app\common\model\ProjectUserQuestionBankLog;
use app\common\model\Shop;
use app\common\model\User;
use Psr\Http\Message\ResponseInterface;
use think\Cookie;
use think\Db;
use think\Env;
use think\Log;
use Yansongda\Pay\Pay;

class Wechat extends Base
{

    protected $config = null;

    /** @var User $user */
    protected $user;

    protected $error;

    public function __construct($user = null)
    {
        $this->config = [
            'wechat' => [
                'default' => [
                    // 必填-商户号，服务商模式下为服务商商户号
                    // 可在 https://pay.weixin.qq.com/ 账户中心->商户信息 查看
                    'mch_id' => Env::get('mini.mch_id'),
                    // 必填-商户秘钥
                    // 即 API v3 密钥(32字节，形如md5值)，可在 账户中心->API安全 中设置
                    'mch_secret_key' => 'Room401402No655JialinRoadPudongN',
                    // 必填-商户私钥 字符串或路径
                    // 即 API证书 PRIVATE KEY，可在 账户中心->API安全->申请API证书 里获得
                    // 文件名形如：apiclient_key.pem
                    'mch_secret_cert' => ROOT_PATH . 'application/cert/apiclient_key.pem',
                    // 必填-商户公钥证书路径
                    // 即 API证书 CERTIFICATE，可在 账户中心->API安全->申请API证书 里获得
                    // 文件名形如：apiclient_cert.pem
                    'mch_public_cert_path' => ROOT_PATH . 'application/cert/apiclient_cert.pem',
                    // 必填-微信回调url
                    // 不能有参数，如?号，空格等，否则会无法正确回调
                    'notify_url' => request()->domain().'/api/notify/wechat',
                    // 选填-公众号 的 app_id
                    // 可在 mp.weixin.qq.com 设置与开发->基本配置->开发者ID(AppID) 查看
                    'mp_app_id' => 'wxe84c1695fdbbe643',
                    // 选填-小程序 的 app_id
                    'mini_app_id' => 'wxe84c1695fdbbe643',
                    // 选填-app 的 app_id
                    'app_id' => 'wxe84c1695fdbbe643',
                    // 选填-合单 app_id
                    'combine_app_id' => '',
                    // 选填-合单商户号
                    'combine_mch_id' => '',
                    // 选填-服务商模式下，子公众号 的 app_id
                    'sub_mp_app_id' => '',
                    // 选填-服务商模式下，子 app 的 app_id
                    'sub_app_id' => '',
                    // 选填-服务商模式下，子小程序 的 app_id
                    'sub_mini_app_id' => '',
                    // 选填-服务商模式下，子商户id
                    'sub_mch_id' => '',
                    // 选填-微信平台公钥证书路径, optional，强烈建议 php-fpm 模式下配置此参数
                    'wechat_public_cert_path' => [
                        //'45F59D4DABF31918AFCEC556D5D2C6E376675D57' => __DIR__.'/Cert/wechatPublicKey.crt',
                    ],
                    // 选填-默认为正常模式。可选为： MODE_NORMAL, MODE_SERVICE
                    'mode' => Pay::MODE_NORMAL,
                ],
            ],
            'logger' => [
                'enable' => true,
                'file' => RUNTIME_PATH . 'paylogs/wechat.log',
                'level' => 'debug', // 建议生产环境等级调整为 info，开发环境为 debug
                'type' => 'daily', // optional, 可选 daily.
                'max_file' => 30, // optional, 当 type 为 daily 时有效，默认 30 天
            ],
            'http' => [ // optional
                'timeout' => 5.0,
                'connect_timeout' => 5.0,
                // 更多配置项请参考 [Guzzle](https://guzzle-cn.readthedocs.io/zh_CN/latest/request-options.html)
            ],
        ];
        $this->user = $user;
    }


    /**
     * 参加活动
     * @param Activity $activity
     * @return \Yansongda\Supports\Collection
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    public function joinActivity(Activity $activity)
    {
        //生成财务记录
        $payment = $this->createPayment(
            $this->user,
            Dict::SERVICE_TYPE_ACTIVITY,
            Dict::PAY_TYPE_WECHAT,
            $activity->price,
            $activity
        );

        //生成微信订单
        Pay::config($this->config);
        $order = [
            'out_trade_no' => $payment->order_no,
            'description' => "参加活动:" . $activity->name,
            'amount' => [
                'total' => (int)bcmul($activity->price, 100, 0), //分
                'currency' => 'CNY',
            ],
            'payer' => [
                'openid' => $this->user->openid ?: "",
            ]
        ];

        return Pay::wechat()->mini($order);
    }

    /**
     * 邀约
     * @param InviteLog $inviteLog
     * @return bool
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    public function invitation(InviteLog $inviteLog)
    {
        //生成财务记录
        $payment = $this->createPayment(
            $this->user,
            Dict::SERVICE_TYPE_INVITATION,
            Dict::PAY_TYPE_WECHAT,
            $inviteLog->inviter_paid,
            $inviteLog
        );

        //生成微信订单
        Pay::config($this->config);
        $order = [
            'out_trade_no' => $payment->order_no,
            'description' => "发起邀约",
            'amount' => [
                'total' => (int)bcmul($inviteLog->inviter_paid, 100, 0), //分
                'currency' => 'CNY',
            ],
            'payer' => [
                'openid' => $this->user->openid ?: "",
            ]
        ];

        return Pay::wechat()->mini($order);
    }

    /**
     * 同意邀约
     * @param Invite $invite
     * @return \Yansongda\Supports\Collection
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    public function approveInvitation(Invite $invite)
    {
        //生成财务记录
        $payment = $this->createPayment(
            $this->user,
            Dict::SERVICE_TYPE_APPROVE_INVITATION,
            Dict::PAY_TYPE_WECHAT,
            $invite->invitee_paid,
            $invite
        );

        //生成微信订单
        Pay::config($this->config);
        $order = [
            'out_trade_no' => $payment->order_no,
            'description' => "同意邀约",
            'amount' => [
                'total' => (int)bcmul($invite->invitee_paid, 100, 0), //分
                'currency' => 'CNY',
            ],
            'payer' => [
                'openid' => $this->user->openid ?: "",
            ]
        ];

        return Pay::wechat()->mini($order);
    }

    /**
     * 发起召集
     * @param InviteCall $inviteCall
     * @return \Yansongda\Supports\Collection
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    public function call(InviteCall $inviteCall)
    {
        $totalPrice = bcadd($inviteCall->inviter_paid, $inviteCall->publish_fee, 2);
        //生成财务记录
        $payment = $this->createPayment(
            $this->user,
            Dict::SERVICE_TYPE_INVITE_CALL,
            Dict::PAY_TYPE_WECHAT,
            $totalPrice,
            $inviteCall
        );

        //生成微信订单
        Pay::config($this->config);
        $order = [
            'out_trade_no' => $payment->order_no,
            'description' => "发起召集",
            'amount' => [
                'total' => (int)bcmul($totalPrice, 100, 0), //分
                'currency' => 'CNY',
            ],
            'payer' => [
                'openid' => $this->user->openid ?: "",
            ]
        ];

        return Pay::wechat()->mini($order);
    }

    /**
     * 购买会员
     * @param Member $member
     * @return \Yansongda\Supports\Collection
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    public function buyMember(Member $member)
    {
        //生成财务记录
        $payment = $this->createPayment(
            $this->user,
            Dict::SERVICE_TYPE_BUY_MEMBER,
            Dict::PAY_TYPE_WECHAT,
            $member->price,
            $member
        );

        //生成微信订单
        Pay::config($this->config);
        $order = [
            'out_trade_no' => $payment->order_no,
            'description' => "购买会员",
            'amount' => [
                'total' => (int)bcmul($member->price, 100, 0), //分
                'currency' => 'CNY',
            ],
            'payer' => [
                'openid' => $this->user->openid ?: "",
            ]
        ];

        return Pay::wechat()->mini($order);
    }

    /**
     * 活动 - 退款
     * @param $payment
     * @return array|\Yansongda\Supports\Collection
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    public function refundActivity($payment, $refund)
    {
        Pay::config($this->config);
        $order = [
            'out_trade_no'  => $payment->order_no,
            'out_refund_no' => $payment->order_no.'-1',
            'amount' => [
                'refund' => (int)bcmul($refund, 100, 0), //分
                'total'  => (int)bcmul($payment->price, 100, 0), //分
                'currency' => 'CNY',
            ],
        ];

        Pay::wechat()->refund($order);
        return true;
    }

    /**
     * 邀约 - 退款
     * @param $payment
     * @return array|\Yansongda\Supports\Collection
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    public function refundInvitation($payment, $refund)
    {
        Pay::config($this->config);
        $order = [
            'out_trade_no'  => $payment->order_no,
            'out_refund_no' => $payment->order_no.'-1',
            'amount' => [
                'refund' => (int)bcmul($refund, 100, 0), //分
                'total'  => (int)bcmul($payment->price, 100, 0), //分
                'currency' => 'CNY',
            ],
        ];

        Pay::wechat()->refund($order);
        return true;
    }

    /**
     * 转账@todo 检查下转账状态
     * @param $price
     * @return bool
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    public function transfer($price)
    {
        // var_dump($this->config);die;
        Pay::config($this->config);

        $order = [
            'out_batch_no' => time().mt_rand(1111, 9999),
            'batch_name' => '转账',
            'batch_remark' => '转账',
            'total_amount' => (int)bcmul($price, 100, 0), //分
            'total_num' => 1,
            'transfer_detail_list' => [
                [
                    'out_detail_no' => time().mt_rand(1111, 9999),
                    'transfer_amount' => (int)bcmul($price, 100, 0), //分
                    'transfer_remark' => '转账',
                    'openid' => $this->user->openid ?: "",
                ],
            ],
        ];

        $result = Pay::wechat()->transfer($order)->toArray();
        return true;
    }
    
     /**
     * 转账@todo 检查下转账状态
     * @param $price
     * @return bool
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    public function cashTransfer($price,$out_batch_no)
    {
        Pay::config($this->config);

        $order = [
            'out_batch_no' => $out_batch_no,
            'batch_name' => '红包提现',
            'batch_remark' => '红包提现',
            'total_amount' => (int)bcmul($price, 100, 0), //分
            'total_num' => 1,
            'transfer_detail_list' => [
                [
                    'out_detail_no' => time().mt_rand(1111, 9999),
                    'transfer_amount' => (int)bcmul($price, 100, 0), //分
                    'transfer_remark' => '红包提现',
                    'openid' => $this->user->openid ?: "",
                    // 'user_name' => '闫嵩达'  // 明文传参即可，sdk 会自动加密
                ],
            ],
        ];

        $result = Pay::wechat()->transfer($order)->toArray();
        return $result;
    }

    /**
     * 邀约召集- 退款
     * @param Payment $payment
     * @return bool
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    public function refundInvitationCall(Payment $payment)
    {
        Pay::config($this->config);
        $order = [
            'out_trade_no'  => $payment->order_no,
            'out_refund_no' => $payment->order_no.'-1',
            'amount' => [
                'refund' => (int)bcmul($payment->price, 100, 0), //分
                'total'  => (int)bcmul($payment->price, 100, 0), //分
                'currency' => 'CNY',
            ],
        ];

        Pay::wechat()->refund($order);
        return true;
    }

    /**
     * 通知
     */
    public function notify()
    {
        Pay::config($this->config);
        // 是的，你没有看错，就是这么简单！
        $result = Pay::wechat()->callback();
        \Yansongda\Pay\Logger::info('WECHAT notify...', $result->all());
        $data = $result->all();
        if($data['event_type'] != 'TRANSACTION.SUCCESS') {
            return Pay::wechat()->success()->getBody();
        }

        $noData = $data['resource']['ciphertext'];
        Db::startTrans();
        try {
            $paymentModel = new Payment();
            $payment = $paymentModel->where('order_no', $noData['out_trade_no'])->where("is_use", -1)->find();
            if(!$payment) {
                throw new \Exception("订单不存在");
            }

            if($payment->service_type == Dict::SERVICE_TYPE_ACTIVITY) {// 活动
                $this->createActivityUser($payment);
            }
            if($payment->service_type == Dict::SERVICE_TYPE_INVITATION) {// 邀约
                $this->createInvitation($payment);
            }
            if($payment->service_type == Dict::SERVICE_TYPE_APPROVE_INVITATION) {// 同意邀约
                $this->createApproveInvitation($payment);
            }
            if($payment->service_type == Dict::SERVICE_TYPE_INVITE_CALL) {// 发起召集
                $this->createInviteCall($payment);
            }
            if($payment->service_type == Dict::SERVICE_TYPE_BUY_MEMBER) {// 会员
                $this->createMember($payment);
            }
            Db::commit();
        } catch (\Exception $ex) {
            Log::error('WECHAT notify...'.$ex->getMessage());
            Db::rollback();
        }

        return Pay::wechat()->success()->getBody();
    }

}
