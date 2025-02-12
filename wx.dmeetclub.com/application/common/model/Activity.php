<?php

namespace app\common\model;

use app\common\library\Dict;
use app\common\library\pay\Payment;
use think\Model;

/**
 * 活动
 */
class Activity Extends Model
{

    protected $name = 'activity';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    // 追加属性
    protected $append = [
    ];

    protected $insert = [
        "is_delete"      => Dict::IS_FALSE,
        "status"         => Dict::ACTIVITY_STATUS_INPROGRESS,
        "sign_status"    => Dict::ACTIVITY_SIGN_STATUS_LACK
    ];

    protected $type = [
        "images" => "json",
    ];

    public function setBeginTimeAttr($value)
    {
        return strtotime($value);
    }

    public function setEndTimeAttr($value)
    {
        return strtotime($value);
    }

    public function setImagesAttr($value)
    {
        return $value ? json_encode(explode(',', $value)) : null;
    }

    /**
     * 判断活动是否已结束
     * @param $value
     * @param $row
     * @return bool
     */
    public function getIsFinishAttr($value, $row)
    {
        return $row['status'] != Dict::ACTIVITY_STATUS_INPROGRESS || $row['end_time'] < time();
    }

    /**
     * 活动状态格式化
     * @param $value
     * @param $row
     * @return mixed|string
     */
    public function getStatusTextAttr($value, $row)
    {
        //如果进行中的活动，结束时间已过。 则返回已结束
        if($row == Dict::ACTIVITY_STATUS_INPROGRESS && $row['end_time'] < time()) {
            return Dict::getActivityStatus(Dict::ACTIVITY_STATUS_FINISH);
        }
        return Dict::getActivityStatus($row['status']);
    }

    /**
     * 活动报名状态格式化
     * @param $value
     * @param $row
     * @return mixed|string
     */
    public function getSignStatusTextAttr($value, $row)
    {
        return Dict::getActivitySignStatus($row['sign_status']);
    }

    /**
     * 活动开始日期 格式化
     * @param $value
     * @param $row
     * @return false|string
     */
    public function getBeginTimeTextAttr($value, $row)
    {
        return $row['begin_time'] ? date("Y-m-d H:i", $row['begin_time']) : "";
    }

    /**
     * 活动结束日期 格式化
     * @param $value
     * @param $row
     * @return false|string
     */
    public function getEndTimeTextAttr($value, $row)
    {
        return $row['end_time'] ? date("Y-m-d H:i", $row['end_time']) : "";
    }

    /**
     * 缩略图 格式化
     * @param $value
     * @param $row
     * @return string
     */
    public function getThumbTextAttr($value, $row)
    {
        return $row['thumb'] ? cdnurl($row['thumb'], true) : "";

    }

    /**
     * 轮播图 格式化
     * @param $value
     * @param $row
     * @return string
     */
    public function getImagesTextAttr($value, $row)
    {
        $images = $row['images'] ? json_decode($row['images'], true) : [];
        foreach($images as $key => &$item)
        {
            $item = cdnurl($item, true);
        }
        return $images;
    }

    public function getTypeTextAttr($value, $row)
    {
        return $this->activityType->name;
    }


    /**===========================
     *   RELATION
    =============================*/

    /**
     * 扩展信息
     * @return \think\model\relation\HasOne
     */
    public function content()
    {
        return $this->hasOne("app\common\model\ActivityContent", 'activity_id');
    }

    /**
     * 活动类型
     * @return \think\model\relation\BelongsTo
     */
    public function activityType()
    {
        return $this->belongsTo("app\common\model\ActivityType", 'activity_type_id')->setEagerlyType(0);
    }

    /**
     * @return \think\model\relation\HasMany
     */
    public function user()
    {
        return $this->hasMany("app\common\model\ActivityUser", 'activity_id');
    }


    /**===========================
     *   Extra
    =============================*/

    /**
     * 结束进行中的到期活动
     */
    public function finish()
    {
        $this->save(['status' => Dict::ACTIVITY_STATUS_FINISH], [
            "status" => Dict::ACTIVITY_STATUS_INPROGRESS,
            "end_time" => ['<', time()]
        ]);

        //更新用户参与状态
        model('app\common\model\ActivityUser')->allowField(true)->save([
            'status' => Dict::ACTIVITY_USER_STATUS_FINISH
        ], [
            'activity_id' => $this->id,
            'status'      => Dict::ACTIVITY_USER_STATUS_SIGN
        ]);
    }

    /**
     * 活动列表 - 分页
     * @param array $where
     * @param User $user
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($where = [], User $user)
    {
        $list = $this->with(['activityType'])
            ->where($where)
            ->where("status", '<>', DICT::ACTIVITY_STATUS_CANCEL)
            ->order('create_time', 'desc')
            ->paginate(20);
//        echo $this->with(['activityType'])->getLastSql();exit;

        //报名的活动列表
        $activityUser = $this->user()->where('user_id', $user->id)->where('status', "<>", Dict::ACTIVITY_USER_STATUS_REFUND)->column('activity_id');
        $activityUser = array_unique($activityUser);

        $areaNew = model('app\common\model\AreaNew')->column('id,name');
        foreach($list as $key => $item)
        {
            $item->join_status = 1;
            $item->join_status_text = "未报名";
            if(in_array($item->id, $activityUser)) {
                $item->join_status = 2;
                $item->join_status_text = "已报名";
            }

            if($item->is_finish) {
                $item->join_status = 3;
                $item->join_status_text = "已结束";
            }

            $item->area = DMUserAreaNo($areaNew, $item->area_path, -3);
            $item->visible([
                "id", "name","join_status", "join_status_text", 'area'
            ]);
            $item->append(['begin_time_text', "type_text", "thumb_text", "area"]);
        }
        return $list;
    }

    /**
     * 活动详情
     * @param null $user
     * @return Activity
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getDetail($user = null)
    {
        $this->appendRelationAttr('content', 'content');

        $this->can_join = true;

        $joinNum = $this->user()->where('status', '<>', Dict::ACTIVITY_USER_STATUS_REFUND)->count();

        $this->can_join = true;
        if($user) {
            //活动是否结束， 是否超过开始时间， 是否超过最大参与人数
            if($this->is_finish || time() < $this->begin_time || $this->max_num <= $joinNum) {
                $this->can_join = false;
            } else {
                //我是否参加过
                $_myjoin = model('app\common\model\ActivityUser')->where('user_id', $user->id)->where('activity_id', $this->id)->where('status', '<>', Dict::ACTIVITY_USER_STATUS_REFUND)->find();
                if($_myjoin) {
                    $this->can_join = false;
                }
            }
        }


        $areaNew = model('app\common\model\AreaNew')->column('id,name');
        $this->area = DMUserAreaNo($areaNew, $this->area_path, -3);
        $this->visible([
            "id", "name", "price", "content", "can_join"
        ]);
        $this->append(['begin_time_text', "type_text", "images_text", "area"]);
        return $this;
    }

    /**
     * 付款
     * @param User $user
     * @param $activityId
     * @param $payType
     * @return array
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function pay(User $user, $activityId, $payType)
    {
        $activity = $this->get([
            "id"        => $activityId,
            "is_delete" => Dict::IS_FALSE
        ]);

        if(!$activity) {
            throw new \Exception("活动不存在");
        }

        $joinNum = $activity->user()->where('status', '<>', Dict::ACTIVITY_USER_STATUS_REFUND)->count();
        //活动是否结束， 是否超过开始时间， 是否超过最大参与人数
        if($activity->is_finish || time() < $activity->begin_time || $activity->max_num <= $joinNum) {
            throw new \Exception("活动已结束，请选择其他活动");
        }

        //是否参加过活动
        $isJoin = model('app\common\model\ActivityUser')->where([
            "activity_id" => $activityId,
            "user_id"     => $user->id,
            "status"      => ['<>', Dict::ACTIVITY_USER_STATUS_REFUND]
        ])->find();
        if($isJoin) {
            throw new \Exception("您已参与，请勿重复提交");
        }

        try {
            $payment = new Payment($user, $payType);
            $result = $payment->activity($activity);
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }

        return $result;

    }


}
