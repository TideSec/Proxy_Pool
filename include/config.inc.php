<?php

//设置mysql最大上传文件  set global max_allowed_packet=1024*1024*16;
// 保存一天 
$lifeTime = 1 * 3600; 
session_set_cookie_params($lifeTime); 
session_start();
//error_reporting(0);
error_reporting(E_ALL || ~E_NOTICE);
//error_reporting(E_ALL);
$cfg['soft_name'] = 'Proxy_Pool';
$cfg['soft_version'] = '';
$cfg['soft_lang'] = 'UTF-8';
header("Content-Type: text/html; charset={$cfg['soft_lang']}");
$cfg['db_host'] = 'localhost';       // 数据库服务器
$cfg['db_name'] = 'proxy';       // 数据库名
$cfg['db_user'] = 'root';       // 数据库用户名
$cfg['db_pass'] = '123456';       // 数据库密码
$cfg['db_charset'] = 'utf-8';      //数据库编码
$cfg['db_pre'] = '';      //表前缀
$cfg['file_mod'] = 0777;
$cfg['authkey'] = '6295259';

//配置结束

define('soft_name', $cfg['soft_name']);	
define('LDINC', str_replace("\\", '/', dirname(__FILE__) ) );
define('LDROOT', str_replace("\\", '/', substr(LDINC,0,-8) ) );
define('LDFMOD', $cfg['file_mod'] ? $cfg['file_mod'] : '');			//文件写入模式
date_default_timezone_set("Asia/Shanghai"); 							//设置默认时区
require_once("sqlsafe.php");
$sql = new sqlsafe();		//防SQL注入

require_once(LDINC."/common.fun.php");									//引用全局函数
require_once("mysql.class.php");

$db = new Mysql($cfg['db_host'],$cfg['db_user'],$cfg['db_pass'],$cfg['db_name'],$cfg['db_charset'],$cfg['db_charset'],$cfg['db_pre']);
$sitename = $cfg['sitename'];
// require_once("index.action.php");
// require_once("report.action.php");

?>