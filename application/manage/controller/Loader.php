<?php
namespace app\manage\controller;

use think\App;
use think\Request;

class Loader
{

    /**
     * 调用module
     *
     * @param Request $request            
     * @return mixed
     */
    public function run(Request $request)
    {
        
        // 模块变量
        define('_MODULE_', $request->param('_module_'));
        define('_CONTROLLER_', $request->param('_controller_'));
        define('_ACTION_', $request->param('_action_'));
        
        // 后台的view_path
        $manage_view_path = APP_PATH . 'manage/view/';
        define('MANAGE_VIEW_PATH', $manage_view_path);
        
        // 模块的view_path
        $module_view_path = APP_PATH . 'module/' . _MODULE_ . '/view/';
        define('MODULE_VIEW_PATH', $module_view_path);
        
        // 执行操作
        $class = 'module\\' . _MODULE_ . '\\controller\\' . ucfirst(_CONTROLLER_);
        return App::invokeMethod([
            $class,
            _ACTION_
        ]);
    }
}