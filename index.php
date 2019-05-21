<?php
//项目入口文件
define('APP_NAME','MyCms');
define('APP_PATH','./MyCms/');
//define('APP_DEBUG',false);//调试
/*本地运行开启debug*/
define('APP_DEBUG',true);//调试
define('RUNTIME_PATH',"./Cache/");//缓存路径
define('UPLOAD_PATH','./Uploads/');//上传附件目录
define('LOG_PATH', './Logs/');//日志目录
define('HTML_PATH', './');//静态页路径

require './ThinkPHP/ThinkPHP.php';
