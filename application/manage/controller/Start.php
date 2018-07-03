<?php
namespace app\manage\controller;

use think\Url;
use cms\Captcha;
use think\Request;
use app\manage\logic\LoginLogic;

class Start extends Base
{

    /**
     * 后台登录
     *
     * @var unknown
     */
    const LOGIN_KEY = 'manage_login';

    /**
     * 登录页面
     */
    public function login()
    {
        $this->site_title = '欢迎使用';
        
        $login_url = Url::build('start/doLogin');
        $this->assign('login_url', $login_url);
        
        $code_url = Captcha::getCodeSrc(self::LOGIN_KEY);
        $this->assign('code_url', $code_url);
        
        return $this->fetch();
    }

    /**
     * 登录验证
     *
     * @param Request $request            
     */
    public function doLogin(Request $request)
    {
        $verify_code = $request->param('verify_code');
        if (! Captcha::checkCode($verify_code, self::LOGIN_KEY)) {
            return $this->error('验证码错误');
        }
        
        $user_name = $request->param('user_name');
        $user_pass = $request->param('user_passwd');
        $res = LoginLogic::doLogin($user_name, $user_pass);
        return $this->jump($res['code'], $res['msg'], $res['data']);
    }

    /**
     * 退出登录
     *
     * @return mixed
     */
    public function logout()
    {
        LoginLogic::loginOut();
        
        return $this->success('退出登录成功', Url::build('start/login'));
    }
}