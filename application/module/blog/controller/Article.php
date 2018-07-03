<?php
namespace module\blog\controller;

use think\Request;
use core\blog\logic\ArticleLogic;
use core\blog\logic\ArticleCateLogic;

class Article extends Base
{

    /**
     * 文章列表
     *
     * @param Request $request            
     * @return string
     */
    public function index(Request $request)
    {
        $this->site_title = '文章列表';
        
        $article_logic = ArticleLogic::instance();
        
        // 条件
        $map = [
            'delete_time' => 0
        ];
        
        // 文章分类
        $cate = $request->param('cate');
        if (! empty($cate)) {
            $cate = intval($cate);
            $map['article_cate'] = $cate;
        }
        $this->assign('cate', $cate);
        
        // 文章状态
        $status = $request->param('status', '');
        if ($status != '') {
            $status = intval($status);
            $map['article_status'] = $status;
        }
        $this->assign('status', $status);
        
        // 关键词
        $keyword = $request->param('keyword');
        if ($keyword != '') {
            $map['article_title|article_author|article_content'] = [
                'like',
                '%' . $keyword . '%'
            ];
        }
        $this->assign('keyword', $keyword);
        
        // 总数
        $total_count = $article_logic->model->where($map)->count();
        $this->assign('total_count', $total_count);
        
        // 列表
        $list = $article_logic->model->where($map)
            ->order('article_sort asc, create_time desc, id desc')
            ->paginate(10);
        $this->assign('list', $list);
        $this->assign('page', $list->render());
        
        // 分类
        $cate_list = ArticleCateLogic::instance()->getCateList();
        foreach ($cate_list as &$vo) {
            $vo = [
                'name' => $vo['cate_name'],
                'value' => $vo['id']
            ];
        }
        unset($vo);
        $this->assign('cate_list', $cate_list);
        
        // 状态
        $article_status = $article_logic->articleStatus();
        $this->assign('article_status', $article_status);
        
        return $this->fetch();
    }

    /**
     * 添加文章
     *
     * @param Request $request            
     * @return string
     */
    public function addArticle(Request $request)
    {
        $article_logic = ArticleLogic::instance();
        if ($request->isPost()) {
            $data = [
                'article_title' => $request->param('article_title'),
                'article_cate' => $request->param('article_cate'),
                'article_author' => $request->param('article_author'),
                'article_tags' => $request->param('article_tags'),
                'article_info' => $request->param('article_info'),
                'article_cover' => $request->param('article_cover'),
                'article_origin' => $request->param('article_origin'),
                'article_content' => $request->param('article_content'),
                'article_status' => $request->param('article_status', 0),
                'create_time' => strtotime($request->param('create_time')),
                'article_sort' => $request->param('article_sort', 0)
            ];
            
            // 验证
            $res = $article_logic->validate->scene('add')->check($data);
            if (! $res) {
                return $this->error($article_logic->validate->getError());
            }
            
            // 添加
            $article_logic->addArticle($data);
            
            return $this->success('添加文章成功', moduleUrl('index'));
        } else {
            $this->site_title = '新增文章';
            
            // 分类
            $cate_list = ArticleCateLogic::instance()->getCateList();
            foreach ($cate_list as &$vo) {
                $vo = [
                    'name' => $vo['cate_name'],
                    'value' => $vo['id']
                ];
            }
            unset($vo);
            $this->assign('cate_list', $cate_list);
            
            // 状态
            $article_status = $article_logic->articleStatus();
            $this->assign('article_status', $article_status);
            
            return $this->fetch();
        }
    }

    /**
     * 编辑文章
     *
     * @param Request $request            
     * @return mixed
     */
    public function editArticle(Request $request)
    {
        $article_id = $request->param('article_id');
        if (empty($article_id)) {
            return $this->error('分类ID为空');
        }
        
        $article_logic = ArticleLogic::instance();
        if ($request->isPost()) {
            $data = [
                'article_title' => $request->param('article_title'),
                'article_author' => $request->param('article_author'),
                'article_info' => $request->param('article_info'),
                'article_cover' => $request->param('article_cover'),
                'article_cate' => $request->param('article_cate'),
                'article_tags' => $request->param('article_tags'),
                'article_origin' => $request->param('article_origin'),
                'article_sort' => $request->param('article_sort', 0),
                'article_content' => $request->param('article_content'),
                'create_time' => strtotime($request->param('create_time')),
                'article_status' => $request->param('article_status', 0)
            ];
            
            // 验证
            $res = $article_logic->validate->scene('edit')->check($data);
            if (! $res) {
                return $this->error($article_logic->validate->getError());
            }
            
            // 修改
            ArticleLogic::instance()->saveArticle($data, $article_id);
            
            return $this->success('修改文章成功', moduleUrl('index'));
        } else {
            
            $this->site_title = '编辑文章';
            $this->assign('article_id', $article_id);
            
            // 文章
            $article = $article_logic->model->get($article_id);
            $this->assign('article', $article);
            
            // 分类
            $cate_list = ArticleCateLogic::instance()->getCateList();
            foreach ($cate_list as &$vo) {
                $vo = [
                    'name' => $vo['cate_name'],
                    'value' => $vo['id']
                ];
            }
            unset($vo);
            $this->assign('cate_list', $cate_list);
            
            // 状态
            $article_status = $article_logic->articleStatus();
            $this->assign('article_status', $article_status);
            
            return $this->fetch();
        }
    }

    /**
     * 删除文章
     *
     * @param Request $request            
     * @return mixed
     */
    public function delArticle(Request $request)
    {
        $article_id = $request->param('article_id');
        if (empty($article_id)) {
            return $this->error('文章ID为空');
        }
        
        // 逻辑删除
        ArticleLogic::model()->del($article_id, true);
        
        return $this->success('删除文章成功', 'history.go(0);');
    }

    /**
     * 更改文章
     *
     * @param Request $request            
     * @return mixed
     */
    public function modifyArticle(Request $request)
    {
        $id = $request->param('id');
        if (empty($id)) {
            return $this->error('ID为空');
        }
        
        $field_arr = [
            'article_cate',
            'article_sort',
            'article_status'
        ];
        $field = $request->param('field');
        if (! in_array($field, $field_arr)) {
            return $this->error('非法的字段');
        }
        
        $value = $request->param('value', '');
        ArticleLogic::model()->modify($id, $field, $value);
        
        return $this->success('更改成功', 'history.go(0);');
    }
}
