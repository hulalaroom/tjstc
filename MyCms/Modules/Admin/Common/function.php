<?php

//后台公共函数库

//查找栏目下的各个模型列表
/*
 * get_list('1|Article|5');
 *
 * 参数  |  分割
 * 第一个参数  栏目id
 * 第二个参数  模型名称
 * 第三个参数  条数
 *
 */
function get_list($canshu) {
    $can = explode('|', $canshu);
    $cat_pid = $can[0];
    $model = $can[1];
    $limit = $can[2];
    $ishot = $can[3];
    if ($ishot) {
        $map['ishot'] = array('eq', $ishot);
    }
    $cate = D('Cate')->order('sort Asc')->relation(true)->select();
//栏目查询
    if (is_numeric($cat_pid)) {
        $c = get_childsid($cate, $cat_pid);
        $cids = $cat_pid;
        foreach ($c as $v) {
            $cids.="," . $v;
        }
        $map['cat_id'] = array('in', $cids);
    }
//有效期
    $map['start_time'] = array('elt', time()); //发布时间小于现在时间
    $map['_string'] = 'end_time = 0 OR end_time > ' . time(); //结束时间大于现在时间或者等于0
//状态
    $map['status'] = 1;
	$map['isre'] = 1;
    $list = D($model)->where($map)->order('id desc')->limit($limit)->select();
//    echo D($model)->getLastSql();
    return $list;
}


/*
 * 获取后台菜单
 */
function get_menu($menu, $name = 'items', $pid = 0) {
    $arr = array();
    foreach ($menu as $val) {
        if ($val['pid'] == $pid) {

            $arr[$val['id'] . "_menu"]['menuid'] = $val['id'];
            $arr[$val['id'] . "_menu"]['id'] = $val['id'] . "_menu";
            $arr[$val['id'] . "_menu"]['name'] = $val['title'];
            $arr[$val['id'] . "_menu"]['url'] = U($val['module'] . "/" . $val['action']);
            $items = get_menu($menu, $name, $val['id']);
            if(!empty($items)){
                $arr[$val['id'] . "_menu"][$name]=$items;
            }
            
        }
    }
    return $arr;
}

/*
 * 
 */
/* 查找是否有子 */

function findchild($id) {
    $res = D('Cate')->where( 'pid=' . $id)->select();
    if (!empty($res)) {
        return $res[0]['id'];
    }  else {
        return $id;
    }
}

//检测url是否能打开
function check_url_status($url) {
    $context = stream_context_create( array(
        'http' => array('timeout' => 3)
    ));
    $check = @fopen($url, "r", false, $context);
    if ($check){
        $status = true;
	}
    else{
        $status = false;
	}
    return $status;
}
//发送手机验证码
function sendPhoneVerifyCode($phone, $code) {
    $wsdl = C('api_verify_code_url');
    if (check_url_status($wsdl) == false) {
        $data['error'] = 1;
        return($data);
    }

    $data = array(
            //手机号码
            'telNums' => $phone,
            //认证密码
            'content' => $code,
            'active' => 'login'
    );

    $data = http_build_query($data);
    $opts = array (
        'http' => array (
            'method' => 'POST',
            'header'=> "Content-type: application/x-www-form-urlencoded\r\n" .
            "Content-Length: " . strlen($data) . "\r\n",
            'content' => $data
        )
    );

    try {
        $context = stream_context_create($opts);
        file_get_contents($wsdl, false, $context);
        $data['error'] = 0;
    } catch(Exception $e) {
        $data['error'] = 1;
    }
    return($data);
}

//发送审核通知
function sendNotice($phone, $code) {
    $wsdl = C('api_verify_code_url');
    if (check_url_status($wsdl) == false) {
        $data['error'] = 1;
        return($data);
    }
	$con = iconv('UTF-8', 'GB2312', $code);
    $data = array(
            //手机号码
            'telNums' => $phone,
            //认证密码
            'content' => '',
            'active' => 'websecond'
    );

    $data = http_build_query($data);
    $opts = array (
        'http' => array (
            'method' => 'POST',
            'header'=> "Content-type: application/x-www-form-urlencoded\r\n" .
            "Content-Length: " . strlen($data) . "\r\n",
            'content' => $data
        )
    );

    try {
        $context = stream_context_create($opts);
        file_get_contents($wsdl, false, $context);
        $data['error'] = 0;
    } catch(Exception $e) {
        $data['error'] = 1;
    }
    return($data);
}
?>
