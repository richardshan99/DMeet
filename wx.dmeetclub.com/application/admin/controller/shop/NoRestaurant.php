<?php

namespace app\admin\controller\shop;

use app\common\controller\Backend;
use app\common\library\Dict;
use app\common\model\ShopUser;
use app\common\model\ShopUserClaim;
use Exception;
use think\Db;
use think\exception\DbException;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\Request;

/**
 * 地点管理 - 地点列表、非餐厅
 *
 * @icon   fa fa-dashboard
 * @remark
 */
class NoRestaurant extends Backend
{
    /**
     * @var \app\common\model\Shop
     */
    protected $model = null;
    protected $shopCategoryList = null;


    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->model = model('app\common\model\Shop');
        $this->model->where('type', DICT::SHOP_TYPE_NO_RESTAURANT);

        $this->shopCategoryList = model('app\common\model\ShopCategory')
            ->where('is_delete', DICT::IS_FALSE)
            ->column('name', 'id');

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
            $areaNew = model('app\common\model\AreaNew')->column('id,name');
            list($where, $sort, $order, $offset, $limit) = $this->buildparams(null);
            $list = $this->model
                ->field('*, ST_AsText(`point`) as point_text')
                ->with(['category'])
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->paginate($limit);

            foreach($list as $key => $item)
            {
                if(false !== strpos($item->point_text,"POINT")) {
                    preg_match('/\((.*?)\)/', $item->point_text, $matches);
                    $value = $matches[1];
                    $item->point_text = str_replace(" ",",", $value);
                } else {
                    $item->point_text = null;
                }
                 //地区
                $item->area = DMUserArea($areaNew, $item->area_path);
                $item->append(["status_text", 'cash_type_text','area']);
                $item->hidden(['point']);
            }
            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }

        $this->assignconfig('shopCategory', $this->shopCategoryList);
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
            $this->view->assign('shopCategoryList', $this->shopCategoryList);
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);

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


        $point = $params['point'] ?? "";
        $result = false;
        Db::startTrans();
        try {
            unset($params['point']);
            $params['type'] = Dict::SHOP_TYPE_NO_RESTAURANT;
            $params['images'] = explode(',', $params['images']);
            $result = $this->model->together('content')->allowField(true)->save($params);
            if($result !== false){
                if($point) {
                    $_ia = explode(',', $point);
                    // var_dump($_ia);die;
                    if(isset($_ia[0]) && isset($_ia[1])) {
                        $cord = $_ia[0].",".$_ia[1];
                        Db::execute('update DM_shop set `point` = point('.$cord.') where id ='.$this->model->id);
                    }
                }
                $arr = [
                    'info_status' => Dict::SHOP_INFO_STATUS_APPROVE,
                    'is_new' => Dict::IS_FALSE
                ];
                $this->model->allowField(true)->save($arr);
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
        $point = $params['point'] ?? "";

        $result = false;
        Db::startTrans();
        try {
            unset($params['point']);
            $params['images'] = explode(',', $params['images']);
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
     * 删除
     */
    public function del($ids = "")
    {
        if (!$this->request->isPost()) {
            $this->error(__("Invalid parameters"));
        }
        $ids = $ids ?: $this->request->post("ids");
        if ($ids) {
            Db::startTrans();
            try {
                $row = $this->model
                    ->field('*, ST_AsText(`point`) as point_text')
                    ->where('id', $ids)
                    ->find();
                if (!$row) {
                    $this->error(__('No Results were found'));
                }
                $row->delete();
                $row->together('content')->delete();
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }
            $this->success();
        }
        $this->error(__('Parameter %s can not be empty', 'ids'));
    }

    /**
     * 关联用户
     */
    public function claim($ids): string
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }

        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');

            $userInfo = model('app\common\model\User')
                ->where(['id'=>$params['user_id']])
                ->find();

            if(empty($userInfo)){
                $this->error("当前用户不存在");
            }
            //判断手机号是否存在
            $isExists = model('app\common\model\ShopUser')
                ->where([
                    "mobile"    => $userInfo['mobile'],
                    "is_delete" => Dict::IS_FALSE
                ])->find();
            if($isExists) {
                $this->error("当前手机号已存在");
            }

            //生成店长
            (new ShopUser)->generateManager([
                "mobile"  => $userInfo['mobile'],
                "shop_id" => $row->id,
            ]);


            $row->save([
                'type'  =>  Dict::SHOP_TYPE_RESTAURANT,
                "mobile"  => $userInfo['mobile'],
                "info_status" => Dict::SHOP_INFO_STATUS_UN,
                "is_new" => Dict::IS_TRUE
            ]);

            $this->success();
        }

        return $this->view->fetch();
    }

}
