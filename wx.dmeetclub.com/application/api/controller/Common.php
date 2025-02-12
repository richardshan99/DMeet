<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\exception\UploadException;
use app\common\model\MeetingRedEnvelope;
use app\common\library\Dict;
use app\common\library\Upload;
use app\common\model\Area;
use app\common\model\AreaNew;
use app\common\model\UserLabel;
use app\common\model\Version;
use fast\Random;
use think\Config;
use think\Hook;
use think\Request;

/**
 * 公共接口
 */
class Common extends BaseApi
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = '*';

    /**
     * 加载初始化
     *
     * @param string $version 版本号
     * @param string $lng     经度
     * @param string $lat     纬度
     */
    public function init()
    {
        if ($version = $this->request->request('version')) {
            $lng = $this->request->request('lng');
            $lat = $this->request->request('lat');

            //配置信息
            $upload = Config::get('upload');
            //如果非服务端中转模式需要修改为中转
            if ($upload['storage'] != 'local' && isset($upload['uploadmode']) && $upload['uploadmode'] != 'server') {
                //临时修改上传模式为服务端中转
                set_addon_config($upload['storage'], ["uploadmode" => "server"], false);

                $upload = \app\common\model\Config::upload();
                // 上传信息配置后
                Hook::listen("upload_config_init", $upload);

                $upload = Config::set('upload', array_merge(Config::get('upload'), $upload));
            }

            $upload['cdnurl'] = $upload['cdnurl'] ? $upload['cdnurl'] : cdnurl('', true);
            $upload['uploadurl'] = preg_match("/^((?:[a-z]+:)?\/\/)(.*)/i", $upload['uploadurl']) ? $upload['uploadurl'] : url($upload['storage'] == 'local' ? '/api/common/upload' : $upload['uploadurl'], '', false, true);

            $content = [
                'citydata'    => Area::getCityFromLngLat($lng, $lat),
                'versiondata' => Version::check($version),
                'uploaddata'  => $upload,
                'coverdata'   => Config::get("cover"),
            ];
            $this->success('', $content);
        } else {
            $this->error(__('Invalid parameters'));
        }
    }

    /**
     * 上传文件
     * @ApiMethod (POST)
     * @param File $file 文件流
     */
    public function upload()
    {
        Config::set('default_return_type', 'json');
        //必须设定cdnurl为空,否则cdnurl函数计算错误
        Config::set('upload.cdnurl', '');
        $chunkid = $this->request->post("chunkid");
        if ($chunkid) {
            if (!Config::get('upload.chunking')) {
                $this->error(__('Chunk file disabled'));
            }
            $action = $this->request->post("action");
            $chunkindex = $this->request->post("chunkindex/d");
            $chunkcount = $this->request->post("chunkcount/d");
            $filename = $this->request->post("filename");
            $method = $this->request->method(true);
            if ($action == 'merge') {
                $attachment = null;
                //合并分片文件
                try {
                    $upload = new Upload();
                    $attachment = $upload->merge($chunkid, $chunkcount, $filename);
                } catch (UploadException $e) {
                    $this->error($e->getMessage());
                }
                $this->success(__('Uploaded successful'), ['url' => $attachment->url, 'fullurl' => cdnurl($attachment->url, true)]);
            } elseif ($method == 'clean') {
                //删除冗余的分片文件
                try {
                    $upload = new Upload();
                    $upload->clean($chunkid);
                } catch (UploadException $e) {
                    $this->error($e->getMessage());
                }
                $this->success();
            } else {
                //上传分片文件
                //默认普通上传文件
                $file = $this->request->file('file');
                try {
                    $upload = new Upload($file);
                    $upload->chunk($chunkid, $chunkindex, $chunkcount);
                } catch (UploadException $e) {
                    $this->error($e->getMessage());
                }
                $this->success();
            }
        } else {
            $attachment = null;
            //默认普通上传文件
            $file = $this->request->file('file');
            try {
                $upload = new Upload($file);
                $attachment = $upload->upload();
            } catch (UploadException $e) {
                $this->error($e->getMessage());
            }

            $this->success(__('Uploaded successful'), ['url' => $attachment->url, 'fullurl' => cdnurl($attachment->url, true)]);
        }

    }

    /**
     * 平台基本信息
     */
    public function info()
    {
        //用户协议
        $agreement = Config::get('site.agreement');
        //隐私政策
        $privacy = Config::get('site.privacy');
        //关于我们
        $aboutus = Config::get('site.aboutus');
        //帮助中心
        $help_center = Config::get('site.help_center');
        //新流程履约保证金
        $new_deposit_price = Config::get('site.new_deposit_price');
        //发布费用
        $meeting_publish_price = Config::get('site.invite_fee');
        //见面红包提现最小金额
        $cash_mix_balance = Config::get('site.cash_mix_balance');
        //套餐1价格
        $package1_price = Config::get('site.pack_one');
        //套餐2价格
        $package2_price = Config::get('site.pack_two');
        $this->renderSuccess(compact('agreement', 'privacy', 'aboutus', 'meeting_publish_price', 'package1_price', 'package2_price','help_center','new_deposit_price','cash_mix_balance'));
    }
    /**
     * 七牛
     */
    public function qiniu()
    {
        $this->getUser();
        //配置信息
        $upload = Config::get('upload');

        $upload = \app\common\model\Config::upload();
        // 上传信息配置后
        Hook::listen("upload_config_init", $upload);

        $upload = Config::set('upload', array_merge(Config::get('upload'), $upload));


        $upload['cdnurl'] = $upload['cdnurl'] ? $upload['cdnurl'] : cdnurl('', true);
        $upload['uploadurl'] = preg_match("/^((?:[a-z]+:)?\/\/)(.*)/i", $upload['uploadurl']) ? $upload['uploadurl'] : url($upload['storage'] == 'local' ? '/api/common/upload' : $upload['uploadurl'], '', false, true);

        $this->renderSuccess($upload);
    }

    /**
     * 见面公示栏
     */
    public function meetAnnounce()
    {
        $this->getUser();

        $list = model('app\common\model\Virt')
            ->order('create_time', 'desc')
            ->limit(50)
            ->select();

        foreach($list as $key => $item)
        {
            $item->visible(['id', 'content']);
            $item->append(['create_time_text']);
        }

        $this->renderSuccess($list);
    }

    /**
     * 见面分享动态
     */
    public function meetShare()
    {
        $blog = model('app\common\model\Blog')
            ->where([
                "status" => Dict::BLOG_STATUS_APPROVE
            ])
            ->whereIn("style" ,[Dict::BLOG_STYLE_INVITATION_MEET,Dict::BLOG_STYLE_SHARE])
            ->order('create_time', 'desc')
            ->find();
        if(!$blog) $this->renderSuccess([]);

        $invite = \app\common\model\Invite::get($blog->invite_id);

        $inviter = \app\common\model\User::get($invite->user_id);
        $invitee = \app\common\model\User::get($invite->invite_user_id);
        $images = $blog->images ?: [];
        $firstImage = array_shift($images);
        $blog->image_text = $firstImage ? cdnurl($firstImage, true) : "";
        $blog->inviter_image_text = $inviter->avatar_text;
        $blog->invitee_image_text = $invitee->avatar_text;
        $blog->shop_id = $invite->shop_id;
        $blog->address = $invite->address;
        $blog->visible(['content', 'shop_id','style','address']);
        $blog->append(['image_text', 'inviter_image_text', 'invitee_image_text']);
        $this->renderSuccess($blog);
    }

    /**
     * 中国 国家 - 省 - 市
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function chinaList(Request $request)
    {
        $list = (new AreaNew)->getChinaList();
        $this->renderSuccess($list);
    }

    /**
     * 海外国家
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function overseaList(Request $request)
    {
        $list = (new AreaNew)->getOverseaList();
        $this->renderSuccess($list);
    }

    /**
     * 下级id
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function areaList(Request $request)
    {
        $list = (new AreaNew)->getAreaList($request->post('pid', -1, 'trim,intval'));
        $this->renderSuccess($list);
    }

    /**
     * 学历列表
     */
    public function educationTypeList()
    {
        $this->renderSuccess(Dict::getEducationTypeListForIndex());
    }

    /**
     * 星座列表
     */
    public function constellationTypeList()
    {
        $this->renderSuccess(Dict::getConstellationTypeListForIndex());

    }

    /**
     * 工作情况列表
     */
    public function workTypeList()
    {
        $this->renderSuccess(Dict::getWorkTypeListForIndex());
    }

    /**
     * 工资水平列表
     */
    public function salaryList()
    {
        $this->renderSuccess(["10万以下","10-20万", "20-30万", "30-50万", "50-100万", "100万以上"]);
    }
    
     /**
     * 见面红包列表
     * @return void
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function meetingRedEnvelopeList(): void
    {
        
        $arr = [
            'id' => 0,
            'price' => "0.00"
        ];
        $list = (new MeetingRedEnvelope)
            ->field('id,price')
            ->order(['id'=>'asc'])
            ->select();
        array_unshift($list, $arr);
        $this->renderSuccess($list);
    }
}
