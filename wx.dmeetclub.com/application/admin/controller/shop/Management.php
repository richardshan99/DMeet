<?php

namespace app\admin\controller\shop;

use app\admin\model\Admin;
use app\admin\model\User;
use app\common\controller\Backend;
use app\common\library\Dict;
use app\common\model\Attachment;
use app\common\model\Config as ConfigModel;
use app\common\model\ShopUser;
use Exception;
use fast\Date;
use think\Db;
use think\exception\DbException;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\Request;

/**
 * 门店管理 - 门店列表
 *
 * @icon   fa fa-dashboard
 * @remark
 */
class Management extends Backend
{
    /**
     * @var \app\common\model\Shop
     */
    protected $model = null;
    protected $shopCategoryList = null;
    protected $accountTypeList = null;


    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->model = model('app\common\model\Shop');
        $this->model->where('type', DICT::SHOP_TYPE_RESTAURANT);

        $this->shopCategoryList = model('app\common\model\ShopCategory')
            ->where('is_delete', DICT::IS_FALSE)
            ->column('name', 'id');

        $this->accountTypeList = Dict::getCashTypeList();
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
                ->with(['category'])
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->paginate($limit);

            foreach($list as $key => $item)
            {
                $item->append(["status_text", 'cash_type_text']);
                $item->hidden(['point']);
            }
            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }

        $this->assignconfig('shopCategory', $this->shopCategoryList);
        return $this->view->fetch();
    }

    /**
     * 查看店员
     * @param null $ids
     * @return string|\think\response\Json
     * @throws \think\Exception
     */
    public function users($ids = null)
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        /** @var ShopUser model */
        $this->model = model('app\common\model\ShopUser');
        if ($this->request->isAjax()) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams(null);
            $list = $this->model
                ->where($where)
                ->where([
                    "shop_id"   => $ids,
                    "role"      => Dict::SHOP_USER_ROLE_CLERK,
                    "is_delete" => Dict::IS_FALSE
                ])
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->paginate($limit);
//            echo $this->projectManagerModel->getLastSql();exit;
            foreach($list as $key => $item) {
                $item->append(["status_text"]);
            }

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }

        $this->assignconfig('shop_id', $ids);
        return $this->view->fetch();
    }

    /**
     * 编辑分成比例
     *
     * @param $ids
     * @return string
     * @throws DbException
     * @throws \think\Exception
     */
    public function ratio($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds) && !in_array($row[$this->dataLimitField], $adminIds)) {
            $this->error(__('You have no permission'));
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
            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                $row->validateFailException()->validate($validate);
            }
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
     * 添加
     *
     * @return string
     * @throws \think\Exception
     */
    public function add()
    {
        if (false === $this->request->isPost()) {
            $this->view->assign('shopCategoryList', $this->shopCategoryList);
            $this->view->assign('cashAccountList', $this->accountTypeList);

            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);

        if(!empty($params['content']['content_images'])) {
            $params['content']['content_images'] = explode(',', $params['content']['content_images']);
        }

        //判断手机号是否存在
        $isExists = model('app\common\model\ShopUser')
            ->where([
                 "mobile"    => $params['mobile'],
                 "is_delete" => Dict::IS_FALSE
            ])->find();
        if($isExists) {
            $this->error("当前手机号已存在");
        }

        $point = $params['point'] ?? "";

        //分析活动地区
        $areaPath = "";
        if(isset($params['country']) && !empty($params['country'])) { //国家
            $areaPath = model('app\common\model\AreaNew')->where('id', $params['country'])->value('path');
        }
        if(isset($params['province']) && !empty($params['province'])) {//省份
            $areaPath = model('app\common\model\AreaNew')->where('id', $params['province'])->value('path');
        }
        if(isset($params['city']) && !empty($params['city'])) {//地市
            $areaPath = model('app\common\model\AreaNew')->where('id', $params['city'])->value('path');

        }
        $params['area_path'] = $areaPath;
        $params['package1']['service'] = $params['package1']['service'] ? json_decode($params['package1']['service'], true) : [];
        $params['package2']['service'] = $params['package2']['service'] ? json_decode($params['package2']['service'], true) : [];

        $result = false;
        Db::startTrans();
        try {
            unset($params['point']);
            $result = $this->model->together('content')->allowField(true)->save($params);
            if($result !== false){
                if($point) {
                    $_ia = explode(',', $point);
                    if(isset($_ia[0]) && isset($_ia[1])) {
                        $cord = $_ia[0].",".$_ia[1];
                        Db::execute('update DM_shop set `point` = point('.$cord.') where id ='.$this->model->id);
                    }
                }

                //生成店长
                (new ShopUser)->generateManager([
                    "mobile"  => $params['mobile'],
                    "shop_id" => $this->model->id,
                ]);
            }

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
        $row = $this->model
            ->field('*, ST_AsText(`point`) as point_text')
            ->where('id', $ids)
            ->find();
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds) && !in_array($row[$this->dataLimitField], $adminIds)) {
            $this->error(__('You have no permission'));
        }
        if (false === $this->request->isPost()) {
            //关联扩展
            $row->appendRelationAttr('content', ['content', 'content_images']);

            $this->view->assign('shopCategoryList', $this->shopCategoryList);
            $this->view->assign('cashAccountList', $this->accountTypeList);

            $point = $row->point_text;
            if(false !== strpos($point,"POINT")) {
                preg_match('/\((.*?)\)/', $point, $matches);
                $value = $matches[1];
                $row->point = str_replace(" ",",", $value);
            } else {
                $row->point = null;
            }
            
            $this->view->assign('row', $row);
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }

        //分析活动地区
        $areaPath = "";
        if(isset($params['country']) && !empty($params['country'])) { //国家
            $areaPath = model('app\common\model\AreaNew')->where('id', $params['country'])->value('path');
        }
        if(isset($params['province']) && !empty($params['province'])) {//省份
            $areaPath = model('app\common\model\AreaNew')->where('id', $params['province'])->value('path');
        }
        if(isset($params['city']) && !empty($params['city'])) {//地市
            $areaPath = model('app\common\model\AreaNew')->where('id', $params['city'])->value('path');

        }
        $params['area_path'] = $areaPath;
        
        if(!empty($params['content']['content_images'])) {
            $params['content']['content_images'] = explode(',', $params['content']['content_images']);
        }
        $params['package1']['service'] = $params['package1']['service'] ? json_decode($params['package1']['service'], true) : [];
        $params['package2']['service'] = $params['package2']['service'] ? json_decode($params['package2']['service'], true) : [];

        $point = $params['point'] ?? "";

        $result = false;
        Db::startTrans();
        try {
            unset($params['point']);
            $result = $row->together('content')->allowField(true)->save($params);

            if($point) {
                $_ia = explode(',', $point);
                if(isset($_ia[0]) && isset($_ia[1])) {
                    $cord = $_ia[0].",".$_ia[1];
                    Db::execute('update DM_shop set `point` = point('.$cord.') where id ='.$row->id);
                }
            }
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
     * 查看门店数据
     * @param null $ids
     * @return string
     * @throws \think\Exception
     */
    public function data($ids = null)
    {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {

            $filter = $this->request->get("filter", '');
            $op = $this->request->get("op", '', 'trim');
            $filter = (array)json_decode($filter, true);
            $op = (array)json_decode($op, true);
            $andWhere = [];
            //筛选 --
            if(array_key_exists('meet_time', $filter) > 0) {
                $createTime = $filter['meet_time'];
                unset($filter['meet_time']);
                unset($op['meet_time']);
                \request()->get(['filter' => json_encode($filter)]);
                \request()->get(['op' => json_encode($op)]);

                $createTime = str_replace(' - ', ',', $createTime);
                $createTime = array_slice(explode(',', $createTime), 0, 2);
                $andWhere['meet_time'] = ['between', [strtotime($createTime[0]), strtotime($createTime[1])]];
            }

            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $list = model("app\common\model\Payment")->with(['user'])
                ->field('invite.package, invite.status as invite_status, invite.meet_time')
                ->join('__INVITE__ invite', 'invite.id = payment.service_biz_id')
                ->where($where)
                ->where("invite.status", Dict::INVITE_STATUS_FINISH)
                ->where("invite.shop_id", $ids)
                ->where("payment.service_type", Dict::SERVICE_TYPE_INVITATION)
                ->where("payment.pay_type", Dict::PAY_TYPE_WECHAT)
                ->where("payment.is_use", Dict::IS_TRUE)
                ->where($andWhere)
                ->order($sort, $order)
                ->paginate($limit);
//            echo  model("app\common\model\Payment")->with(['user'])->getLastSql();exit;

            foreach ($list as $k => $v) {
                $v->invite_status_text = Dict::getInviteStatus($v->invite_status);
//                $v->append(['status_text']);
                $v->hidden(['user' => ['password', 'salt']]);
            }

            $totalIncome = model('app\common\model\Invite')->where([
                "status" => Dict::INVITE_STATUS_FINISH,
                "shop_id" => $ids,
            ])->where($andWhere)->sum('price');
            $orderCount = model('app\common\model\Invite')->where([
                    "status" => Dict::INVITE_STATUS_FINISH,
                    "shop_id" => $ids,
                ])->where($andWhere)->count();

            $chart = [
                ["name" => "营收数据（收入金额）", "value" => $totalIncome ?? "0.00"],
                ["name" => "订单数量", "value" => $orderCount ?? 0],
            ];
            $result = array("total" => $list->total(), "rows" => $list->items(), "extend" => ['chart' => $chart]);

            return json($result);
        }
        $this->assignconfig('shop_id', $ids);

        return $this->view->fetch();
    }
}
