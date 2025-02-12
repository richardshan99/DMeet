<?php

namespace app\common\model;

use app\common\library\Dict;
use think\Db;
use think\Model;

/**
 * 用户表单
 */
class UserForm Extends Model
{

    protected $name = 'user_form';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = false;
    // 追加属性
    protected $append = [
    ];
    protected $insert = [
        "is_regular" => Dict::IS_FALSE
    ];

    public function getTypeTextAttr($value, $item)
    {
        return $item['is_regular'] != Dict::IS_TRUE ? Dict::getUserFormType($item['type']) : "";
    }

    public function getValueTextAttr($value, $item)
    {
        if($item['key'] == 'work_type') {//工作情况
            return Dict::getWorkType($item['value']);
        }
        if($item['key'] == 'education_type') {//学历
            return Dict::getEducationType($item['value']);
        }

        if($item['is_regular'] == -1) { //自定义字段
            if($item['type'] == Dict::USER_FORM_TYPE_AREA_PICKER) { //地区选择器
                $content = json_decode($item['content'], true);
                return $content['name'] ?? "";
            }
        }

        return $item['value'];
    }

    /**
     * 必填字段列表
     */
    public function requireFieldList()
    {
        return $this->where("is_require", Dict::IS_TRUE)->select();
    }

    /**
     * 非必填字段列表
     */
    public function  notRequireFieldList()
    {
        return $this->where("is_require", Dict::IS_FALSE)->select();
    }

    /**
     * 固定字段列表
     */
    public function regularFieldList()
    {
        return $this->formatterField(Dict::IS_TRUE);
    }

    /**
     * 自定义字段列表
     */
    public function selfFieldList()
    {
        return $this->formatterField(Dict::IS_FALSE);
    }

    /**
     * 解析字段
     * @param $isRegular
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function formatterField($isRegular)
    {
        $list = $this->where('is_regular', $isRegular)->select();
        foreach($list as $key => &$item)
        {
            if(!$item['content']) continue;
            if($item['type'] == Dict::USER_FORM_TYPE_RADIO) { //单选
                $content = json_decode($item['content'] ?: "", true);
                $tmp = [];
                foreach($content as $_key => $_item)
                {
                    if(empty($_key)) continue;
                    array_push($tmp, [
                        "value" => $_key,
                        "title" => $_item
                    ]);
                }
                $item['content'] = $tmp;
            }

            if($item['type'] == Dict::USER_FORM_TYPE_AREA_PICKER) { //城市选择器
                $item['content'] = json_decode($item['content'] ?: "", true);
            }

            if(in_array($item['type'], [Dict::USER_FORM_TYPE_SELECT, Dict::USER_FORM_TYPE_MULTI_SELECT])) { //下拉
                $content = json_decode($item['content'], true);
                $tmp = [];
                foreach($content as $_key => $_item)
                {
                    array_push($tmp, [
                        "value" => $_key,
                        "title" => $_item
                    ]);
                }
                $item['content'] = $tmp;
            }
        }

        return $list;
    }
}
