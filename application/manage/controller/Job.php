<?php
namespace app\manage\controller;

use think\Request;
use core\manage\logic\JobLogic;

class Job extends Base
{

    /**
     * 任务列表
     *
     * @param Request $request            
     * @return string
     */
    public function index(Request $request)
    {
        $this->site_title = '任务列表';
        $job_logic = JobLogic::instance();
        
        // 队列列表
        $queue_list = $job_logic->getJobQueue();
        $this->assign('queue_list', $queue_list);
        
        $map = [];
        
        // 队列
        $queue = $request->param('queue', '');
        if (! empty($queue)) {
            $map['queue'] = $queue;
        }
        $this->assign('queue', $queue);
        
        // 关键词
        $keyword = $request->param('keyword', '');
        if (! empty($keyword)) {
            $map['payload'] = [
                'exp',
                'like \'%' . str_replace([
                    '"',
                    '\\'
                ], [
                    '',
                    '%'
                ], json_encode($keyword)) . '%\''
            ];
        }
        $this->assign('keyword', $keyword);
        
        // 总数
        $total_count = $job_logic->model->where($map)->count();
        $this->assign('total_count', $total_count);
        
        // 列表
        $list = $job_logic->model->where($map)->paginate($this->rows_num);
        $list_new = [];
        foreach ($list as $vo) {
            $vo['payload'] = var_export(json_decode($vo['payload'], true), true);
            $list_new[] = $vo;
        }
        $this->assign('list', $list_new);
        $this->assign('page', $list->render());
        
        return $this->fetch();
    }

    /**
     * 任务状态
     *
     * @param Request $request            
     * @return mixed
     */
    public function modifyStatus(Request $request)
    {
        $id = $request->param('id');
        if (empty($id)) {
            return $this->error('任务ID为空');
        }
        
        $status = $request->param('value', 0);
        if ($status == 0) {
            $data = [
                'reserved' => 0,
                'reserved_at' => null
            ];
        } elseif ($status == 1) {
            $data = [
                'reserved' => 1,
                'reserved_at' => time()
            ];
        } else {
            $time = $request->param('time', 10);
            $data = [
                'reserved' => 0,
                'reserved_at' => null,
                'available_at' => time() + $time
            ];
        }
        JobLogic::model()->save($data, $id);
        
        return $this->success('操作成功', 'javascript:history.go(0);');
    }

    /**
     * 删除任务
     *
     * @param Request $request            
     * @return mixed
     */
    public function delJob(Request $request)
    {
        $job_id = $request->param('job_id');
        if (empty($job_id)) {
            return $this->error('任务ID为空');
        }
        
        $res = JobLogic::model()->del($job_id);
        if ($res) {
            return $this->success('删除任务成功', 'history.go(0)');
        } else {
            return $this->error('删除任务失败');
        }
    }
}