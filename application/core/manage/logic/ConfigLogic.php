<?php
namespace core\manage\logic;

use cms\Logic;
use think\Cache;

class ConfigLogic extends Logic
{

    /**
     * 缓存key
     *
     * @var unknown
     */
    const CACHE_KEY = 'common_config_cache';

    /**
     * 配置分组
     *
     * @return array
     */
    public function getGroupList()
    {
        $list = $this->model->order('config_sort asc')->select();
        $res = [];
        foreach ($list as $vo) {
            $group = $vo['config_group'];
            if (! isset($res[$group])) {
                $res[$group] = [
                    'name' => $group,
                    'key' => md5($group),
                    'list' => []
                ];
            }
            $res[$group]['list'][] = $this->processGroupItem($vo);
        }
        return $res;
    }

    /**
     * 处理分组
     *
     * @param array $item            
     * @return array
     */
    public function processGroupItem($item)
    {
        switch ($item['config_type']) {
            case 'array':
                $arr = empty($item['config_extra']) ? [] : explode(',', $item['config_extra']);
                $value = empty($item['config_value']) ? [] : json_decode($item['config_value'], true);
                foreach ($arr as $vo) {
                    if (! isset($value[$vo])) {
                        $value[$vo] = '';
                    }
                }
                $item['config_value'] = json_encode($value, JSON_UNESCAPED_UNICODE);
                break;
            case 'radio':
            case 'checkbox':
            case 'select':
                $arr = empty($item['config_extra']) ? [] : explode('|', $item['config_extra']);
                $extra = [];
                foreach ($arr as $vo) {
                    list ($value, $name) = explode(':', $vo);
                    $extra[] = [
                        'name' => $name,
                        'value' => $value
                    ];
                }
                $item['config_extra'] = $extra;
                break;
        }
        return $item;
    }

    /**
     * 获取配置
     *
     * @return array
     */
    public function getConfig()
    {
        $config = Cache::get(self::CACHE_KEY);
        if (empty($config)) {
            $list = $this->model->select();
            $config = [];
            foreach ($list as $vo) {
                switch ($vo['config_type']) {
                    case 'checkbox':
                    case 'array':
                        $vo['config_value'] = empty($vo['config_value']) ? [] : @json_decode($vo['config_value'], true);
                        break;
                }
                $config[$vo['config_name']] = $vo['config_value'];
            }
            
            // 处理变量
            $config = $this->processConfig($config);
            
            Cache::set(self::CACHE_KEY, $config);
        }
        return $config;
    }

    /**
     * 添加配置
     *
     * @param array $data            
     * @return boolean
     */
    public function addConfig($data)
    {
        $this->model->add($data);
        
        return $this->clearCache();
    }

    /**
     * 保存配置
     *
     * @param array $data            
     * @param mixed $map            
     * @return boolean
     */
    public function saveConfig($data, $map)
    {
        $this->model->save($data, $map);
        
        return $this->clearCache();
    }

    /**
     * 更改配置
     *
     * @param number $id            
     * @param string $field            
     * @param string $value            
     * @return boolean
     */
    public function modifyConfig($id, $field, $value)
    {
        $this->model->modify($id, $field, $value);
        
        return $this->clearCache();
    }

    /**
     * 删除配置
     *
     * @param mixed $map            
     * @return boolean
     */
    public function delConfig($map)
    {
        $this->model->del($map);
        
        return $this->clearCache();
    }

    /**
     * 清除缓存
     *
     * @return boolean
     */
    public function clearCache()
    {
        return Cache::rm(self::CACHE_KEY);
    }

    /**
     * 处理配置
     *
     * @param array $config            
     * @return array
     */
    public function processConfig($config)
    {
        $var_list = $this->getVariableList();
        foreach ($config as $co => $vo) {
            if (is_array($vo)) {
                $config[$co] = $this->processConfig($vo);
            } else {
                $config[$co] = str_replace($var_list[0], $var_list[1], $config[$co]);
            }
        }
        return $config;
    }

    /**
     * 变量列表
     *
     * @return array
     */
    public function getVariableList()
    {
        if (empty($this->var_list)) {
            $this->var_list = [
                [],
                []
            ];
            $list = [
                '{EXT}' => EXT,
                '{DS}' => DS,
                '{THINK_PATH}' => THINK_PATH,
                '{WEB_PATH}' => WEB_PATH,
                '{ROOT_PATH}' => ROOT_PATH,
                '{APP_PATH}' => APP_PATH,
                '{CONF_PATH}' => CONF_PATH,
                '{LIB_PATH}' => LIB_PATH,
                '{CORE_PATH}' => CORE_PATH,
                '{TRAIT_PATH}' => TRAIT_PATH,
                '{EXTEND_PATH}' => EXTEND_PATH,
                '{VENDOR_PATH}' => VENDOR_PATH,
                '{RUNTIME_PATH}' => RUNTIME_PATH,
                '{LOG_PATH}' => LOG_PATH,
                '{CACHE_PATH}' => CACHE_PATH,
                '{TEMP_PATH}' => TEMP_PATH
            ];
            foreach ($list as $co => $vo) {
                $this->var_list[0][] = $co;
                $this->var_list[1][] = $vo;
            }
        }
        return $this->var_list;
    }

    /**
     * 配置类型
     *
     * @return array
     */
    public function getConfigType()
    {
        return [
            [
                'name' => '文本',
                'value' => 'text'
            ],
            [
                'name' => '文本域',
                'value' => 'textarea'
            ],
            [
                'name' => '标签',
                'value' => 'tag'
            ],
            [
                'name' => '日期',
                'value' => 'date'
            ],
            [
                'name' => '颜色',
                'value' => 'color'
            ],
            [
                'name' => '图片',
                'value' => 'image'
            ],
            [
                'name' => '文件',
                'value' => 'file'
            ],
            [
                'name' => '多选',
                'value' => 'checkbox'
            ],
            [
                'name' => '单选',
                'value' => 'radio'
            ],
            [
                'name' => '下拉',
                'value' => 'select'
            ],
            [
                'name' => '数组',
                'value' => 'array'
            ],
            [
                'name' => '富文本',
                'value' => 'editor'
            ]
        ];
    }
}