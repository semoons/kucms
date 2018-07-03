<?php
namespace app\manage\controller;

use think\Request;
use core\manage\logic\MemberLogic;
use core\manage\logic\MemberLogLogic;

class MemberLog extends Base
{

    /**
     * 日志列表
     *
     * @param Request $request            
     * @return string
     */
    public function index(Request $request)
    {
        $this->site_title = '日志列表';
        $log_logic = MemberLogLogic::instance();
        
        // 用户下拉选择
        $user_select = MemberLogic::instance()->getUserSelect();
        $this->assign('user_select', $user_select);
        
        $map = [];
        
        // 用户
        $uid = $request->param('uid/d', 0);
        if (! empty($uid)) {
            $map['login_uid'] = $uid;
        }
        $this->assign('uid', $uid);
        
        // 开始时间
        $start_time = $request->param('start_time', '');
        $this->assign('start_time', $start_time);
        
        // 结束时间
        $end_time = $request->param('end_time', '');
        $this->assign('end_time', $end_time);
        
        // 时间
        if (! empty($start_time) && ! empty($end_time)) {
            $map['create_time'] = [
                'between',
                [
                    strtotime($start_time),
                    strtotime($end_time)
                ]
            ];
        } elseif (! empty($start_time)) {
            $map['create_time'] = [
                'egt',
                strtotime($start_time)
            ];
        } elseif (! empty($end_time)) {
            $map['create_time'] = [
                'elt',
                strtotime($end_time)
            ];
        }
        
        // 关键词
        $keyword = $request->param('keyword', '');
        if (! empty($keyword)) {
            $map['login_ip|login_agent'] = [
                'like',
                '%' . $keyword . '%'
            ];
        }
        $this->assign('keyword', $keyword);
        
        // 总数
        $total_count = $log_logic->model->where($map)->count();
        $this->assign('total_count', $total_count);
        
        // 列表
        $list = $log_logic->model->where($map)
            ->order('id desc')
            ->paginate($this->rows_num);
        
        // 补充用户数据
        $list_new = [];
        foreach ($list as $vo) {
            $vo['user'] = isset($user_select[$vo['login_uid']]) ? $user_select[$vo['login_uid']] : [
                'name' => 'unknown'
            ];
            $list_new[] = $vo;
        }
        $this->assign('list', $list_new);
        $this->assign('page', $list->render());
        
        return $this->fetch();
    }
}