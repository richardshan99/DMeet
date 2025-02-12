<?php

namespace app\admin\controller\user;

use app\common\controller\Backend;
use app\common\library\Auth;
use app\common\library\Dict;
use app\common\model\MessageSystem;
use app\common\model\UserForm;
use Exception;
use think\Db;

/**
 * 会员管理
 *
 * @icon fa fa-user
 */
class User extends Backend
{

    protected $relationSearch = true;
    protected $searchFields = 'id,nickname';

    /**
     * @var \app\admin\model\User
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('app\common\model\User');
    }

    /**
     * 查看
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {

            $filter = $this->request->get("filter", '');
            $op = $this->request->get("op", '', 'trim');
            $filter = (array)json_decode($filter, true);
            $op = (array)json_decode($op, true);
            $andWhere = [];
            //筛选 -- 地区
            $areaId = 0;
            if(array_key_exists('country', $filter) > 0) {
                $areaId = $filter['country'];
                unset($filter['country']);
                unset($op['country']);
                \request()->get(['filter' => json_encode($filter)]);
                \request()->get(['op' => json_encode($op)]);
            }

            if(array_key_exists('province', $filter) > 0) {
                $areaId = $filter['province'];
                unset($filter['province']);
                unset($op['province']);
                \request()->get(['filter' => json_encode($filter)]);
                \request()->get(['op' => json_encode($op)]);
            }

            if(array_key_exists('city', $filter) > 0) {
                $areaId = $filter['city'];
                unset($filter['city']);
                unset($op['city']);
                \request()->get(['filter' => json_encode($filter)]);
                \request()->get(['op' => json_encode($op)]);
            }
            if(!empty($areaId)) {
                $andWhere[] = function($query) use($areaId) {
                    $query->whereRaw("find_in_set({$areaId}, `area_path`)");
                };
            }

            if(array_key_exists('min-age', $filter) > 0) {
                $minAge = $filter['min-age'];
                unset($filter['min-age']);
                unset($op['min-age']);
                \request()->get(['filter' => json_encode($filter)]);
                \request()->get(['op' => json_encode($op)]);

                if($minAge) {
                    $andWhere[] = function($query) use($minAge) {
                        $query->where("age", ">=", $minAge);
                    };
                }
            }

            if(array_key_exists('max-age', $filter) > 0) {
                $maxAge = $filter['max-age'];
                unset($filter['max-age']);
                unset($op['max-age']);
                \request()->get(['filter' => json_encode($filter)]);
                \request()->get(['op' => json_encode($op)]);

                if($maxAge) {
                    $andWhere[] = function($query) use($maxAge) {
                        $query->where("age", "<=", $maxAge);
                    };
                }
            }

            //筛选 -- 家乡
            if(array_key_exists('hometown', $filter) > 0) {
                $area =  trim($filter['hometown']);
                unset($filter['hometown']);
                unset($op['hometown']);
                \request()->get(['filter' => json_encode($filter)]);
                \request()->get(['op' => json_encode($op)]);

                $area = model('app\common\model\AreaNew')->where('name', 'like', "{$area}")->value('id');
                $area = $area ?: -1;
                $andWhere[] = function($query) use($area) {
                    $query->whereRaw("find_in_set({$area}, `hometown`)");
                };

            }
            //筛选 -- 用户状态
            if(array_key_exists('new_status', $filter) > 0) {
                $newStatus = $filter['new_status'];
                unset($filter['new_status']);
                unset($op['new_status']);
                \request()->get(['filter' => json_encode($filter)]);
                \request()->get(['op' => json_encode($op)]);

                if($newStatus == 1) {//授权用户
                    $andWhere[] = function($query) {
                        $query->where("is_improve", -1);
                    };
                }

                if($newStatus == 2) {//普通用户
                    $andWhere[] = function($query) {
                        $query->where("is_improve", 1);
                    };
                }

                if($newStatus == 3) {//认证用户
                    $andWhere[] = function($query) {
                        $query->where("is_cert_realname", 1);
                    };
                }

                if($newStatus == 4) {//会员用户
                    $andWhere[] = function($query) {
                        $query->where("is_member", 1);
                    };
                }
            }
            

            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $list = $this->model->with(['cert'])
                ->where($where)
                ->where($andWhere)
                ->order($sort, $order)
                ->paginate($limit);

            $sourceIds = array_column($list->items(), 'source_id');
            $source = model('app\common\model\Source')->whereIn('id', $sourceIds)->column('id,name');
            $areaNew = model('app\common\model\AreaNew')->column('id,name');
            foreach ($list as $k => $v) {
                $v->source_text = $source[$v->source_id] ?? "";
                $v->area = DMUserAreaNo($areaNew, $v->area_path);
                $v->hometown = DMUserAreaNo($areaNew, $v->hometown);
                $v->work_type_text = Dict::getWorkType($v->work_type);
                $v->education_type_text = Dict::getEducationType($v->education_type);
                $v->append(['source_text', 'work_type', 'area', 'hometown']);
                $v->hidden(['password', 'salt','active_point']);
            }

            if(array_key_exists('gender', $filter) > 0) {
                $gender = $filter['gender'];
                unset($filter['gender']);
                unset($op['gender']);
                \request()->get(['filter' => json_encode($filter)]);
                \request()->get(['op' => json_encode($op)]);

                if($gender) {
                    $andWhere[] = function($query) use($gender) {
                        $query->where("gender", $gender);
                    };
                }
            }
            $totalUser = model('app\common\model\User')->where($andWhere)->count();
            
            $totalSUser = model('app\common\model\User')->where('is_improve', Dict::IS_FALSE)->where($andWhere)->count();
            $totalNormalUser = model('app\common\model\User')->where('is_improve', Dict::IS_TRUE)->where($andWhere)->count();
            $totalRealUser = model('app\common\model\User')->where('is_cert_realname', Dict::IS_TRUE)->where($andWhere)->count();
            $totalMemberUser = model('app\common\model\User')->where('is_member', Dict::IS_TRUE)->where($andWhere)->count();
            $totalMaleUser = model('app\common\model\User')->where('gender', Dict::GENDER_MALE)->where($andWhere)->count();
            $totalFemaleUser = model('app\common\model\User')->where('gender', Dict::GENDER_FEMALE)->where($andWhere)->count();
//            echo model('app\common\model\User')->getLastSql();exit;
            $chart = [
                ["name" => "授权用户", "value" => $totalSUser, "ratio" => $totalUser ? bcmul(bcdiv($totalSUser, $totalUser, 9), 100, 2) : 0],
                ["name" => "普通用户", "value" => $totalNormalUser, "ratio" => $totalUser ? bcmul(bcdiv($totalNormalUser, $totalUser, 9), 100, 2) : 0],
                ["name" => "认证用户", "value" => $totalRealUser, "ratio" => $totalUser ? bcmul(bcdiv($totalRealUser, $totalUser, 9), 100, 2) : 0],
                ["name" => "会员用户", "value" => $totalMemberUser, "ratio" => $totalUser ? bcmul(bcdiv($totalMemberUser, $totalUser, 9), 100, 2) : 0],
                ["name" => "男性用户", "value" => $totalMaleUser, "ratio" => $totalUser ? bcmul(bcdiv($totalMaleUser, $totalUser, 9), 100, 2) : 0],
                ["name" => "女性用户", "value" => $totalFemaleUser, "ratio" => $totalUser ? bcmul(bcdiv($totalFemaleUser, $totalUser, 9), 100, 2) : 0] ,
            ];
            $result = array("total" => $list->total(), "rows" => $list->items(), "extend" => ['chart' => $chart]);

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * @param null $ids
     * @return string
     * @throws \think\Exception
     */
    public function detail($ids = null)
    {
        $user = $this->model->get($ids);
        $areaNew = model('app\common\model\AreaNew')->column('id,name');
        $user->area = DMUserArea($areaNew, $user->area_path, -3);
        $user->append(['gender_text']);
        //自定义字段
        $self = (new UserForm)->selfFieldList();
        $this->view->assign('user', $user);
        $this->view->assign('self', $self);
        $this->assignconfig('user_id', $ids);
        return $this->view->fetch();
    }

    /**
     * 留言列表
     * @param null $ids
     * @return string|\think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function message($ids =null)
    {
        $this->relationSearch = true;
        $this->model = model('app\common\model\Chat');
        //设置过滤方法
        $this->request->filter(['strip_tags']);

        if ($this->request->isAjax()) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams(null);
            $list = $this->model->with(['fromuser'])
                ->where('chat.to_user_id', $ids)
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->paginate($limit);
            foreach($list as $key => $item)
            {
                $item->append(['fromuser' => ['nickname', 'avatar', 'mobile']]);
            }
            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        $this->assignconfig('user_id', $ids);
        return $this->view->fetch();
    }

       /**
     * 发送系统消息
     *
     * @param null $ids
     * @return string
     * @throws \think\Exception
     */
    public function sendmessage($ids = null)
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
            $result = MessageSystem::create([
                "user_id" => $ids,
                "message" => $params['content'],
            ]);
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($result === false) {
            $this->error(__('No rows were inserted'));
        }

        //发送模板消息 @time 2024-9-27
        try {
            $user = $this->model->get($ids);
            if($user) {
                if($user['email']){
                    $subject = "系统消息通知";
                    $body = "尊敬的#".$user['nickname']."，<br>您收到了一条系统消息，<br>请登录DMeet直面 微信小程序查看。".Dict::EMAIL_TEXT;
                    (new \app\common\library\NewEmail)->send($user['email'],$subject,$body);
                }
            }
        } catch (\Exception $ex) {}
        $this->success();
    }
    
    /**
     * 用户下拉列表
     */
    public function selectpage()
    {
        $params = $this->request->param("");
        $mobile =  $params['mobile'] ?? '';
        $where = [];
        if (!empty($params['keyValue'])) {
            $where['id'] = $params['keyValue'];
        }

        if (!empty($mobile)) {
            $where['mobile'] = ['like','%'.$mobile.'%'];
        }

        $page = isset($params['pageNumber']) ? intval($params['pageNumber']) : 1; // 获取页码，默认为第一页
        $limit = isset($params['pageSize']) ? intval($params['pageSize']) : 10; // 每页显示条数，默认为10

        $category_list = $this->model->where($where)->field('id,mobile')->page($page, $limit)->select();
        $total = $this->model->where($where)->count();

        return json(['list' => $category_list, 'total' => $total]);
    }

}
