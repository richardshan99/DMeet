<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\library\Dict;
use app\common\library\Invite;
use app\common\library\pay\Wechat;
use app\common\model\InviteCall;
use app\common\model\Source;
use app\common\model\User;
use app\common\model\UserChange;
use app\job\CancelOrder;
use think\Log;
use think\Queue;
use think\Cache;

/**
 * 示例接口
 */
class Demo extends Api
{

    //如果$noNeedLogin为空表示所有接口都需要登录才能请求
    //如果$noNeedRight为空表示所有接口都需要验证权限才能请求
    //如果接口已经设置无需登录,那也就无需鉴权了
    //
    // 无需登录的接口,*表示全部
    protected $noNeedLogin = ['*'];
    // 无需鉴权的接口,*表示全部
    protected $noNeedRight = ['*'];

    /**
     * 测试方法
     *
     * @ApiTitle    (测试名称)
     * @ApiSummary  (测试描述信息)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/demo/test/id/{id}/name/{name})
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams   (name="id", type="integer", required=true, description="会员ID")
     * @ApiParams   (name="name", type="string", required=true, description="用户名")
     * @ApiParams   (name="data", type="object", sample="{'user_id':'int','user_name':'string','profile':{'email':'string','age':'integer'}}", description="扩展数据")
     * @ApiReturnParams   (name="code", type="integer", required=true, sample="0")
     * @ApiReturnParams   (name="msg", type="string", required=true, sample="返回成功")
     * @ApiReturnParams   (name="data", type="object", sample="{'user_id':'int','user_name':'string','profile':{'email':'string','age':'integer'}}", description="扩展数据返回")
     * @ApiReturn   ({
         'code':'1',
         'msg':'返回成功'
        })
     */
    public function test()
    {
         $invite = \app\common\model\Invite::get(83);
         $user = User::get(4);
            $library = new Invite($user, $invite);
          var_dump(  $library->cancelOrder());die;
            die;
        $timezone = date_default_timezone_get();
 echo "当前时区为：".$timezone;die;
        Queue::push(CancelOrder::class, ['id' => 47, 'status'=>2]);die;
        var_dump( Queue::later(10, 'app\job\CancelOrder', ['id' => 2, 'status'=>3], 'order'));die;
    }

}
