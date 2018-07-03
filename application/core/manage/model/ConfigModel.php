<?php
namespace core\manage\model;

use cms\Model;

class ConfigModel extends Model
{

    /**
     * 去前缀表名
     *
     * @var unknown
     */
    protected $name = 'manage_config';

    /**
     * 自动写入时间戳
     * 
     * @var unknown
     */
    protected $autoWriteTimestamp = true;
}