<?php
namespace app\manage\controller;

use think\Db;
use think\Url;
use cms\Common;
use think\Request;
use core\manage\logic\MemberLogic;
use core\manage\logic\MenuLogic;
use core\manage\logic\FileLogic;
use core\manage\logic\ConfigLogic;
use app\manage\logic\RuntimeLogic;
use app\manage\logic\LoginLogic;

class Index extends Base
{

    /**
     * 首页
     *
     * @param Request $request            
     * @return string
     */
    public function index(Request $request)
    {
        $this->site_title = '后台首页';
        
        // 基础统计
        $site_info = [
            'member_num' => MemberLogic::model()->count(),
            'menu_num' => MenuLogic::model()->count(),
            'file_num' => FileLogic::model()->count(),
            'config_num' => ConfigLogic::model()->count()
        ];
        $this->assign('site_info', $site_info);
        
        // 系统信息
        $mysql_version = Db::query('select version() as version');
        $server_info = array(
            '系统版本' => THINK_VERSION,
            '系统信息' => ' <a class="am-text-success" target="new" href="https://www.waakuu.com">哇酷科技</a> ,  <a class="am-text-success" target="new" href="http://www.waakuu.com">KuCms</a>',
            '操作系统' => PHP_OS,
            '主机名信息' => $request->server('SERVER_NAME') . ' (' . $request->server('SERVER_ADDR') . ':' . $request->server('SERVER_PORT') . ')',
            '运行环境' => $request->server('SERVER_SOFTWARE'),
            'PHP运行方式' => php_sapi_name(),
            '程序目录' => WEB_PATH,
            'MYSQL版本' => 'MYSQL ' . $mysql_version[0]['version'],
            '上传限制' => ini_get('upload_max_filesize'),
            'POST限制' => ini_get('post_max_size'),
            '最大内存' => ini_get('memory_limit'),
            '执行时间限制' => ini_get('max_execution_time') . "秒",
            '内存使用' => Common::formatBytes(@memory_get_usage()),
            '磁盘使用' => Common::formatBytes(@disk_free_space(".")) . '/' . Common::formatBytes(@disk_total_space(".")),
            'display_errors' => ini_get("display_errors") == "1" ? '√' : '×',
            'register_globals' => get_cfg_var("register_globals") == "1" ? '√' : '×',
            'magic_quotes_gpc' => (1 === get_magic_quotes_gpc()) ? '√' : '×',
            'magic_quotes_runtime' => (1 === get_magic_quotes_runtime()) ? '√' : '×'
        );
        $this->assign('server_info', $server_info);
        
        // 扩展列表
        $extensions_list = get_loaded_extensions();
        $this->assign('extensions_list', implode(' , ', $extensions_list));
        
        return $this->fetch();
    }

    /**
     * 账号设置
     *
     * @param Request $request            
     * @return string
     */
    public function account(Request $request)
    {
        if ($request->isPost()) {
            $user_nick = $request->param('user_nick');
            if (empty($user_nick)) {
                return $this->error('昵称为空');
            }
            
            $user_passwd = $request->param('user_passwd');
            $re_passwd = $request->param('re_passwd');
            if (! empty($user_passwd)) {
                
                // 重复密码
                if ($user_passwd != $re_passwd) {
                    return $this->error('两次密码不一致');
                }
                
                // 验证原密码
                $old_passwd = $request->param('old_passwd');
                if (empty($old_passwd)) {
                    return $this->error('原密码为空');
                } else {
                    $user = LoginLogic::gteLoginUserInfo();
                    
                    $result = LoginLogic::doLogin($user['user_name'], $old_passwd);
                    if ($result['code'] != 1) {
                        return $this->error('原密码错误');
                    }
                }
            }
            
            // 保存账号
            $data = [
                'user_nick' => $user_nick
            ];
            $user_passwd && $data['user_passwd'] = $user_passwd;
            MemberLogic::instance()->saveMember($data, $this->user_id);
            
            return $this->success('修改资料成功', Url::build('index/account'));
        } else {
            $this->site_title = '账号设置';
            return $this->fetch();
        }
    }

    /**
     * 缓存管理
     *
     * @param Request $request            
     * @return string
     */
    public function runtime(Request $request)
    {
        $this->site_title = '缓存管理';
        
        // 路径
        $path = $request->param('path');
        $path = $path ? base64_decode($path) : '/';
        $this->assign('path', $path);
        
        // 上一级
        $this->assign('path_base', RuntimeLogic::dirName($path));
        
        // 文件列表
        $list = RuntimeLogic::getRuntime($path);
        $this->assign('list', $list);
        
        return $this->fetch();
    }

    /**
     * 删除缓存
     *
     * @param Request $request            
     * @return mixed
     */
    public function delRuntime(Request $request)
    {
        // 路径
        $path = $request->param('path');
        $path = $path ? base64_decode($path) : '/';
        
        // 是否删除自己
        $self = $request->param('self', 0);
        
        // 跳转链接
        $url = Url::build('runtime', [
            'path' => base64_encode($self ? RuntimeLogic::dirName($path) : $path)
        ]);
        
        // 删除缓存
        $res = RuntimeLogic::delRuntime($path, $self);
        if ($res) {
            return $this->success('删除文件成功', $url);
        } else {
            return $this->error('删除文件失败');
        }
    }
}