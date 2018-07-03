<?php
namespace core\manage\logic;

use cms\Logic;
use cms\Upload;
use think\Config;
use newday\common\Format;
use newday\file\FileInfo;

class FileLogic extends Logic
{

    /**
     * 获取文件
     *
     * @param string $hash            
     * @return string
     */
    public function getFile($hash)
    {
        $map = array(
            'file_hash' => $hash
        );
        $file = $this->model->field('file_url')
            ->where($map)
            ->find();
        return $file ? $file['file_url'] : null;
    }

    /**
     * 保存文件
     *
     * @param unknown $hash            
     * @param unknown $url            
     * @param string $type            
     * @param number $size            
     * @return number
     */
    public function addFile($hash, $url, $type = '', $size = 0)
    {
        // 后缀
        $arr = explode('.', $url);
        $ext = end($arr);
        
        // 类型
        $type || $type = $this->getUploadDriver()->getFileType($ext);
        
        // 大小
        $size || $size = FileInfo::getFileSize($url);
        
        $data = array(
            'file_hash' => $hash,
            'file_type' => $type,
            'file_ext' => $ext,
            'file_size' => $size,
            'file_url' => $url,
            'create_time' => time()
        );
        return $this->model->insert($data);
    }

    /**
     * 删除文件
     *
     * @param number $file_id            
     * @return array
     */
    public function delFile($file_id)
    {
        $file = $this->model->get($file_id);
        if (empty($file)) {
            return Format::formatResult(0, '文件不存在');
        }
        
        // 删除记录
        $this->model->del($file_id);
        
        // 删除文件
        $upload_driver = $this->getUploadDriver();
        return $upload_driver->deleteFile($file['file_url']);
    }

    /**
     * 上传类型选择
     *
     * @return array
     */
    public function getUploadTypeSelect()
    {
        $upload_type = $this->getUploadDriver()->getUploadType();
        $upload_type_select = [];
        foreach ($upload_type as $co => $vo) {
            $upload_type_select[] = [
                'name' => $co,
                'value' => $co
            ];
        }
        return $upload_type_select;
    }

    /**
     * 上传驱动
     *
     * @return \app\common\driver\Upload
     */
    public function getUploadDriver()
    {
        if (empty($this->upload_driver)) {
            $upload_driver = Config::get('upload_driver');
            if ($upload_driver == Upload::TYPE_LOCAL) {
                $option = Config::get('upload_local');
            } else {
                $option = Config::get('upload_upyun');
            }
            $this->upload_driver = Upload::create($option, $upload_driver);
            
            // 检查文件
            $this->upload_driver->onCheck = function ($data) {
                return FileLogic::instance()->getFile($data['hash']);
            };
            
            // 上传成功
            $this->upload_driver->onSuccess = function ($data) {
                FileLogic::instance()->addFile($data['hash'], $data['url'], $data['type'], FileInfo::getFileSize($data['path']));
            };
        }
        return $this->upload_driver;
    }
}