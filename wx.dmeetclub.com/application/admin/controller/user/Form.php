<?php

namespace app\admin\controller\user;

use app\admin\model\Admin;
use app\admin\model\User;
use app\common\controller\Backend;
use app\common\library\Dict;
use app\common\model\Attachment;
use app\common\model\Config as ConfigModel;
use app\common\model\Config;
use Exception;
use fast\Date;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\Request;

/**
 * 用户个人表单信息设置
 *
 * @icon   fa fa-dashboard
 * @remark
 */
class Form extends Backend
{
    /**
     * @var \app\common\model\UserForm
     */
    protected $model = null;

    public function __construct()
    {
        parent::__construct();
        $this->model = model('app\common\model\UserForm');

        $needList = [
            Dict::USER_FORM_TYPE_TEXT,
            Dict::USER_FORM_TYPE_MULTI_TEXT,
            Dict::USER_FORM_TYPE_NUMBER_PICKER,
            Dict::USER_FORM_TYPE_AREA_PICKER,
            Dict::USER_FORM_TYPE_SELECT,
            Dict::USER_FORM_TYPE_MULTI_SELECT,
            Dict::USER_FORM_TYPE_DATE,
        ];

        foreach($needList as $key => $item)
        {
            $typeList[$item] = Dict::getUserFormType($item);
        }
        $this->view->assign('typeList', $typeList);
    }

    /**
     * 查看
     */
    public function index()
    {
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams(null);
            $list = $this->model
                ->where($where)
                ->order($sort, 'asc')
                ->limit($offset, $limit)
                ->paginate($limit);

            foreach($list as $key => $item)
            {
                if($item->type == Dict::USER_FORM_TYPE_RADIO) { //单选
                    $content = json_decode($item->content, true);
                    $item->value = $content[$item->value] ?? null;
                }

                $item->append(['type_text', 'value_text']);
            }

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 添加
     *
     * @return string
     * @throws \think\Exception
     */
    public function add()
    {
        if (false === $this->request->isPost()) {
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);

        //判断key是否重复
        $isExists = $this->model->where('key', $params['key'])->find();
        if($isExists) {
            $this->error("字段名已存在");
        }
        //单选 OR 多选 处理列表
        if (in_array($params['type'], [Dict::USER_FORM_TYPE_SELECT, Dict::USER_FORM_TYPE_MULTI_SELECT])) {
            $params['content'] = json_encode(Config::decode($params['content']), JSON_UNESCAPED_UNICODE);
        } else {
            $params['content'] = null;
        }

        //地址选择器
        if($params['type'] == Dict::USER_FORM_TYPE_AREA_PICKER)
        {
            //默认值填充
            if($params['country']) {
                $params['value'] = $params['city'] ?? $params['province'];
                $areaNew = model('app\common\model\AreaNew')->column('id,name');
                $currentArea = model('app\common\model\AreaNew')->find($params['value']);
                $params['content'] = json_encode(['area_path' => $currentArea['path'], 'name' => DMUserArea($areaNew, $currentArea['path'])]);
            }
        }

        if(in_array($params['type'], [
            Dict::USER_FORM_TYPE_TEXT,
            Dict::USER_FORM_TYPE_MULTI_TEXT,
            Dict::USER_FORM_TYPE_SELECT,
            Dict::USER_FORM_TYPE_MULTI_SELECT,
        ])) {
            $params['value'] = $params['value1'];
        }

        if(in_array($params['type'], [
            Dict::USER_FORM_TYPE_NUMBER_PICKER
        ])) {
            $params['value'] = $params['value2'];
        }

        if(in_array($params['type'], [
            Dict::USER_FORM_TYPE_DATE
        ])) {
            $params['value'] = $params['value3'];
        }
        $result = false;
        Db::startTrans();
        try {
            $result = $this->model->allowField(true)->save($params);
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($result === false) {
            $this->error(__('No rows were inserted'));
        }
        $this->success();
    }

    /**
     * 编辑
     *
     * @param $ids
     * @return string
     * @throws DbException
     * @throws \think\Exception
     */
    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }

        if (false === $this->request->isPost()) {
            $row['content'] = json_decode($row['content'], true);
            $this->view->assign('row', $row);
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if($row->is_regular == Dict::IS_FALSE) //自定义字段
        {
            //判断key是否重复
            $isExists = $this->model->where([
                'key' => $params['key'],
                'id'  => ['<>', $row->id],
            ])->find();
            if($isExists) {
                $this->error("字段名已存在");
            }

            //单选 OR 多选 处理列表
            if (in_array($params['type'], [Dict::USER_FORM_TYPE_SELECT, Dict::USER_FORM_TYPE_MULTI_SELECT])) {
                $params['content'] = json_encode(Config::decode($params['content']), JSON_UNESCAPED_UNICODE);
            } else {
                $params['content'] = null;
            }

            //地址选择器
            if($params['type'] == Dict::USER_FORM_TYPE_AREA_PICKER)
            {
                //默认值填充
                if($params['country']) {
                    $params['value'] = $params['city'] ?? $params['province'];
                    $areaNew = model('app\common\model\AreaNew')->column('id,name');
                    $currentArea = model('app\common\model\AreaNew')->find($params['value']);
                    $params['content'] = json_encode(['area_path' => $currentArea['path'], 'name' => DMUserArea($areaNew, $currentArea['path'])]);
                }
            }

            if(in_array($params['type'], [
                Dict::USER_FORM_TYPE_TEXT,
                Dict::USER_FORM_TYPE_MULTI_TEXT,
                Dict::USER_FORM_TYPE_SELECT,
                Dict::USER_FORM_TYPE_MULTI_SELECT,
            ])) {
                $params['value'] = $params['value1'];
            }

            if(in_array($params['type'], [
                Dict::USER_FORM_TYPE_NUMBER_PICKER
            ])) {
                $params['value'] = $params['value2'];
            }

            if(in_array($params['type'], [
                Dict::USER_FORM_TYPE_DATE
            ])) {
                $params['value'] = $params['value3'];
            }
        } else {
            $params['value'] = $params['value'] ?: null;
        }
        $result = false;
        Db::startTrans();
        try {
            $result = $row->allowField(true)->save($params);
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if (false === $result) {
            $this->error(__('No rows were updated'));
        }
        $this->success();
    }

}
