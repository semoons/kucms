<?php
namespace app\manage\block;

class DateBlock extends Block
{

    /**
     * form
     *
     * @param array $data            
     * @return string
     */
    public static function form($data = [])
    {
        if (isset($data['format']) && ! empty($data['format'])) {
            $data['attr'] = 'data-am-datepicker="{format: \'' . $data['format'] . '\'}"';
        } else {
            $data['attr'] = 'data-am-datepicker';
        }
        
        return TextBlock::form($data);
    }

    /**
     * search
     *
     * @param array $data            
     * @return string
     */
    public static function search($data = [])
    {
        if (isset($data['format']) && ! empty($data['format'])) {
            $data['attr'] = 'data-am-datepicker="{format: \'' . $data['format'] . '\'}"';
        } else {
            $data['attr'] = 'data-am-datepicker';
        }
        
        return TextBlock::search($data);
    }
}