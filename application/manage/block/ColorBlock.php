<?php
namespace app\manage\block;

class ColorBlock extends Block
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
        $data['class'] .= 'nd-color';
        
        return TextBlock::form($data);
    }
}