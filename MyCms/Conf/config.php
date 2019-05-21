<?php
$api = include CONF_PATH . '/api.php';
//载入接口配置信息
$site = include CONF_PATH . '/site.php';
//载入站点配置信息
$attach = include CONF_PATH . '/attach.php';
//载入附件配置信息
$email = include CONF_PATH . '/email.php';
//载入邮箱配置信息
$content=  include CONF_PATH.'/content.php';
//载入页面配置信息
$weixin=  include CONF_PATH.'/weixin.php';
//载入微信配置信息
$array = array(
    //调试信息
//    'SHOW_PAGE_TRACE' => true, // 显示页面Trace信息
    //分组相关
    'APP_GROUP_LIST' => 'Home,Admin', //项目分组设定
    'DEFAULT_GROUP' => 'Home', //默认分组
    'APP_GROUP_MODE' => 1, //独立分组
    //数据库配置信息
    'DB_TYPE' => 'mysql', // 数据库类型
    //'DB_HOST' => '10.102.12.63', // 服务器地址
	'DB_HOST' => '10.105.15.2', // 服务器地址
    'DB_NAME' => 'new', // 数据库名
    'DB_USER' => 'root', // 用户名
    'DB_PWD' => 'root', // 密码
    'DB_PORT' => 3306, // 端口
    'DB_PREFIX' => 'ad_', // 数据库表前缀 
	 'DB_DEBUG' =>  true, 
	//oracle数据库连接
	'DB_ORACLE' => 'oracle://tjstc:tjstc@192.168.8.251:1521/hx',
	//结算接口连接
	'CALL_URL' => 'http://10.105.15.2/TjstcWebImpl',
    //模板相关cl
    'TMPL_FILE_DEPR' => '-', //模板文件MODULE_NAME与ACTION_NAME之间的分割符
    'TMPL_VAR_IDENTIFY' => 'array', //模板中点的解析
    //每页条数
    'PAGE_SIZE' => 10,
    'URL_MODEL' => 3, // 如果你的环境不支持PATHINFO 请设置为3
    //默认错误跳转对应的模板文件
    'TMPL_ACTION_ERROR' =>  './' . APP_NAME . '/Tpl/error.html',
    //默认成功跳转对应的模板文件
    'TMPL_ACTION_SUCCESS' =>  './' . APP_NAME . '/Tpl/error.html',
	'URL_CASE_INSENSITIVE' => true,
	/* 页面设置 */
	'HTML_FILE_SUFFIX' => '.html',// 默认静态文件后缀
    /* log */
    'LOG_RECORD' => true, // 日志记录
    'LOG_LEVEL' => 'EMERG,ALERT,CRIT,ERR', // 只记录EMERG ALERT CRIT ERR 错误
);

$arr = array_merge($api,$site, $attach, $email,$content,$weixin, $array);
return $arr;
?>