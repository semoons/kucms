<?php
namespace app\manage\logic;

use cms\Auth;
use core\manage\logic\MenuLogic;
use core\manage\logic\MemberLogic;

class AuthLogic extends Auth
{

    /**
     * 是否授权操作
     *
     * @param number $user_id            
     * @return boolean
     */
    public static function isAuthAction($user_id)
    {
        $user_logic = MemberLogic::instance();
        
        // 超级管理员
        if ($user_logic->isAdmin($user_id)) {
            return true;
        }
        
        // 菜单不存在
        $current_menu = MenuLogic::instance()->getMenuByFlag();
        if (empty($current_menu)) {
            return false;
        }
        
        // 授权菜单
        $auth_menu = $user_logic->getUserMenu($user_id);
        return in_array($current_menu['id'], $auth_menu);
    }
}