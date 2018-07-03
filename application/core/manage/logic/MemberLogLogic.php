<?php
namespace core\manage\logic;

use cms\Logic;
use cms\Common;

class MemberLogLogic extends Logic
{

    /**
     * 新增日志
     *
     * @param number $user_id            
     * @param number $type            
     * @return number
     */
    public function addLog($user_id)
    {
        $data = [
            'login_uid' => $user_id,
            'login_ip' => Common::getIp(),
            'login_agent' => Common::getAgent()
        ];
        return $this->model->add($data);
    }
}