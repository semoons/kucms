<?php
namespace app\manage\block;

class EditorBlock extends Block
{

    /**
     * form
     *
     * @param array $data            
     * @return string
     */
    public static function form($data = [])
    {
        if (! isset($data['class'])) {
            $data['class'] = '';
        }
        $data['class'] .= 'nd-editor-wang';
        
        $data['attr'] = 'nd-target="' . $data['name'] . '"';
        
        $data['rows'] = 30;
        
        return TextareaBlock::form($data);
    }
}