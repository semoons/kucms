<?php
// 是否严格检查变量是否存在，错误级别
error_reporting(E_ERROR | E_PARSE );
return [

    // 显示错误信息
	'show_error_msg'        =>  true,
	// 错误显示信息,非调试模式有效
	'error_message'          => '页面错误！请稍后再试～',
	// 默认模块名
	'default_module'         => 'manage',
	// 禁止访问模块
	'deny_module_list'       => ['common'],
	// 默认控制器名
	'default_controller'     => 'Index',
	// 默认操作名
	'default_action'         => 'index',
	// URL普通方式参数 用于自动生成 ？ 方式
    'url_common_param'       => true
	
];