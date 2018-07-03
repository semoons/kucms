<?php
namespace app\manage\exception;

use cms\View;
use think\Config;
use think\Request;
use think\Response;
use newday\common\Format;
use think\exception\Handle;

class Http extends Handle
{

    /**
     * !CodeTemplates.overridecomment.nonjd!
     *
     * @see \think\exception\Handle::render()
     */
    public function render(\Exception $e)
    {
        // 父类处理
        parent::render($e);
        
        try {
            $request = Request::instance();
            $msg = $e->getMessage();
            
            if ($request->isAjax()) {
                $data = Format::formatJump(0, $msg);
                $response = Response::create($data, 'json');
            } else {
                
                $view = new View();
                
                // 标题
                $view->assign('site_title', $msg);
                
                // 跳转
                $jump = Format::formatJump(0, $msg);
                $view->assign('jump', $jump);
                
                // 主题
                $view_path = dirname(__DIR__) . '/view/' . Config::get('manage_theme') . '/';
                $view->config('view_path', $view_path);
                
                $data = $view->fetch('common/jump');
                
                $response = Response::create($data);
            }
        } catch (\Exception $e) {
            return parent::render($e);
        }
        
        return $response;
    }
}