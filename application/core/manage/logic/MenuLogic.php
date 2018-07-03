<?php
namespace core\manage\logic;

use cms\Logic;
use think\Url;
use cms\Common;

class MenuLogic extends Logic
{

    /**
     * 新增菜单
     *
     * @param array $data            
     * @return number
     */
    public function addMenu($data)
    {
        $data['menu_flag'] = $this->processMenuFlag($data['menu_url'], $data['menu_build']);
        return $this->model->add($data);
    }

    /**
     * 保存菜单
     *
     * @param array $data            
     * @param array $map            
     * @return number
     */
    public function saveMenu($data, $map)
    {
        $data['menu_flag'] = $this->processMenuFlag($data['menu_url'], $data['menu_build']);
        return $this->model->save($data, $map);
    }

    /**
     * 菜单下拉选择
     *
     * @return array
     */
    public function getMenuSelect()
    {
        $menu_tree = $this->getMenuTree();
        $menu_select = [];
        $menu_select[] = [
            'name' => '无',
            'value' => 0
        ];
        foreach ($menu_tree['main_menu'] as $vo) {
            $menu_select[] = [
                'name' => $vo['menu_name'],
                'value' => $vo['menu_id']
            ];
            foreach ($menu_tree['sub_menu'][$vo['menu_id']] as $ko) {
                $menu_select[] = [
                    'name' => '--' . $ko['menu_name'],
                    'value' => $ko['menu_id']
                ];
            }
        }
        return $menu_select;
    }

    /**
     * 菜单树
     *
     * @return array
     */
    public function getMenuTree()
    {
        $menu = [
            'main_menu' => [],
            'sub_menu' => [],
            'sub_sub_menu' => []
        ];
        
        // 一级菜单
        $map = [
            'menu_pid' => 0
        ];
        $list = $this->model->where($map)
            ->order('menu_sort asc')
            ->select();
        
        $main_pids = [];
        foreach ($list as $vo) {
            $main_pids[] = $vo['id'];
            $menu['main_menu'][$vo['id']] = [
                'menu_id' => $vo['id'],
                'menu_name' => $vo['menu_name']
            ];
            $menu['sub_menu'][$vo['id']] = [];
        }
        
        // 二级菜单
        $map = [
            'menu_pid' => [
                'in',
                $main_pids
            ]
        ];
        $list = $this->model->where($map)
            ->order('menu_sort asc')
            ->select();
        
        $sub_pids = [];
        foreach ($list as $vo) {
            $sub_pids[] = $vo['id'];
            $menu['sub_menu'][$vo['menu_pid']][$vo['id']] = [
                'menu_id' => $vo['id'],
                'menu_name' => $vo['menu_name']
            ];
            $menu['sub_sub_menu'][$vo['id']] = [];
        }
        
        // 三级菜单
        $map = [
            'menu_pid' => [
                'in',
                $sub_pids
            ]
        ];
        $list = $this->model->where($map)
            ->order('menu_sort asc')
            ->select();
        
        $sub_pids = [];
        foreach ($list as $vo) {
            $sub_pids[] = $vo['id'];
            $menu['sub_sub_menu'][$vo['menu_pid']][$vo['id']] = [
                'menu_id' => $vo['id'],
                'menu_name' => $vo['menu_name']
            ];
        }
        
        return $menu;
    }

    /**
     * 当前菜单
     *
     * @param string $menu_flag            
     * @return array
     */
    public function getCurrentMenu($menu_flag = null)
    {
        // 当前菜单
        $current_menu = $this->getMenuByFlag($menu_flag);
        if (empty($current_menu)) {
            return null;
        }
        
        // 上级菜单
        $parent_menu = $this->model->get($current_menu['menu_pid']);
        if ($parent_menu['menu_pid'] > 0) {
            return $this->getCurrentMenu($parent_menu['menu_flag']);
        } else {
            return $current_menu;
        }
    }

    /**
     * 主菜单
     *
     * @param number $user_id            
     * @return array
     */
    public function getMainMenu($user_id)
    {
        // 当前菜单
        $current_menu = $this->getCurrentMenu();
        
        $main_menu = $this->getMenuByPid(0, $user_id);
        foreach ($main_menu as &$menu) {
            
            // 菜单权限
            if (empty($menu['menu_url_origin'])) {
                $menu_url = $this->getMainMenuUrl($menu['menu_id'], $user_id);
                is_null($menu_url) || $menu['menu_url'] = $menu_url;
            }
            
            if ($current_menu && $menu['menu_id'] == $current_menu['menu_pid']) {
                $menu['menu_active'] = 1;
            } else {
                $menu['menu_active'] = 0;
            }
        }
        unset($menu);
        
        return $main_menu;
    }

    /**
     * 主菜单链接
     */
    public function getMainMenuUrl($menu_id, $user_id)
    {
        $auth_menu = MemberLogic::instance()->getUserMenu($user_id);
        if (! in_array($menu_id, $auth_menu)) {
            return null;
        }
        
        $map = array(
            'menu_status' => 1,
            'menu_pid' => $menu_id,
            'id' => [
                'in',
                $auth_menu
            ]
        );
        $menu = $this->model->where($map)
            ->order('menu_sort asc')
            ->find();
        return $menu ? Url::build($menu['menu_url']) : '';
    }

    /**
     * 侧边菜单
     *
     * @param number $user_id            
     * @return array
     */
    public function getSiderMenu($user_id)
    {
        // 当前菜单
        $current_menu = $this->getCurrentMenu();
        
        if (empty($current_menu)) {
            return [];
        } else {
            $sider_menu = $this->getMenuByPid($current_menu['menu_pid'], $user_id);
            foreach ($sider_menu as &$menu) {
                if (isset($menu['sub_menu'])) {
                    $menu['menu_active'] = 0;
                    foreach ($menu['sub_menu'] as &$item) {
                        if ($item['menu_id'] == $current_menu['id']) {
                            $item['menu_active'] = 1;
                            $menu['menu_active'] = 1;
                        } else {
                            $item['menu_active'] = 0;
                        }
                    }
                    unset($item);
                } else {
                    if ($menu['menu_id'] == $current_menu['id']) {
                        $menu['menu_active'] = 1;
                    } else {
                        $menu['menu_active'] = 0;
                    }
                }
            }
            unset($menu);
            return $sider_menu;
        }
    }

    /**
     * 根据标识获取菜单
     *
     * @param string $menu_flag            
     * @return array
     */
    public function getMenuByFlag($menu_flag = null)
    {
        // 默认当前操作
        if (empty($menu_flag)) {
            if (defined('_MODULE_')) {
                $menu_flag = 'module' . '/' . _MODULE_ . '/' . _CONTROLLER_ . '/' . _ACTION_;
            } else {
                $menu_flag = Common::getCurrentAction();
            }
        }
        
        $map = array(
            'menu_flag' => $menu_flag,
            'menu_pid' => array(
                'gt',
                0
            )
        );
        return $this->model->where($map)->find();
    }

    /**
     * 根据Pid获取菜单
     *
     * @param number $menu_pid            
     * @param number $user_id            
     * @return array
     */
    public function getMenuByPid($menu_pid, $user_id)
    {
        $auth_menu = MemberLogic::instance()->getUserMenu($user_id);
        $map = array(
            'menu_status' => 1,
            'menu_pid' => $menu_pid,
            'id' => [
                'in',
                $auth_menu
            ]
        );
        $list = $this->model->where($map)
            ->order('menu_sort asc')
            ->select();
        
        $menu = array();
        foreach ($list as $v) {
            if ($v['menu_group'] && $menu_pid > 0) {
                $key = 'group_' . md5($v['menu_group']);
                if (! isset($menu[$key])) {
                    $menu[$key] = [
                        'menu_name' => $v['menu_group'],
                        'sub_menu' => []
                    ];
                }
                $menu[$key]['sub_menu'][] = $this->getMenuItem($v);
            } else {
                $key = 'menu_' . $v['id'];
                $menu[$key] = $this->getMenuItem($v);
            }
        }
        
        return $menu;
    }

    /**
     * 获取菜单项
     *
     * @param array $item            
     * @return array
     */
    public function getMenuItem($item)
    {
        return [
            'menu_id' => $item['id'],
            'menu_name' => $item['menu_name'],
            'menu_url_origin' => $item['menu_url'],
            'menu_url' => $item['menu_build'] ? Url::build($item['menu_url']) : $item['menu_url'],
            'menu_target' => $item['menu_target']
        ];
    }

    /**
     * 菜单标识
     *
     * @param string $link            
     * @return string
     */
    public function processMenuFlag($link, $menu_build = true)
    {
        // 外链
        if ($menu_build == false) {
            return md5($link);
        }
        
        // 测试连接
        $url_test = 'path/test/domain';
        $url_path = str_replace($url_test . '.html', '', Url::build($url_test, '', true, true));
        
        // 相对url
        $url = Url::build($link, '', true, true);
        $url_relative = str_replace([
            $url_path,
            '.html'
        ], '', $url);
        
        // Url标识
        $arr = explode('/', $url_relative);
        if (strpos($link, '@module') !== false) {
            $arr = array_slice($arr, 0, 4);
        } else {
            $arr = array_slice($arr, 0, 3);
        }
        return implode('/', $arr);
    }

    /**
     * 菜单状态
     *
     * @return array
     */
    public function menuStatus()
    {
        return [
            [
                'name' => '显示',
                'value' => 1
            ],
            [
                'name' => '隐藏',
                'value' => 0
            ]
        ];
    }

    /**
     * 打开方式
     *
     * @return array
     */
    public function menuTarget()
    {
        return [
            [
                'name' => '当前窗口',
                'value' => '_self'
            ],
            [
                'name' => '新窗口',
                'value' => '_blank'
            ]
        ];
    }

    /**
     * 是否需要huild
     *
     * @return array
     */
    public function menuBuild()
    {
        return [
            [
                'name' => '需要',
                'value' => 1
            ],
            [
                'name' => '不需要',
                'value' => 0
            ]
        ];
    }
}