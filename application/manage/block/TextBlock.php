<?php
namespace app\manage\block;

class TextBlock extends Block
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
        $html .= '<input type="' . $data['type'] . '" class="am-input-sm ' . $data['class'] . '" name="' . $data['name'] . '" value="' . $data['value'] . '" placeholder="' . $data['holder'] . '" style="' . $data['style'] . '" ' . $data['attr'] . ' />';
        
        // 提示
        if (! empty($data['tip'])) {
            $html .= '(' . $data['tip'] . ')';
        }
        
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }

    /**
     * search
     *
     * @param array $data            
     * @return string
     */
    public static function search($data = [])
    {
        $data = array_merge(self::$default_search, $data);
        
        $html = '<div class="am-u-sm-' . $data['sm_num'] . ' am-u-md-' . $data['md_num'] . '">';
        $html .= '<div class="am-form-group am-input-group-sm">';
        $html .= '<input type="text" class="am-form-field nd-search-field" name="' . $data['name'] . '" placeholder="' . $data['holder'] . '" value="' . $data['value'] . '" style="' . $data['style'] . '" ' . $data['attr'] . ' />';
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }
}