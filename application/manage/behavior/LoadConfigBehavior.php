<?php
namespace app\manage\behavior;

use think\Config;
use core\manage\logic\ConfigLogic;

class LoadConfigBehavior
{

    /**
     * 加载配置
     *
     * @param unknown $params            
     */
    public function run(&$params)
    {
        Config::set(ConfigLogic::instance()->getConfig());
    }
}