<?php
namespace app\manage\controller;

use cms\Common;
use think\Request;
use core\manage\logic\FileLogic;

class File extends Base
{

    /**
     * 文件列表
     *
     * @param Request $request            
     * @return string
     */
    public function index(Request $request)
    {
        $this->site_title = '附件列表';
        $file_logic = FileLogic::instance();
        
        $map = [];
        
        // 类型
        $type = $request->param('type', '');
        if (! empty($type)) {
            $map['file_type'] = $type;
        }
        $this->assign('type', $type);
        
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
            $map['file_hash'] = [
                'like',
                '%' . $keyword . '%'
            ];
        }
        $this->assign('keyword', $keyword);
        
        // 总数
        $total_count = $file_logic->model->where($map)->count();
        $this->assign('total_count', $total_count);
        
        // 列表
        $list = $file_logic->model->where($map)
            ->order('id desc')
            ->paginate($this->rows_num);
        
        // 文件大小格式化
        $res = array();
        foreach ($list as $vo) {
            $vo['file_size'] = Common::formatBytes($vo['file_size']);
            $res[] = $vo;
        }
        $this->assign('list', $res);
        $this->assign('page', $list->render());
        
        // 上传类型
        $upload_type_list = $file_logic->getUploadTypeSelect();
        $this->assign('upload_type_list', $upload_type_list);
        
        return $this->fetch();
    }

    /**
     * 删除文件
     *
     * @param Request $request            
     * @return mixed
     */
    public function delFile(Request $request)
    {
        $file_id = $request->param('file_id');
        if (empty($file_id)) {
            return $this->error('文件ID为空');
        }
        
        $res = FileLogic::instance()->delFile($file_id);
        if ($res['code'] == 1) {
            return $this->success('删除文件成功', 'history.go(0)');
        } else {
            return $this->error($res['msg']);
        }
    }

    /**
     * 上传文件
     *
     * @return string
     */
    public function upload()
    {
        $this->site_title = '文件上传';
        
        return $this->fetch();
    }
}