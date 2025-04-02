<?php

namespace app\admin\controller\auth;

use app\admin\model\AuthRule;
use app\common\controller\Backend;
use fast\Tree;
use think\Cache;
use think\Collection;

/**
 * 规则管理
 *
 * @icon   fa fa-list
 * @remark 规则通常对应一个控制器的方法,同时左侧的菜单栏数据也从规则中体现,通常建议通过控制台进行生成规则节点
 */
class Rule extends Backend
{
    /**
     * @var \app\admin\model\AuthRule
     * 定义当前控制器使用的模型为 AuthRule 模型
     */
    protected $model = null;
    // 存储规则列表数据
    protected $rulelist = [];
    // 定义支持批量操作的字段
    protected $multiFields = 'ismenu,status';
    // 缓存键名，用于存储菜单数据
    const MENU_CACHE_KEY = '__menu__';

    /**
     * 初始化方法，在控制器的每个方法执行前调用
     * 此方法主要完成规则列表数据的初始化，包括从缓存或数据库获取数据，
     * 处理规则数据，为视图分配必要的变量等操作
     */
    public function _initialize()
    {
        // 调用父类的初始化方法
        parent::_initialize();

        // 检查当前用户是否为超级管理员，若不是则返回错误信息
        if (!$this->auth->isSuperAdmin()) {
            $this->error(__('Access is allowed only to the super management group'));
        }

        // 实例化 AuthRule 模型
        $this->model = model('AuthRule');

        // 尝试从缓存中获取规则列表
        $this->rulelist = Cache::get('rule_list');
        if (!$this->rulelist) {
            // 若缓存不存在，则从数据库中查询规则列表
            // 只查询需要的字段，并按权重和 ID 排序
            $ruleList = \think\Db::name("auth_rule")->field('type,condition,remark,createtime,updatetime', true)->order('weigh ASC,id ASC')->select();

            // 遍历规则列表，对规则标题进行国际化处理
            foreach ($ruleList as &$rule) {
                $rule['title'] = __($rule['title']);
            }
            // 释放引用
            unset($rule);

            // 初始化 Tree 类，用于构建树形结构
            Tree::instance()->init($ruleList);
            // 获取树形结构的规则列表
            $this->rulelist = Tree::instance()->getTreeList(Tree::instance()->getTreeArray(0), 'title');

            // 将规则列表存入缓存，方便下次使用
            Cache::set('rule_list', $this->rulelist);
        }

        // 初始化规则数据数组，用于存储可作为父规则的选项
        $ruledata = [0 => __('None')];
        // 遍历规则列表，筛选出可作为父规则的菜单规则
        foreach ($this->rulelist as &$rule) {
            if (!$rule['ismenu']) {
                continue;
            }
            $ruledata[$rule['id']] = $rule['title'];
            // 移除不必要的间隔符字段
            unset($rule['spacer']);
        }
        // 释放引用
        unset($rule);

        // 为视图分配规则数据和菜单类型列表
        $this->view->assign('ruledata', $ruledata);
        $this->view->assign("menutypeList", $this->model->getMenutypeList());
    }

    /**
     * 查看规则列表页面
     * 若请求为 AJAX 请求，则返回规则列表数据；否则返回规则列表页面视图
     */
    public function index()
    {
        if ($this->request->isAjax()) {
            // 获取规则列表数据
            $list = $this->rulelist;
            // 计算规则列表的总数
            $total = count($this->rulelist);
            // 构建返回的 JSON 数据结构
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }
        // 返回规则列表页面视图
        return $this->view->fetch();
    }

    /**
     * 添加规则
     * 若请求为 POST 请求，则处理添加规则的逻辑；否则返回添加规则页面视图
     */
    public function add()
    {
        if ($this->request->isPost()) {
            // 验证表单令牌，防止 CSRF 攻击
            $this->token();
            // 获取 POST 请求中的规则数据，并进行过滤
            $params = $this->request->post("row/a", [], 'strip_tags');
            if ($params) {
                // 验证非菜单规则是否有父规则
                if (!$this->validateNonMenuRule($params)) {
                    $this->error(__('The non-menu rule must have parent'));
                }
                try {
                    // 对规则数据进行验证并保存到数据库
                    $result = $this->model->validate()->save($params);
                    if ($result === false) {
                        // 若保存失败，返回错误信息
                        $this->error($this->model->getError());
                    }
                    // 清除菜单缓存和规则列表缓存
                    Cache::rm(self::MENU_CACHE_KEY);
                    Cache::rm('rule_list');
                    // 添加成功，返回成功信息
                    $this->success();
                } catch (\Exception $e) {
                    // 记录异常日志
                    \think\facade\Log::error('添加规则时出现异常：' . $e->getMessage());
                    // 返回错误信息
                    $this->error(__('An unexpected error occurred'));
                }
            }
            // 若参数为空，返回错误信息
            $this->error();
        }
        // 返回添加规则页面视图
        return $this->view->fetch();
    }

    /**
     * 编辑规则
     * @param string|null $ids 规则的 ID
     * 若请求为 POST 请求，则处理编辑规则的逻辑；否则返回编辑规则页面视图
     */
    public function edit($ids = null)
    {
        // 根据 ID 获取要编辑的规则数据
        $row = $this->model->get(['id' => $ids]);
        if (!$row) {
            // 若未找到规则数据，返回错误信息
            $this->error(__('No Results were found'));
        }
        if ($this->request->isPost()) {
            // 验证表单令牌，防止 CSRF 攻击
            $this->token();
            // 获取 POST 请求中的规则数据，并进行过滤
            $params = $this->request->post("row/a", [], 'strip_tags');
            if ($params) {
                // 验证非菜单规则是否有父规则
                if (!$this->validateNonMenuRule($params)) {
                    $this->error(__('The non-menu rule must have parent'));
                }
                // 检查是否将父规则设置为自身
                if ($params['pid'] == $row['id']) {
                    $this->error(__('Can not change the parent to self'));
                }
                // 检查是否将父规则设置为子规则
                if ($params['pid'] != $row['pid']) {
                    $childrenIds = $this->getChildrenIds($row['id']);
                    if (in_array($params['pid'], $childrenIds)) {
                        $this->error(__('Can not change the parent to child'));
                    }
                }
                // 定义规则验证器
                $ruleValidate = \think\Loader::validate('AuthRule');
                $ruleValidate->rule([
                    'name' => 'require|unique:AuthRule,name,' . $row->id,
                ]);
                try {
                    // 对规则数据进行验证并更新到数据库
                    $result = $row->validate()->save($params);
                    if ($result === false) {
                        // 若更新失败，返回错误信息
                        $this->error($row->getError());
                    }
                    // 清除菜单缓存和规则列表缓存
                    Cache::rm(self::MENU_CACHE_KEY);
                    Cache::rm('rule_list');
                    // 编辑成功，返回成功信息
                    $this->success();
                } catch (\Exception $e) {
                    // 记录异常日志
                    \think\facade\Log::error('编辑规则时出现异常：' . $e->getMessage());
                    // 返回错误信息
                    $this->error(__('An unexpected error occurred'));
                }
            }
            // 若参数为空，返回错误信息
            $this->error();
        }
        // 为视图分配要编辑的规则数据
        $this->view->assign("row", $row);
        // 返回编辑规则页面视图
        return $this->view->fetch();
    }

    /**
     * 删除规则
     * @param string $ids 要删除的规则 ID，多个 ID 用逗号分隔
     * 若请求为 POST 请求，则处理删除规则的逻辑
     */
    public function del($ids = "")
    {
        if (!$this->request->isPost()) {
            // 若不是 POST 请求，返回错误信息
            $this->error(__("Invalid parameters"));
        }
        $ids = $ids ? $ids : $this->request->post("ids");
        if ($ids) {
            // 初始化要删除的规则 ID 数组
            $delIds = [];
            // 遍历要删除的规则 ID，获取其所有子规则 ID
            foreach (explode(',', $ids) as $id) {
                $delIds = array_merge($delIds, Tree::instance()->getChildrenIds($id, true));
            }
            // 去重
            $delIds = array_unique($delIds);
            try {
                // 根据 ID 删除规则数据
                $count = $this->model->where('id', 'in', $delIds)->delete();
                if ($count) {
                    // 清除菜单缓存和规则列表缓存
                    Cache::rm(self::MENU_CACHE_KEY);
                    Cache::rm('rule_list');
                    // 删除成功，返回成功信息
                    $this->success();
                }
            } catch (\Exception $e) {
                // 记录异常日志
                \think\facade\Log::error('删除规则时出现异常：' . $e->getMessage());
                // 返回错误信息
                $this->error(__('An unexpected error occurred'));
            }
        }
        // 若 ID 为空，返回错误信息
        $this->error();
    }

    /**
     * 验证非菜单规则是否有父规则
     * @param array $params 规则参数
     * @return bool 若为非菜单规则且有父规则，返回 true；否则返回 false
     */
    private function validateNonMenuRule($params)
    {
        return $params['ismenu'] || $params['pid'];
    }

    /**
     * 获取指定规则的子规则 ID 列表
     * @param int $id 规则 ID
     * @return array 子规则 ID 列表
     */
    private function getChildrenIds($id)
    {
        // 尝试从缓存中获取子规则 ID 列表
        $childrenIds = Cache::get('children_ids_' . $id);
        if (!$childrenIds) {
            // 若缓存不存在，则从数据库中查询规则列表
            $ruleList = AuthRule::select();
            // 初始化 Tree 类，获取子规则 ID 列表
            $childrenIds = Tree::instance()->init(Collection::make($ruleList)->toArray())->getChildrenIds($id);
            // 将子规则 ID 列表存入缓存
            Cache::set('children_ids_' . $id, $childrenIds);
        }
        return $childrenIds;
    }
}