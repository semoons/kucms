<?php
namespace app\manage\block;

abstract class Block
{

    /**
     * 表单配置
     *
     * @var unknown
     */
    protected static $default_form = [
        'l_sm_num' => 4,
        'l_md_num' => 2,
        'r_sm_num' => 8,
        'r_md_num' => 7,
        
        'title' => '0',
        'type' => 'text',
        'rows' => 5,
        'holder' => '',
        'name' => '',
        'value' => '',
        'list' => [],
        'tip' => '',
        
        'class' => '',
        'style' => '',
        'attr' => '',
        'option' => '',
        
        'text_ok' => '确定',
        'text_cancel' => '取消',
        'target' => 'ajax-form'
    ];

    /**
     * form
     *
     * @param array $data            
     * @return string
     */
    public static function form($data = [])
    {
        return '';
    }

    /**
     * 搜索配置
     *
     * @param array $data            
     * @var unknown
     */
    protected static $default_search = [
        'sm_num' => 12,
        'md_num' => 2,
        
        'holder' => '',
        'name' => '',
        'value' => '',
        'list' => [],
        'all' => 1,
        
        'class' => '',
        'style' => '',
        'attr' => '',
        
        'text' => '搜索',
        'target' => 'search-form'
    ];

    /**
     * form
     *
     * @param array $data            
     * @return string
     */
    public static function search($data = [])
    {
        return '';
    }
}