<?php
namespace core\manage\logic;

use cms\Logic;

class JobLogic extends Logic
{

    /**
     * 队列列表
     *
     * @return array
     */
    public function getJobQueue()
    {
        $list = $this->model->group('queue')->select();
        $list || $list = [];
        
        $result = [];
        foreach ($list as $vo) {
            $result[] = [
                'name' => $vo['queue'],
                'value' => $vo['queue']
            ];
        }
        return $result;
    }
}