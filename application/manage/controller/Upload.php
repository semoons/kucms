<?php
namespace app\manage\controller;

use think\Request;
use newday\common\Format;
use core\manage\logic\FileLogic;

class Upload extends Base
{

    /**
     * 上传文件
     *
     * @param Request $request            
     * @throws \think\exception\HttpResponseException
     */
    public function upload(Request $request)
    {
        $type = $request->param('upload_type', '');
        
        // 文件
        $upload_file = isset($_FILES['upload_file']) ? $_FILES['upload_file'] : null;
        if (empty($upload_file)) {
            responseReturn(Format::formatResult(0, '上传文件不存在'));
        }
        
        // 额外配置
        $upload_option = $request->param('upload_option', '');
        if (! empty($upload_option)) {
            $upload_option = json_decode($upload_option, true);
        } else {
            $upload_option = [];
        }
        
        // 上传文件
        $upload_driver = FileLogic::instance()->getUploadDriver();
        $res = $upload_driver->upload($upload_file, $type, $upload_option);
        responseReturn($res);
    }

    /**
     * wangEditor
     *
     * @param Request $request            
     * @return string
     */
    public function wang(Request $request)
    {
        // 文件
        $upload_file = isset($_FILES['upload_file']) ? $_FILES['upload_file'] : null;
        if (empty($upload_file)) {
            return 'error|上传文件不存在';
        }
        
        // 额外配置
        $upload_option = [
            'width' => 1920,
            'height' => 1080
        ];
        
        // 上传文件
        $upload_driver = FileLogic::instance()->getUploadDriver();
        $res = $upload_driver->upload($upload_file, '', $upload_option);
        if ($res['code'] == 1) {
            return $res['data']['url'];
        } else {
            return 'error|' . $res['msg'];
        }
    }
}