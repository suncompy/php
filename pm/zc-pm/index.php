<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: oncoo.net <www.oncoo.net>
// +----------------------------------------------------------------------

// 应用入口文件

// 检测PHP环境
// ini_set('session.cookie_domain', "money.hddzsp.com");//跨域访问Session
// ini_set('session.cookie_domain', ".fjsxpmh.com/");//跨域访问Session
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');
ob_start();
ini_set('date.timezone', 'Asia/Shanghai');
// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG',true);

// 定义应用目录
define('APP_PATH','./Application/');
define("DatabaseBackDir", APP_PATH . "Database/"); //系统备份数据库文件存放目录
define('WEB_CACHE_PATH', APP_PATH."Runtime/");
if (!file_exists(APP_PATH.'Common/Conf/systemConfig.php')) {
    header("Location: Application/install/");
    exit;
}
require 'vendor/autoload.php';
// 引入ThinkPHP入口文件
require './ThinkPHP/ThinkPHP.php';
// 亲^_^ 后面不需要任何代码了 就是如此简单