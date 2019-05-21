<?php

return array(
    'TMPL_PARSE_STRING' => array(
        '__PUBLIC__' => __ROOT__ . '/' . APP_NAME . '/Modules/' . GROUP_NAME . '/Tpl'
    ), //修改默认PUBLIC解析

    'SESSION_AUTO_START' => true,
    'USER_AUTH_ON' => true,
    'USER_AUTH_TYPE' => 2, // 默认认证类型 1 登录认证 2 实时认证
    'USER_AUTH_KEY' => 'myid', // 用户认证SESSION标记
    'ADMIN_AUTH_KEY' => 'myadmin',
    'USER_AUTH_MODEL' => 'Admin', // 默认验证数据表模型
    'AUTH_PWD_ENCODER' => 'md5', // 用户认证密码加密方式
    'USER_AUTH_GATEWAY' => GROUP_NAME . '/Public/login', // 默认认证网关
    'NOT_AUTH_MODULE' => 'Public', // 默认无需认证模块
    'RBAC_ROLE_TABLE' => 'my_role',
    'RBAC_USER_TABLE' => 'my_role_admin',
    'RBAC_ACCESS_TABLE' => 'my_access',
    'RBAC_NODE_TABLE' => 'my_node',
    /**/
    'VAR_SESSION_ID' => 'session_id',
    'SHOW_PAGE_TRACE' => false, // 显示页面Trace信息
    'LOG_RECORD' => true,
    'URL_MODEL' => 3,
    'URL_HTML_SUFFIX' => ''
);
?>
