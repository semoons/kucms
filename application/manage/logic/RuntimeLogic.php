<?php
namespace app\manage\logic;

use think\Url;
use cms\Common;
use newday\file\FileManager;

class RuntimeLogic
{

    /**
     * 上级目录
     *
     * @param string $path            
     * @return string
     */
    public static function dirName($path = '/')
    {
        // 真实路径
        $path_runtime = realpath(RUNTIME_PATH);
        $path_list = dirname(realpath(RUNTIME_PATH . $path));
        
        // 上级路径
        if (strpos($path_list, $path_runtime) !== false) {
            return self::processDirName($path_list, $path_runtime);
        } else {
            return '/';
        }
    }

    /**
     * 缓存文件
     *
     * @param string $path            
     * @return array
     */
    public static function getRuntime($path = '/')
    {
        // 真实路径
        $path_runtime = realpath(RUNTIME_PATH);
        $path_list = realpath(RUNTIME_PATH . $path);
        
        // 判断越界
        if (strpos($path_list, $path_runtime) === false) {
            return [];
        }
        
        // 列出文件
        $list = FileManager::listDir($path_list);
        foreach ($list as &$vo) {
            $vo['file_str'] = self::fileDir($vo['file'], $path_runtime);
            $vo['file'] = str_replace([
                $path_runtime,
                '\\'
            ], [
                '',
                '/'
            ], realpath($vo['file']));
            $vo['size'] = Common::formatBytes($vo['size']);
        }
        
        return $list;
    }

    /**
     * 删除缓存
     *
     * @param string $path            
     * @return boolean
     */
    public static function delRuntime($path = '/', $self = false)
    {
        // 真实路径
        $path_runtime = realpath(RUNTIME_PATH);
        $path_list = realpath(RUNTIME_PATH . $path);
        
        // 判断越界
        if (strpos($path_list, $path_runtime) === false) {
            return false;
        }
        
        // 删除文件
        try {
            FileManager::delFile($path_list, $self);
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 文件目录
     *
     * @param string $file            
     * @return array
     */
    public static function fileDir($file, $base)
    {
        // 真实路径
        $file = realpath($file);
        $base = realpath($base);
        
        $file_str = '';
        while (1) {
            if ($file == $base) {
                $file_str = '/<a href="' . Url::build('index/runtime') . '">runtime</a>' . $file_str;
                break;
            }
            
            $file_parent = dirname($file);
            $file_current = iconv('gbk', 'utf-8', str_replace($file_parent, '', $file));
            if (is_dir($file)) {
                $file_path = self::processDirName($file, $base);
                $url = Url::build('index/runtime', [
                    'path' => base64_encode($file_path)
                ]);
                $file_str = '/<a href="' . $url . '">' . str_replace(DS, '', $file_current) . '</a>' . $file_str;
            } else {
                $file_str = $file_current . $file_str;
            }
            
            $file = $file_parent;
        }
        
        return str_replace('\\', '/', $file_str);
    }

    /**
     * 处理文件名称
     *
     * @param string $file            
     * @param string $base            
     * @return mixed
     */
    public static function processDirName($file, $base = null)
    {
        $base || $base = realpath(RUNTIME_PATH);
        return str_replace([
            $base,
            '\\'
        ], [
            '',
            '/'
        ], $file);
    }
}