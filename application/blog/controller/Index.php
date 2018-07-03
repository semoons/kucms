<?php
namespace app\blog\controller;

use think\Request;
use core\blog\logic\ArticleLogic;
use core\blog\logic\ArticleCateLogic;

class Index extends Base
{

    /**
     * 首页
     *
     * @param Request $request            
     * @return string
     */
    public function index(Request $request)
    {
        $this->site_title = '博客首页';
        
        $map = [
            'article_status' => 1
        ];
        return $this->displayList($map);
    }

    /**
     * 分类
     *
     * @param Request $request            
     * @return string
     */
    public function cate(Request $request)
    {
        
        // 分类
        $cate_name = $request->param('name');
        if (empty($cate_name)) {
            return $this->error('链接错误');
        }
        
        $map = [
            'cate_flag' => $cate_name
        ];
        $cate = ArticleCateLogic::model()->where($map)->find();
        if (empty($cate)) {
            return $this->error('分类不存在');
        } elseif ($cate['cate_status'] == 0) {
            return $this->error('分类未启用');
        }
        
        $this->menu_class = 'cate_' . $cate_name;
        $map = [
            'article_status' => 1,
            'article_cate' => $cate['id']
        ];
        return $this->displayList($map);
    }

    /**
     * 详情
     *
     * @param Request $request            
     * @return string
     */
    public function show(Request $request)
    {
        $article_key = $request->param('key');
        if (empty($article_key)) {
            return $this->error('链接错误');
        }
        
        $map = [
            'article_key' => $article_key
        ];
        $article = ArticleLogic::model()->where($map)->find();
        if (empty($article)) {
            return $this->error('文章不存在');
        } elseif ($article['article_status'] == 0) {
            return $this->error('文章审核中');
        }
        
        $this->site_title = $article['article_title'];
        $this->menu_class = 'index_index';
        $this->assign('article', $article);
        
        return $this->fetch();
    }

    /**
     * 显示列表
     *
     * @param array $map            
     * @return string
     */
    protected function displayList(array $map)
    {
        // 幻灯片
        $slider = ArticleLogic::model()->where($map)
            ->order('rand()')
            ->limit(4)
            ->select();
        $this->assign('slider', $slider);
        
        // 总数
        $total_count = ArticleLogic::model()->where($map)->count();
        $this->assign('total_count', $total_count);
        
        // 列表
        $list = ArticleLogic::model()->where($map)
            ->order('id desc')
            ->paginate(4);
        $this->assign('list', $list);
        
        // 分页
        $page = $list->render();
        $this->assign('page', $page);
        
        return $this->fetch('index');
    }
}