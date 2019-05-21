<?php

return array(
    'TMPL_EXCEPTION_FILE' => './' . APP_NAME . '/Tpl/500.html',
    'SESSION_OPTIONS'         =>  array(
        'name'                =>  'HOME_SESSION',
        'expire'              =>  1800,
        'use_trans_sid'       =>  1,
        'use_only_cookies'    =>  0
    ),
    'FTPARRAY'         =>  array(
        'host'                =>  '192.168.8.21',
        'port'              => '21',
        'username'       =>  'stcjs',
        'pwd'    =>  'stcjs'
    ),
    'FTPSWARRAY'         =>  array(
        'host'                =>  '192.168.8.22',
        'port'              => '21',
        'username'       =>  'stczss',
        'pwd'    =>  'stczss'
    )
);

?>
