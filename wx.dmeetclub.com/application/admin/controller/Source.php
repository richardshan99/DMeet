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
use think\Request;

/**
 * 渠道码管理
 *
 * @icon   fa fa-dashboard
 * @remark
 */
class Source extends Backend
{
    /**
     * @var \app\common\model\Source
     */
    protected $model = null;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->model = model('app\common\model\Source');
    }

    /**
     * 查看
     */
    public function index()
    {

        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams(null);
            $list = $this->model
                ->where('is_delete', Dict::IS_FALSE)
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->paginate($limit);


            $areaNew = model('app\common\model\AreaNew')->column('id,name');
            foreach($list as $key => $item) {
                //地区
                $item->area = DMUserArea($areaNew, $item->area_path);
                $item->append(['area']);
            }
            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 添加虚拟数据
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

        //分析活动地区
        $areaId = 0;
        $areaPath = [];
        if(isset($params['country']) && !empty($params['country'])) { //国家
            $areaId = $params['country'];
            array_push($areaPath, $params['country']);
        }
        if(isset($params['province']) && !empty($params['province'])) {//省份
            $areaId = $params['province'];
            array_push($areaPath, $params['province']);
        }
        if(isset($params['city']) && !empty($params['city'])) {//地市
            $areaId = $params['city'];
            array_push($areaPath, $params['city']);
        }
        $params['area_id'] = $areaId;
        $params['area_path'] = implode(',', $areaPath);

        $result = false;
        Db::startTrans();
        try {
            $result = $this->model->allowField(true)->save($params);
            //生成二维码
            $text = \request()->domain().'/login?source_id='.$this->model->id;
            $image = DMQrcode($text)->getString();
            $image = DMqiniu($image);
            $this->model->allowField(true)->save(['qrcode' => DS.$image['key']]);
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
     * 删除
     *
     * @param $ids
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function del($ids = null)
    {
        if (false === $this->request->isPost()) {
            $this->error(__("Invalid parameters"));
        }
        $ids = $ids ?: $this->request->post("ids");
        if (empty($ids)) {
            $this->error(__('Parameter %s can not be empty', 'ids'));
        }
        $pk = $this->model->getPk();
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            $this->model->where($this->dataLimitField, 'in', $adminIds);
        }
        $list = $this->model->where($pk, 'in', $ids)->select();

        $count = 0;
        Db::startTrans();
        try {
            foreach ($list as $item) {
                $count += $item->allowField(true)->save(['is_delete' => Dict::IS_TRUE]);
            }
            Db::commit();
        } catch (PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($count) {
            $this->success();
        }
        $this->error(__('No rows were deleted'));
    }

    /**
     * 下载二维码
     * @param null $ids
     * @throws \think\exception\DbException
     */
    public function down($ids = null)
    {
        $source = $this->model->get($ids);
        $file = cdnurl($source['qrcode'], true);
        header("Content-type:application/octet-stream");
        header("content-disposition:attachment;filename=$file");
        @readfile($file);
    }
}
