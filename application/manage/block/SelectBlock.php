<?php
namespace app\manage\block;

class SelectBlock extends Block
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
        $html .= '<select name="' . $data['name'] . '" data-am-selected="{btnSize: \'sm\'}">';
        foreach ($data['list'] as $vo) {
            if ($data['value'] === $vo['value']) {
                $html .= '<option selected value="' . $vo['value'] . '">' . $vo['name'] . '</option>';
            } else {
                $html .= '<option value="' . $vo['value'] . '">' . $vo['name'] . '</option>';
            }
        }
        $html .= '</select>';
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
        
        $html = '<div class="am-u-sm-' . $data['sm_num'] . ' am-u-md-' . $data['md_num'] . ' am-u-end">';
        $html .= '<div class="am-form-group"><label>' . $data['title'] . '</label>';
        $html .= '<select name="' . $data['name'] . '" class="nd-search-field" data-am-selected="{btnSize: \'sm\'}">';
        if ($data['all']) {
            $default = isset($data['default'])&&!empty($data['default'])?$data['default']:'不限';
            $html .= '<option value="**">' . $default . '</option>';
        }
        foreach ($data['list'] as $vo) {
            if ($data['value'] === $vo['value']) {
                $html .= '<option selected value="' . $vo['value'] . '">' . $vo['name'] . '</option>';
            } else {
                $html .= '<option value="' . $vo['value'] . '">' . $vo['name'] . '</option>';
            }
        }
        $html .= '</select><label>' . $data['end_title'] . '</label>';
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }
}