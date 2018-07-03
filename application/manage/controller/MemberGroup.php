<?php
namespace app\manage\controller;

use think\Url;
use think\Request;
use core\manage\logic\MenuLogic;
use core\manage\logic\MemberLogic;
use core\manage\logic\MemberGroupLogic;

class MemberGroup extends Base
{

    /**
     * 群组列表
     *
     * @param Request $request            
     * @return string
     */
    public function index(Request $request)
    {
        $this->site_title = '用户群组';
        
        $list = MemberGroupLogic::model()->select();
        $this->assign('list', $list);
        
        return $this->fetch();
    }

    /**
     * 添加群组
     *
     * @param Request $request            
     * @return mixed
     */
    public function addGroup(Request $request)
    {
        $group_logic = MemberGroupLogic::instance();
        if ($request->isPost()) {
            $data = [
                'group_name' => $request->param('group_name'),
                'group_info' => $request->param('group_info', ''),
                'home_page' => $request->param('home_page', ''),
                'group_status' => $request->param('group_status', 0)
            ];
            
            // 验证群组
            $res = $group_logic->validate->scene('add')->check($data);
            if (! $res) {
                return $this->error($group_logic->validate->getError());
            }
            
            // 添加群组
            $group_logic->model->add($data);
            
            return $this->success('添加群组成功', Url::build('memberGroup/index'));
        } else {
            $this->site_title = '新增群组';
            
            // 群组状态
            $group_status = $group_logic->groupStatus();
            $this->assign('group_status', $group_status);
            
            return $this->fetch();
        }
    }

    /**
     * 编辑群组
     *
     * @param Request $request            
     * @return mixed
     */
    public function editGroup(Request $request)
    {
        $group_id = $request->param('group_id');
        if (empty($group_id)) {
            return $this->error('群组ID为空');
        }
        
        $group_logic = MemberGroupLogic::instance();
        if ($request->isPost()) {
            $data = [
                'group_name' => $request->param('group_name'),
                'group_info' => $request->param('group_info', ''),
                'home_page' => $request->param('home_page', ''),
                'group_status' => $request->param('group_status', 0)
            ];
            
            // 验证群组
            $res = $group_logic->validate->scene('edit')->check($data);
            if (! $res) {
                return $this->error($group_logic->validate->getError());
            }
            
            // 保存群组
            $group_logic->model->save($data, $group_id);
            
            return $this->success('修改群组成功', Url::build('memberGroup/index'));
        } else {
            
            $this->site_title = '编辑群组';
            $this->assign('group_id', $group_id);
            
            // 群组
            $group = $group_logic->model->get($group_id);
            $this->assign('group', $group);
            
            // 群组状态
            $group_status = $group_logic->groupStatus();
            $this->assign('group_status', $group_status);
            
            return $this->fetch();
        }
    }

    /**
     * 编辑权限
     *
     * @param Request $request            
     * @return mixed
     */
    public function editAuth(Request $request)
    {
        $group_id = $request->param('group_id');
        if (empty($group_id)) {
            return $this->error('群组ID为空');
        }
        
        $group_logic = MemberGroupLogic::instance();
        if ($request->isPost()) {
            
            $group_menus = $request->param('group_menus/a');
            if (empty($group_menus) || count($group_menus) == 0) {
                return $this->error('权限菜单为空');
            }
            
            // 保存权限
            $data = [
                'group_menus' => implode(',', $group_menus)
            ];
            $group_logic->model->save($data, $group_id);
            
            return $this->success('保存权限成功', Url::build('memberGroup/index'));
        } else {
            
            $this->site_title = '编辑权限';
            $this->assign('group_id', $group_id);
            
            // 群组菜单
            $group_menus = $group_logic->getGroupMenu($group_id);
            $this->assign('group_menus', $group_menus);
            
            // 菜单树
            $menu_tree = MenuLogic::instance()->getMenuTree();
            $this->assign('menu_tree', $menu_tree);
            
            return $this->fetch();
        }
    }

    /**
     * 删除群组
     *
     * @param Request $request            
     * @return mixed
     */
    public function delGroup(Request $request)
    {
        $group_id = $request->param('group_id');
        if (empty($group_id)) {
            return $this->error('群组ID为空');
        }
        
        // 是否有用户
        $map = [
            'group_id' => $group_id
        ];
        $record = MemberLogic::model()->where($map)
            ->field('id')
            ->find();
        if ($record) {
            return $this->error('请先移动或删除该群组下的用户');
        }
        
        // 删除群组
        MemberGroupLogic::model()->del($group_id);
        
        return $this->success('删除群组成功', 'history.go(0);');
    }

    /**
     * 更改群组
     *
     * @param Request $request            
     * @return mixed
     */
    public function modifyGroup(Request $request)
    {
        $id = $request->param('id');
        if (empty($id)) {
            return $this->error('ID为空');
        }
        
        $field_arr = [
            'group_status'
        ];
        $field = $request->param('field');
        if (! in_array($field, $field_arr)) {
            return $this->error('非法的字段');
        }
        
        $value = $request->param('value', '');
        MemberGroupLogic::model()->modify($id, $field, $value);
        
        return $this->success('更改成功', 'history.go(0);');
    }
}
