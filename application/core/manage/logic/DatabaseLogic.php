<?php
namespace core\manage\logic;

use think\Db;
use think\Config;
use cms\Logic;
use cms\Common;
use MySQLDump;
use newday\common\Format;

class DatabaseLogic extends Logic
{

    /**
     * 备份记录
     *
     * @return array
     */
    public function getList()
    {
        $list = $this->model->order('id desc')->select();
        $user_select = MemberLogic::instance()->getUserSelect();
        foreach ($list as &$vo) {
            $vo['dump_size'] = Common::formatBytes($vo['dump_size']);
            $vo['user'] = isset($user_select[$vo['dump_uid']]) ? $user_select[$vo['dump_uid']] : [
                'name' => 'unknown'
            ];
        }
        return $list;
    }

    /**
     * 数据表
     *
     * @return array
     */
    public function getTableList()
    {
        $list = array_map('array_change_key_case', Db::query('SHOW TABLE STATUS'));
        foreach ($list as &$vo) {
            $vo['data_format'] = Common::formatBytes($vo['data_length']);
        }
        return $list;
    }

    /**
     * 备份
     *
     * @param number $user_id            
     * @return array
     */
    public function addBakup($user_id)
    {
        try {
            
            $connection = $this->getConnection();
            $dump = new MySQLDump($connection);
            
            // 备份数据库
            $bakup_path = $this->getBakupPath();
            $file_path = '/dump_' . date('Ymd_His') . '.sql';
            $dump->save($bakup_path . $file_path);
            
            // 备份记录
            $data = [
                'dump_uid' => $user_id,
                'dump_size' => filesize($bakup_path . $file_path),
                'dump_file' => $file_path,
                'dump_time' => time()
            ];
            $this->model->add($data);
            
            return Format::formatResult(1, '备份数据库成功');
        } catch (\Exception $e) {
            return Format::formatResult(0, '备份数据库失败:' . $e->getMessage());
        }
    }

    /**
     * 删除备份
     *
     * @param number $bakup_id            
     * @return number
     */
    public function delBakup($bakup_id)
    {
        try {
            $bakup_path = $this->getBakupPath();
            $bakup = $this->model->get($bakup_id);
            unlink($bakup_path . $bakup['dump_file']);
        } catch (\Exception $e) {}
        
        return $this->model->del($bakup_id);
    }

    /**
     * 优化表
     */
    public function optimize()
    {
        $list = array_map('array_change_key_case', Db::query('SHOW TABLE STATUS'));
        foreach ($list as $vo) {
            Db::query('OPTIMIZE TABLE `' . $vo['name'] . '`');
        }
    }

    /**
     * 修复表
     */
    public function repair()
    {
        $list = array_map('array_change_key_case', Db::query('SHOW TABLE STATUS'));
        foreach ($list as $vo) {
            Db::query('REPAIR TABLE `' . $vo['name'] . '`');
        }
    }

    /**
     * 数据库连接
     *
     * @return \mysqli
     */
    public function getConnection()
    {
        $database = Config::get('database');
        return new \mysqli($database['hostname'], $database['username'], $database['password'], $database['database'], $database['hostport']);
    }

    /**
     * 备份路径
     *
     * @return atring
     */
    public function getBakupPath()
    {
        return Config::get('bakup_path');
    }
}