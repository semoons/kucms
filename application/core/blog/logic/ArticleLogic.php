<?php
namespace core\blog\logic;

use cms\Logic;

class ArticleLogic extends Logic
{

    /**
     * 添加文章
     *
     * @param array $data            
     * @return number
     */
    public function addArticle($data)
    {
        $data['article_key'] = substr(md5(time()), 8, 16);
        $data['article_tags'] = ',' . $data['article_tags'] . ',';
        return $this->model->add($data);
    }

    /**
     * 修改文章
     *
     * @param array $data            
     * @param mixed $map            
     * @return number
     */
    public function saveArticle($data, $map)
    {
        $data['article_tags'] = ',' . $data['article_tags'] . ',';
        return $this->model->save($data, $map);
    }

    /**
     * 文章状态
     *
     * @return array
     */
    public function articleStatus()
    {
        return [
            [
                'name' => '发布',
                'value' => 1
            ],
            [
                'name' => '待发布',
                'value' => 0
            ]
        ];
    }
}