<?php
namespace core\manage\validate;

use cms\Validate;

class MemberValidate extends Validate
{

    /**
     * 规则
     *
     * @var unknown
     */
    protected $rule = [
        'user_name' => 'require',
        'user_nick' => 'require',
        'user_passwd' => 'require',
        're_passwd' => 'require|confirm:user_passwd',
        'group_id' => 'require'
    ];

    /**
     * 提示
     *
     * @var unknown
     */
    protected $message = [
        'user_name.require' => '用户名为空',
        'user_nick.require' => '昵称为空',
        'user_passwd.require' => '密码为空',
        're_passwd.require' => '重复密码为空',
        're_passwd.confirm' => '两次密码不同',
        'group_id.require' => '分组ID不能为空'
    ];

    /**
     * 场景
     *
     * @var unknown
     */
    protected $scene = [
        'login' => [
            'user_name',
            'user_passwd'
        ],
        'add' => [
            'user_name',
            'user_passwd',
            're_passwd',
            'group_id'
        ],
        'edit_info' => [
            'user_name',
            'user_nick',
            'group_id'
        ],
        'edit_passwd' => [
            'user_name',
            'user_nick',
            'user_passwd',
            're_passwd',
            'group_id'
        ]
    ];
}