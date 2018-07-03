<?php
namespace app\manage\controller;

use think\Url;
use think\Request;
use core\manage\logic\MemberLogic;
use core\manage\logic\MemberGroupLogic;

class Member extends Base
{

    /**
     * 用户列表
     *
     * @param Request $request            
     * @return string
     */
    public function index(Request $request)
    {
        $this->site_title = '用户管理';
        $member_model = MemberLogic::model();
        
        $member_group = MemberGroupLogic::model()->select();
        $this->assign('member_group', $member_group);
        
        $list = MemberLogic::model()->select();
        $this->assign('list', $list);
        
        return $this->fetch();
    }

    /**
     * 添加用户
     *
     * @param Request $request            
     * @return mixed
     */
    public function addMember(Request $request)
    {
        $user_logic = MemberLogic::instance();
        if ($request->isPost()) {
            $data = [
                'user_name' => $request->param('user_name'),
                'user_nick' => $request->param('user_nick'),
                'user_passwd' => $request->param('user_passwd'),
                're_passwd' => $request->param('re_passwd'),
                'group_id' => $request->param('group_id'),
                'user_status' => $request->param('user_status', 0)
            ];
            
            // 验证
            $res = $user_logic->validate->scene('add')->check($data);
            if (! $res) {
                return $this->error($user_logic->validate->getError());
            }
            
            // 添加
            $user_logic->addMember($data);
            
            return $this->success('添加用户成功', Url::build('member/index'));
        } else {
            $this->site_title = '新增用户';
            
            // 群组下拉选择
            $member_group = MemberGroupLogic::instance()->getGroupSelect();
            $this->assign('member_group', $member_group);
            
            // 用户状态
            $user_status = $user_logic->userStatus();
            $this->assign('user_status', $user_status);
            
            return $this->fetch();
        }
    }

    /**
     * 编辑用户
     *
     * @param Request $request            
     * @return mixed
     */
    public function editMember(Request $request)
    {
        $user_id = $request->param('user_id');
        if (empty($user_id)) {
            return $this->error('用户ID为空');
        }
        
        $user_logic = MemberLogic::instance();
        if ($request->isPost()) {
            $data = [
                'user_name' => $request->param('user_name'),
                'user_nick' => $request->param('user_nick'),
                'user_passwd' => $request->param('user_passwd'),
                're_passwd' => $request->param('re_passwd'),
                'group_id' => $request->param('group_id'),
                'user_status' => $request->param('user_status', 0)
            ];
            
            // 验证
            $scene = empty($data['user_passwd']) ? 'edit_info' : 'edit_passwd';
            $res = $user_logic->validate->scene($scene)->check($data);
            if (! $res) {
                return $this->error($user_logic->validate->getError());
            }
            
            // 修改
            $user_logic->saveMember($data, $user_id);
            
            return $this->success('修改用户成功', Url::build('member/index'));
        } else {
            $this->site_title = '编辑菜单';
            $this->assign('user_id', $user_id);
            
            // 用户
            $member = $user_logic->model->get($user_id);
            $this->assign('member', $member);
            
            // 群组下拉选择
            $member_group = MemberGroupLogic::instance()->getGroupSelect();
            $this->assign('member_group', $member_group);
            
            // 状态
            $user_status = $user_logic->userStatus();
            $this->assign('user_status', $user_status);
            
            return $this->fetch();
        }
    }

    /**
     * 删除用户
     *
     * @param Request $request            
     * @return mixed
     */
    public function delMember(Request $request)
    {
        $user_id = $request->param('user_id');
        if (empty($user_id)) {
            return $this->error('用户ID为空');
        }
        $user_logic = MemberLogic::instance();
        
        // 超级用户
        if ($user_logic->isAdmin($user_id)) {
            return $this->error('超级用户不能删除');
        }
        
        // 删除用户
        $user_logic->model->del($user_id);
        
        return $this->success('删除用户成功', Url::build('member/index'));
    }

    /**
     * 更改用户
     *
     * @param Request $request            
     * @return mixed
     */
    public function modifyMember(Request $request)
    {
        $id = $request->param('id');
        if (empty($id)) {
            return $this->error('ID为空');
        }
        
        $field_arr = [
            'group_id',
            'user_status'
        ];
        $field = $request->param('field');
        if (! in_array($field, $field_arr)) {
            return $this->error('非法的字段');
        }
        
        $value = $request->param('value', '');
        MemberLogic::model()->modify($id, $field, $value);
        
        return $this->success('更改成功', Url::build('member/index'));
    }
}