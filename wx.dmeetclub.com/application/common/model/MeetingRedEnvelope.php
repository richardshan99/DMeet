<?php

namespace app\common\model;

use think\Model;

/**
 * 见面红包
 */
class MeetingRedEnvelope Extends Model
{

    protected $name = 'meeting_red_envelope';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = false;
    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    // 追加属性
    protected $append = [
    ];





}
