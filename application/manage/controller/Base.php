<?php
namespace app\manage\controller;

use think\Config;
use think\Request;
use cms\Controller;
use app\manage\logic\AuthLogic;
use app\manage\logic\LoginLogic;
use core\manage\logic\MenuLogic;

class Base extends Controller
{

    /**
     * 用户ID
     *
     * @var unknown
     */
    protected $user_id;

    /**
     * 每页条数
     *
     * @var unknown
     */
    protected $rows_num;

    /**
     * (non-PHPdoc)
     *
     * @see \app\common\controller\Root::_initialize()
     */
    protected function _initialize()
    {
        $public_action = Config::get('manage_public_action');
        if (! AuthLogic::isPublicAction($public_action)) {
            
            // 验证登录
            $this->verifyLogin();
            
            // 验证权限
            $this->verifyAuth();
            
            // 创建菜单
            Request::instance()->isAjax() || $this->buildMenu();
            
            // 每页条数
            $this->rows_num = Config::get('manage_rows_num') ?  : 12;
        }
    }

    /**
     * !CodeTemplates.overridecomment.nonjd!
     *
     * @see \app\common\controller\Root::fetch()
     */
    protected function fetch($template = '', $vars = [], $replace = [], $config = [])
    {
        $view_path = dirname(dirname(__DIR__)) . '/' . Request::instance()->module() . '/view/' . Config::get('manage_theme') . '/';
        $this->view->config('view_path', $view_path);
        return parent::fetch($template, $vars, $replace, $config);
    }

    /**
     * 验证登录
     */
    protected function verifyLogin()
    {
        $user = LoginLogic::getLoginUser();
        if (empty($user)) {
            responseRedirect('start/login');
        } else {
            // 用户ID
            $this->user_id = $user['user_id'];
            
            // 用户信息
            if (! Request::instance()->isAjax()) {
                $manage_user = LoginLogic::gteLoginUserInfo();
                $this->assign('manage_user', $manage_user);
                
                // 管理首页
                $this->assign('manage_url', $user['manage_url']);
            }
        }
    }

    /**
     * 验证权限
     */
    protected function verifyAuth()
    {
        if (! AuthLogic::isAuthAction($this->user_id)) {
            $content = $this->error('你没有权限访问该页面');
            responseReturn($content, 'text');
        }
    }

    /**
     * 创建菜单
     */
    protected function buildMenu()
    {
        $menu_logic = MenuLogic::instance();
        
        // 主菜单
        $main_menu = $menu_logic->getMainMenu($this->user_id);
        $this->assign('main_menu', $main_menu);
        
        // 侧边菜单
        $sider_menu = $menu_logic->getSiderMenu($this->user_id);
        $this->assign('sider_menu', $sider_menu);
    }
}