<?php
namespace core\blog\logic;

use cms\Logic;

class ArticleCateLogic extends Logic
{

    /**
     * 分类列表
     *
     * @return array
     */
    public function getCateList()
    {
        $map = [
            'cate_status' => 1
        ];
        return $this->model->where($map)
            ->order('cate_sort asc')
            ->select();
    }

    /**
     * 分类状态
     *
     * @return array
     */
    public function cateStatus()
    {
        return [
            [
                'name' => '启用',
                'value' => 1
            ],
            [
                'name' => '禁用',
                'value' => 0
            ]
        ];
    }
}