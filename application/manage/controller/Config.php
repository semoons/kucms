<?php
namespace app\manage\controller;

use think\Url;
use think\Request;
use core\manage\logic\ConfigLogic;

class Config extends Base
{

    /**
     * 配置列表
     *
     * @param Request $request            
     * @return string
     */
    public function index(Request $request)
    {
        $this->site_title = '配置列表';
        
        $map = [];
        $config_model = ConfigLogic::model();
        
        // 配置分组
        $group_name = $request->param('group', '');
        if (! empty($group_name)) {
            $map['config_group'] = $group_name;
        }
        $this->assign('group', $group_name);
        
        // total_count
        $total_count = $config_model->where($map)->count();
        $this->assign('total_count', $total_count);
        
        // list
        $list = $config_model->where($map)
            ->order('config_sort asc')
            ->paginate($this->rows_num);
        $this->assign('list', $list);
        $this->assign('page', $list->render());
        
        // group_list
        $group_list = $config_model->field('id, config_group')
            ->group('config_group')
            ->order('config_sort asc')
            ->select();
        $this->assign('group_list', $group_list);
        
        return $this->fetch();
    }

    /**
     * 添加配置
     *
     * @param Request $request            
     * @return string
     */
    public function addConfig(Request $request)
    {
        $config_logic = ConfigLogic::instance();
        if ($request->isPost()) {
            $data = [
                'config_name' => $request->param('config_name'),
                'config_type' => $request->param('config_type'),
                'config_title' => $request->param('config_title'),
                'config_group' => $request->param('config_group'),
                'config_sort' => $request->param('config_sort', 0),
                'config_extra' => $request->param('config_extra'),
                'config_remark' => $request->param('config_remark')
            ];
            
            // 验证数据
            $res = $config_logic->validate->scene('add')->check($data);
            if (! $res) {
                return $this->error($config_logic->validate->getError());
            }
            $config_logic->addConfig($data);
            
            return $this->success('添加配置成功', Url::build('config/index'));
        } else {
            $this->site_title = '添加配置';
            
            $config_type = $config_logic->getConfigType();
            $this->assign('config_type', $config_type);
            
            return $this->fetch();
        }
    }

    /**
     * 编辑配置
     *
     * @param Request $request            
     * @return string
     */
    public function editConfig(Request $request)
    {
        $config_id = $request->param('config_id');
        if (empty($config_id)) {
            return $this->error('配置ID为空');
        }
        
        $config_logic = ConfigLogic::instance();
        if ($request->isPost()) {
            $data = [
                'config_name' => $request->param('config_name'),
                'config_type' => $request->param('config_type'),
                'config_title' => $request->param('config_title'),
                'config_group' => $request->param('config_group'),
                'config_sort' => $request->param('config_sort', 0),
                'config_extra' => $request->param('config_extra'),
                'config_remark' => $request->param('config_remark')
            ];
            
            // 验证数据
            $res = $config_logic->validate->scene('edit')->check($data);
            if (! $res) {
                return $this->error($config_logic->validate->getError());
            }
            $config_logic->saveConfig($data, $config_id);
            
            return $this->success('修改配置成功', Url::build('config/index'));
        } else {
            $this->site_title = '编辑配置';
            $this->assign('config_id', $config_id);
            
            $config_type = $config_logic->getConfigType();
            $this->assign('config_type', $config_type);
            
            $config = $config_logic->model->get($config_id);
            $this->assign('config', $config);
            
            return $this->fetch();
        }
    }
    
    /**
     * 更改配置
     *
     * @param Request $request
     * @return mixed
     */
    public function modifyConfig(Request $request)
    {
        $id = $request->param('id');
        if (empty($id)) {
            return $this->error('ID为空');
        }
    
        $field_arr = [
            'config_group',
            'config_sort'
        ];
        $field = $request->param('field');
        if (! in_array($field, $field_arr)) {
            return $this->error('非法的字段');
        }
    
        $value = $request->param('value', '');
        ConfigLogic::model()->modify($id, $field, $value);
    
        return $this->success('更改成功', Url::build('config/index'));
    }

    /**
     * 删除配置
     *
     * @param Request $request            
     * @return mixed
     */
    public function delConfig(Request $request)
    {
        $config_id = $request->param('config_id');
        if (empty($config_id)) {
            return $this->error('配置ID为空');
        }
        
        // 删除配置
        ConfigLogic::instance()->delConfig($config_id);
        
        return $this->success('删除配置成功', 'history.go(0);');
    }

    /**
     * 网站设置
     *
     * @return string
     */
    public function setting()
    {
        $this->site_title = '网站设置';
        
        // 配置列表
        $list = ConfigLogic::instance()->getGroupList();
        $this->assign('list', $list);
        
        return $this->fetch();
    }

    /**
     * 保存设置
     *
     * @param Request $request            
     * @return mixed
     */
    public function saveConfig(Request $request)
    {
        $config = $request->param('config/a', []);
        
        // 逐条保存
        $config_logic = ConfigLogic::instance();
        foreach ($config as $co => $vo) {
            $map = [
                'config_name' => $co
            ];
            $data = [
                'config_value' => is_array($vo) ? json_encode($vo, JSON_UNESCAPED_UNICODE) : $vo
            ];
            $config_logic->saveConfig($data, $map);
        }
        
        return $this->success('保存设置成功', 'history.go(0);');
    }
}