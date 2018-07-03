<?php
namespace app\manage\block;

class TagBlock extends Block
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
        $data['class'] .= 'nd-tag';
        
        return TextBlock::form($data);
    }
}