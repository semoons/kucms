<?php
namespace core\blog\model;

use cms\Model;

class ArticleModel extends Model
{

    /**
     * 去前缀表名
     *
     * @var unknown
     */
    protected $name = 'blog_article';

    /**
     * 去前缀表名
     *
     * @var unknown
     */
    protected $autoWriteTimestamp = true;
}