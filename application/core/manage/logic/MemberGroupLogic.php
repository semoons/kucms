<?php
namespace core\manage\logic;

use cms\Logic;

class MemberGroupLogic extends Logic
{

    /**
     * 群组菜单
     *
     * @param number $group_id            
     * @return array
     */
    public function getGroupMenu($group_id)
    {
        $map = [
            'id' => $group_id
        ];
        $group = $this->model->field('group_menus')
            ->where($map)
            ->find();
        if ($group && $group['group_menus']) {
            return explode(',', $group['group_menus']);
        }
        return [];
    }

    /**
     * 群组下拉选择
     *
     * @return array
     */
    public function getGroupSelect()
    {
        $group_list = $this->model->select();
        $group_select = [];
        foreach ($group_list as $vo) {
            $group_select[] = [
                'name' => $vo['group_name'],
                'value' => $vo['id']
            ];
        }
        return $group_select;
    }

    /**
     * 群组状态
     *
     * @return array
     */
    public function groupStatus()
    {
        return [
            [
                'name' => '启用',
                'value' => 1
            ],
            [
                'name' => '禁用',
                'value' => 0
            ]
        ];
    }
}