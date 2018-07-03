<?php
namespace app\manage\block;

class ArrayBlock extends Block
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
        
        $data['class'] .= ' nd-editor-ace am-hide';
        $data['attr'] = 'nd-type="json" nd-target="nd-editor-ace-' . $data['name'] . '"';
        
        return TextareaBlock::form($data);
    }
}