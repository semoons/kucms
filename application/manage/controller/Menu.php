<?php
namespace app\manage\controller;

use think\Url;
use think\Request;
use core\manage\logic\MenuLogic;

class Menu extends Base
{

    /**
     * 菜单列表
     *
     * @param Request $request            
     * @return string
     */
    public function index(Request $request)
    {
        $this->site_title = '菜单管理';
        $menu_model = MenuLogic::model();
        
        // 上级
        $menu_pid = $request->param('menu_pid', 0);
        $this->assign('menu_pid', $menu_pid);
        
        $map = [
            'menu_pid' => $menu_pid
        ];
        
        // 分组列表
        $group_list = $menu_model->field('id, menu_group')
            ->where($map)
            ->group('menu_group')
            ->order('menu_sort asc')
            ->select();
        $this->assign('group_list', $group_list);
        
        // 分组
        $group_name = $request->param('group', '');
        if (! empty($group_name)) {
            $map['menu_group'] = $group_name;
        }
        $this->assign('group', $group_name);
        
        // 列表
        $list = $menu_model->where($map)
            ->order('menu_sort asc')
            ->select();
        $this->assign('list', $list);
        
        return $this->fetch();
    }

    /**
     * 添加菜单
     *
     * @param Request $request            
     * @return mixed
     */
    public function addMenu(Request $request)
    {
        $menu_logic = MenuLogic::instance();
        if ($request->isPost()) {
            $data = [
                'menu_name' => $request->param('menu_name'),
                'menu_url' => $request->param('menu_url'),
                'menu_pid' => $request->param('menu_pid', 0),
                'menu_group' => $request->param('menu_group', ''),
                'menu_sort' => $request->param('menu_sort', 0),
                'menu_target' => $request->param('menu_target', ''),
                'menu_build' => $request->param('menu_build', 0),
                'menu_status' => $request->param('menu_status', 0)
            ];
            
            // 验证菜单
            $res = $menu_logic->validate->scene('add')->check($data);
            if (! $res) {
                return $this->error($menu_logic->validate->getError());
            }
            
            // 添加菜单
            $menu_logic->addMenu($data);
            
            return $this->success('添加菜单成功', Url::build('menu/index', [
                'menu_pid' => $request->param('menu_pid')
            ]));
        } else {
            $this->site_title = '新增菜单';
            
            // 上级，返回用
            $menu_pid = $request->param('menu_pid', 0);
            $this->assign('menu_pid', intval($menu_pid));
            
            // 上级菜单下拉选择
            $menu_select = $menu_logic->getMenuSelect();
            $this->assign('menu_select', $menu_select);
            
            // 打开方式
            $menu_target = $menu_logic->menuTarget();
            $this->assign('menu_target', $menu_target);
            
            // 是否build
            $menu_build = $menu_logic->menuBuild();
            $this->assign('menu_build', $menu_build);
            
            // 菜单状态
            $menu_status = $menu_logic->menuStatus();
            $this->assign('menu_status', $menu_status);
            
            return $this->fetch();
        }
    }

    /**
     * 编辑菜单
     *
     * @param Request $request            
     * @return mixed
     */
    public function editMenu(Request $request)
    {
        $menu_id = $request->param('menu_id');
        if (empty($menu_id)) {
            return $this->error('菜单ID为空');
        }
        
        $menu_logic = MenuLogic::instance();
        if ($request->isPost()) {
            $data = [
                'menu_name' => $request->param('menu_name'),
                'menu_url' => $request->param('menu_url'),
                'menu_pid' => $request->post('menu_pid', 0),
                'menu_group' => $request->param('menu_group', ''),
                'menu_sort' => $request->param('menu_sort', 0),
                'menu_target' => $request->param('menu_target', ''),
                'menu_build' => $request->param('menu_build', 0),
                'menu_status' => $request->param('menu_status', 0)
            ];
            
            // 验证菜单
            $res = $menu_logic->validate->scene('edit')->check($data);
            if (! $res) {
                return $this->error($menu_logic->validate->getError());
            }
            
            // 保存菜单
            $menu_logic->saveMenu($data, $menu_id);
            
            return $this->success('修改菜单成功', Url::build('menu/index', [
                'menu_pid' => $request->param('menu_pid')
            ]));
        } else {
            $this->site_title = '编辑菜单';
            $this->assign('menu_id', $menu_id);
            
            // 菜单
            $menu = $menu_logic->model->get($menu_id);
            $this->assign('menu', $menu);
            
            // 上级菜单下拉选择
            $menu_select = $menu_logic->getMenuSelect();
            $this->assign('menu_select', $menu_select);
            
            // 打开方式
            $menu_target = $menu_logic->menuTarget();
            $this->assign('menu_target', $menu_target);
            
            // 是否build
            $menu_build = $menu_logic->menuBuild();
            $this->assign('menu_build', $menu_build);
            
            // 菜单状态
            $menu_status = $menu_logic->menuStatus();
            $this->assign('menu_status', $menu_status);
            
            return $this->fetch();
        }
    }

    /**
     * 删除菜单
     *
     * @param Request $request            
     * @return mixed
     */
    public function delMenu(Request $request)
    {
        $menu_id = $request->param('menu_id');
        if (empty($menu_id)) {
            return $this->error('菜单ID为空');
        }
        $menu_model = MenuLogic::model();
        
        // 检查子菜单
        $map = [
            'menu_pid' => $menu_id
        ];
        $menu = $menu_model->where($map)->find();
        if (! empty($menu)) {
            return $this->error('请先删除该菜单下的子菜单');
        }
        
        // 删除菜单
        $menu_model->del($menu_id);
        
        return $this->success('删除菜单成功', 'history.go(0);');
    }

    /**
     * 更改菜单
     *
     * @param Request $request            
     * @return mixed
     */
    public function modifyMenu(Request $request)
    {
        $id = $request->param('id');
        if (empty($id)) {
            return $this->error('ID为空');
        }
        
        $field_arr = [
            'menu_group',
            'menu_sort',
            'menu_status'
        ];
        $field = $request->param('field');
        if (! in_array($field, $field_arr)) {
            return $this->error('非法的字段');
        }
        
        $value = $request->param('value', '');
        MenuLogic::model()->modify($id, $field, $value);
        
        return $this->success('更改成功', 'history.go(0);');
    }
}