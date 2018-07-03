<?php
namespace app\manage\block;

class SubmitBlock extends Block
{

    /**
     * form
     *
     * @param array $data            
     * @return string
     */
    public static function form($data = [])
    {
        $data = array_merge(self::$default_form, $data);
        
        $html = '<div class="am-g am-margin-top-sm">';
        $html .= '<div class="am-u-sm-' . $data['l_sm_num'] . ' am-u-md-' . $data['l_md_num'] . ' am-text-right">' . $data['title'] . '</div>';
        $html .= '<div class="am-u-sm-' . $data['r_sm_num'] . ' am-u-md-' . $data['r_md_num'] . ' am-u-end">';
        $html .= '<button type="button" class="am-btn am-btn-sm am-btn-primary ajax-post" target-form="' . $data['target'] . '">' . $data['text_ok'] . '</button>';
        $html .= '&nbsp;&nbsp;&nbsp;&nbsp;';
        $html .= '<button type="button" class="am-btn am-btn-sm am-btn-default nd-backward">' . $data['text_cancel'] . '</button>';
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }
}