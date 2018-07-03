<?php
namespace app\blog\controller;

use think\Request;
use cms\Controller;
use core\blog\logic\ArticleCateLogic;

class Base extends Controller
{

    /**
     * 当前菜单样式
     *
     * @var unknown
     */
    protected $menu_class;

    /**
     * 初始化
     */
    public function _initialize()
    {
        parent::_initialize();
        
        // 默认样式
        $request = Request::instance();
        $this->menu_class = strtolower($request->controller()) . '_' . strtolower($request->action());
        
        // 分类
        $cate_list = ArticleCateLogic::instance()->getCateList();
        foreach ($cate_list as &$vo) {
            $vo['cate_class'] = 'cate_' . $vo['cate_flag'];
        }
        unset($vo);
        $this->assign('cate_list', $cate_list);
    }

    /**
     * !CodeTemplates.overridecomment.nonjd!
     *
     * @see \app\common\controller\Root::fetch()
     */
    protected function fetch($template = '', $vars = [], $replace = [], $config = [])
    {
        // 菜单样式
        $this->assign('menu_class', $this->menu_class);
        
        return parent::fetch($template, $vars, $replace, $config);
    }
}