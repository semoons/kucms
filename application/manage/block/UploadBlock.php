<?php
namespace app\manage\block;

class UploadBlock extends Block
{

    public static function form($data = []) {
        $data = array_merge(self::$default_form, $data);
        $data['uuid'] = md5(serialize($data));

        $title = $data['title'] ? $data['title'] : '导入';
        $name = $data['name'] ? $data['name'] : 'upload_file';


        $html = '';

        if (isset($data['am']) && !empty($data['am'])) {        
            $html .= '<div class="am-u-sm-' . $data['l_sm_num'] . ' am-u-md-' . $data['l_md_num'] . '">';
            $html .= '<div class="am-btn-toolbar">';
            $html .= '    <div class="am-btn-group am-btn-group-xs">';
        }

        $html .= '<div class="am-form-group am-form-file am-btn am-btn-default">';
        $html .= '<span class="am-icon-plus"></span> ' . $title;
        $html .= '<input type="file" name="' . $name . '" id="' . $name . '">';
        $html .= '</div>';

        if (!empty($data['template'])) {
            $html .= '<a class="am-btn am-btn-default" href="' . $data['template'] . '">';
            $html .= '    <span class="am-icon-cloud-download"></span> 下载导入模板';
            $html .= '</a>';
        }
        if (isset($data['am']) && !empty($data['am'])) { 
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';
        }

        return $html;
    }
}