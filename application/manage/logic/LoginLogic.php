<?php
namespace app\manage\logic;

use cms\Login;
use think\Url;
use think\Config;
use newday\common\Format;
use core\manage\logic\MemberLogic;

class LoginLogic
{

    /**
     * 登录存储Key
     *
     * @var unknown
     */
    const LOGIN_KEY = 'manage_user';

    /**
     * 登录操作
     *
     * @param string $user_name            
     * @param string $user_pass            
     * @return array
     */
    public static function doLogin($user_name, $user_pass)
    {
        $res = MemberLogic::instance()->checkLogin($user_name, $user_pass);
        if ($res['code'] != MemberLogic::TYPE_USER_LOGIN) {
            return Format::formatResult(0, $res['msg']);
        }
        $user = $res['data']['user'];
        $group = $res['data']['group'];
        
        $login_driver = self::getLoginDriver();
        $data = [
            'user_id' => $user['id'],
            'manage_url' => Url::build($group['home_page'])
        ];
        $login_driver->storageLogin(self::LOGIN_KEY, $data);
        
        return Format::formatResult(1, '登录成功', $data['manage_url']);
    }

    /**
     * 注销登录
     */
    public static function loginOut()
    {
        $login_driver = self::getLoginDriver();
        $login_driver->clearLogin(self::LOGIN_KEY);
    }

    /**
     * 登录用户
     *
     * @return array
     */
    public static function getLoginUser()
    {
        $login_driver = self::getLoginDriver();
        return $login_driver->readLogin(self::LOGIN_KEY);
    }

    /**
     * 登录用户信息
     *
     * @return array
     */
    public static function gteLoginUserInfo()
    {
        $user = self::getLoginUser();
        if (empty($user)) {
            return null;
        }
        
        return MemberLogic::instance()->getUser($user['user_id']);
    }

    /**
     * 登录存储驱动
     *
     * @return \cms\Login
     */
    public static function getLoginDriver()
    {
        return Login::create(Config::get('manage_driver_login'));
    }
}