<?php

namespace app\common\model;

use app\common\exception\BaseException;
use app\common\library\Dict;
use Exception;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\Model;

/**
 * 活动报名数据
 */
class ActivityUser Extends Model
{

    protected $name = 'activity_user';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = false;
    // 追加属性
    protected $append = [
    ];

    protected $insert = [
        "status"    => Dict::ACTIVITY_USER_STATUS_SIGN
    ];

    protected $type = [

    ];

    /**===========================
     *   DATA FORMAT
    =============================*/

    /**
     * 状态格式化
     * @param $value
     * @param $row
     * @return string
     */
    public function getStatusTextAttr($value, $row)
    {
        return Dict::getActivityUserStatus($row['status']);
    }


    /**========================
     *   RELATION
    ==========================*/
    public function user()
    {
        return $this->belongsTo('app\common\model\User', 'user_id')->setEagerlyType(0);
    }

    public function activity()
    {
        return $this->belongsTo('app\common\model\Activity', 'activity_id')->setEagerlyType(0);
    }


    /**========================
     *   EXTEND
    ==========================*/
    /**
     * 我的活动列表 - 分页
     * @param null $user
     * @param array $where
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($user= null, $where = [])
    {
        if(isset($where['status']) && !empty($where['status'])) {
            $where['activity_user.status'] = $where['status'];
        }
        unset($where['status']);
        $list = $this->with(['activity' => ['activityType']])
            ->where('activity_user.user_id', $user->id)
            ->where($where)
            ->order('activity.begin_time', 'asc')
            ->paginate(20);
//        echo $this->with(['activity' => ['activityType']])->getLastSql();exit;
        $areaNew = model('app\common\model\AreaNew')->column('id,name');
        foreach($list as $key => $item)
        {
//            $item->activity->type_name = $item->activity->activityType->name;
            $item->activity->area = DMUserAreaNo($areaNew, $item->activity->area_path, -3);
            $item->visible([
                "id", "status",
                "activity" => ["id", "name"],
            ]);
            $item->append([
                "id", "status_text",
                "activity" => ['begin_time_text', "type_text", "thumb_text", "area"]
            ]);
        }
        return $list;
    }

    /**
     * 活动 退款
     * @param $activityId
     * @return false|int
     * @throws BaseException
     * @throws \think\exception\DbException
     */
    public function refund()
    {
        //判断活动是否结束
        $activity = $this->activity;
        if(empty($activity)) {
            throw new BaseException(['msg' => "活动不存在"]);
        }

        if($activity->status != Dict::ACTIVITY_STATUS_INPROGRESS || $activity->end_time < time()) {
            throw new BaseException(['msg' => "活动已结束"]);
        }

        //退款
        $user = User::get($this->user_id);
        $payment = new \app\common\library\pay\Payment($user);
        try {
            $result = $payment->refundActivity($this);
        } catch (\Exception $ex) {
            throw new BaseException(['msg' => $ex->getMessage()]);
        }
        return $result;
    }

}
