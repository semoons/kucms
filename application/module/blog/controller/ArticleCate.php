<?php
namespace module\blog\controller;

use think\Request;
use core\blog\logic\ArticleLogic;
use core\blog\logic\ArticleCateLogic;

class ArticleCate extends Base
{

    /**
     * 分类列表
     *
     * @param Request $request            
     * @return string
     */
    public function index(Request $request)
    {
        $this->site_title = '分类列表';
        
        $list = ArticleCateLogic::model()->order('cate_sort asc, id desc')->select();
        $this->assign('list', $list);
        
        return $this->fetch();
    }

    /**
     * 添加分类
     *
     * @param Request $request            
     * @return mixed
     */
    public function addCate(Request $request)
    {
        $cate_logic = ArticleCateLogic::instance();
        if ($request->isPost()) {
            $data = [
                'cate_name' => $request->param('cate_name'),
                'cate_flag' => $request->param('cate_flag'),
                'cate_info' => $request->param('cate_info'),
                'cate_sort' => $request->param('cate_sort', 0),
                'cate_status' => $request->param('cate_status', 0)
            ];
            
            // 验证
            $res = $cate_logic->validate->scene('add')->check($data);
            if (! $res) {
                return $this->error($cate_logic->validate->getError());
            }
            
            // 添加
            $cate_logic->model->add($data);
            
            return $this->success('添加分类成功', moduleUrl('index'));
        } else {
            $this->site_title = '新增分类';
            
            // 分类状态
            $cate_status = $cate_logic->cateStatus();
            $this->assign('cate_status', $cate_status);
            
            return $this->fetch();
        }
    }

    /**
     * 编辑分类
     *
     * @param Request $request            
     * @return mixed
     */
    public function editCate(Request $request)
    {
        $cate_id = $request->param('cate_id');
        if (empty($cate_id)) {
            return $this->error('分类ID为空');
        }
        
        $cate_logic = ArticleCateLogic::instance();
        if ($request->isPost()) {
            $data = [
                'cate_name' => $request->param('cate_name'),
                'cate_flag' => $request->param('cate_flag'),
                'cate_info' => $request->param('cate_info'),
                'cate_sort' => $request->param('cate_sort', 0),
                'cate_status' => $request->param('cate_status', 0)
            ];
            
            // 验证
            $res = $cate_logic->validate->scene('edit')->check($data);
            if (! $res) {
                return $this->error($cate_logic->validate->getError());
            }
            
            // 保存
            $cate_logic->model->save($data, $cate_id);
            
            return $this->success('修改分类成功', moduleUrl('index'));
        } else {
            
            $this->site_title = '编辑分类';
            $this->assign('cate_id', $cate_id);
            
            // 分类
            $cate = $cate_logic->model->get($cate_id);
            $this->assign('cate', $cate);
            
            // 状态
            $cate_status = $cate_logic->cateStatus();
            $this->assign('cate_status', $cate_status);
            
            return $this->fetch();
        }
    }

    /**
     * 删除分类
     *
     * @param Request $request            
     * @return mixed
     */
    public function delCate(Request $request)
    {
        $cate_id = $request->param('cate_id');
        if (empty($cate_id)) {
            return $this->error('分类ID为空');
        }
        
        // 是否分类下有文章
        $map = [
            'article_cate' => $cate_id
        ];
        $record = ArticleLogic::model()->field('id')
            ->where($map)
            ->find();
        if ($record) {
            return $this->error('请先删除或移动该分类下的文章');
        }
        
        // 删除分类
        ArticleCateLogic::model()->del($cate_id);
        
        return $this->success('删除分类成功', 'history.go(0);');
    }

    /**
     * 更改分类
     *
     * @param Request $request            
     * @return mixed
     */
    public function modifyCate(Request $request)
    {
        $id = $request->param('id');
        if (empty($id)) {
            return $this->error('ID为空');
        }
        
        $field_arr = [
            'cate_sort',
            'cate_status'
        ];
        $field = $request->param('field');
        if (! in_array($field, $field_arr)) {
            return $this->error('非法的字段');
        }
        
        $value = $request->param('value', '');
        ArticleCateLogic::model()->modify($id, $field, $value);
        
        return $this->success('更改成功', 'history.go(0);');
    }
}
