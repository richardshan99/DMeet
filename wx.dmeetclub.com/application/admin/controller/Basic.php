<?php

namespace app\admin\controller;

use app\admin\model\Admin;
use app\admin\model\User;
use app\common\controller\Backend;
use app\common\library\Dict;
use app\common\model\Attachment;
use app\common\model\Config as ConfigModel;
use Exception;
use fast\Date;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;

/**
 * 基础数据管理
 *
 * @icon   fa fa-dashboard
 * @remark 基础数据
 */
class Basic extends Backend
{
    protected $noNeedRight = [
        'virtlist', 'addvirt', 'editvirt', 'delvirt',
        'messagelist', 'addmesssage', 'editmessage', 'delmessage'
    ];

    /**
     * 查看
     */
    public function index()
    {

        $this->model = new ConfigModel;
        $siteList = [];
        $groupList = ConfigModel::getGroupList();
        foreach ($groupList as $k => $v) {  
            if(!in_array($k, ['normal'])) continue;
            $siteList[$k]['name'] = $k;
            $siteList[$k]['title'] = $v;
            $siteList[$k]['list'] = [];
        }

        $allItems = $this->model->all();                   //改为逆序 
        foreach (array_reverse($allItems) as $k => $v) {   //by Richard
            if (!isset($siteList[$v['group']])) {
                continue;
            }
            $value = $v->toArray();
            $value['title'] = __($value['title']);
            if (in_array($value['type'], ['select', 'selects', 'checkbox', 'radio'])) {
                $value['value'] = explode(',', $value['value']);
            }
            $value['content'] = json_decode($value['content'], true);
            if (in_array($value['name'], ['categorytype', 'configgroup', 'attachmentcategory'])) {
                $dictValue = (array)json_decode($value['value'], true);
                foreach ($dictValue as $index => &$item) {
                    $item = __($item);
                }
                unset($item);
                $value['value'] = json_encode($dictValue, JSON_UNESCAPED_UNICODE);
            }
            $value['tip'] = htmlspecialchars($value['tip']);
            if ($value['name'] == 'cdnurl') {
                //cdnurl不支持在线修改
                continue;
            }
            $siteList[$v['group']]['list'][] = $value;
        }
        $index = 0;
        foreach ($siteList as $k => &$v) {
            $v['active'] = !$index ? true : false;
            $index++;
        }
        $this->view->assign('siteList', $siteList);
        $this->view->assign('typeList', ConfigModel::getTypeList());
        $this->view->assign('ruleList', ConfigModel::getRegexList());
        $this->view->assign('groupList', ConfigModel::getGroupList());


        //用户信息审核
        $check_user_info_num = model('app\common\model\UserChange')->where('status', Dict::CERT_STATUS_WAIT)->count();
        //用户动态审核提醒
        $check_user_trend_num = model('app\common\model\Blog')->where([
            'status' => Dict::BLOG_STATUS_WAIT,
            'style' => Dict::BLOG_STYLE_NORMAL,
        ])->count();
        //用户认证审核提醒
        $certWorkCount = model('app\common\model\CertWork')->where('status', Dict::CERT_STATUS_WAIT)->count();
        $certEduCount = model('app\common\model\CertEducation')->where('status', Dict::CERT_STATUS_WAIT)->count();
        $check_user_auth_num = bcadd($certWorkCount, $certEduCount);
        //门店入驻审核提醒
        $check_shop_entrant_num = model('app\common\model\ShopApply')->where('status', Dict::SHOP_APPLY_STATUS_WAIT)->count();
        //门店信息审核提醒
        $check_shop_info_num = model('app\common\model\ShopChange')->where('status', Dict::SHOP_INFO_STATUS_WAIT)->count();
        //门店提现审核提醒
        $check_shop_cash_num = model('app\common\model\ShopCash')->where('status', Dict::SHOP_CASH_STATUS_WAIT)->count();
        $this->view->assign(compact(
            "check_user_info_num","check_user_trend_num","check_user_auth_num",
            "check_shop_entrant_num", "check_shop_info_num", "check_shop_cash_num"
        ));
        return $this->view->fetch();
    }

    /**
     * 虚拟数据
     */
    public function virt()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams(null);
            $list = model('app\common\model\Virt')
                ->where($where)
                ->where('source', Dict::MEET_DATA_SOURCE_VIRT)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->paginate($limit);
            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 添加虚拟数据
     */
    public function addvirt()
    {
        if (false === $this->request->isPost()) {
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);
        $result = false;
        Db::startTrans();
        try {
            $result = model('app\common\model\Virt')->allowField(true)->save(array_merge($params, [
                'source' => Dict::MEET_DATA_SOURCE_VIRT
            ]));
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
     * 编辑虚拟数据
     */
    public function editvirt($ids = null)
    {
        $row = model('app\common\model\Virt')->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }

        if (false === $this->request->isPost()) {
            $this->view->assign('row', $row);
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);
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

    /**
     * 删除虚拟数据
     */
    public function delvirt($ids = null)
    {
        if (false === $this->request->isPost()) {
            $this->error(__('Invalid parameters'));
        }
        $ids = $ids ?: $this->request->post('ids');
        if (empty($ids)) {
            $this->error(__('Parameter %s can not be empty', 'ids'));
        }

        $count = 0;
        Db::startTrans();
        try {
            $list = model('app\common\model\Virt')->where('id', 'in', $ids)->select();
            foreach ($list as $item) {
                $count += $item->delete();
            }
            Db::commit();
        } catch (PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($count) {
            $this->success();
        }
        $this->error(__('No rows were updated'));
    }

    /**
     * 留言消息模版
     */
    public function message()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams(null);
            $list = model('app\common\model\Message')
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->paginate($limit);
            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 添加留言消息模版
     */
    public function addmessage()
    {
        if (false === $this->request->isPost()) {
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);
        $result = false;
        Db::startTrans();
        try {
            $result = model('app\common\model\Message')->allowField(true)->save($params);
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
     * 编辑留言消息模版
     */
    public function editmessage($ids = null)
    {
        $row = model('app\common\model\Message')->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }

        if (false === $this->request->isPost()) {
            $this->view->assign('row', $row);
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);
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

    /**
     * 删除留言消息
     */
    public function delmessage($ids = null)
    {
        if (false === $this->request->isPost()) {
            $this->error(__('Invalid parameters'));
        }
        $ids = $ids ?: $this->request->post('ids');
        if (empty($ids)) {
            $this->error(__('Parameter %s can not be empty', 'ids'));
        }

        $count = 0;
        Db::startTrans();
        try {
            $list = model('app\common\model\Message')->where('id', 'in', $ids)->select();
            foreach ($list as $item) {
                $count += $item->delete();
            }
            Db::commit();
        } catch (PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($count) {
            $this->success();
        }
        $this->error(__('No rows were updated'));
    }

}
