<?php

//前台公共函数库
//
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
    $cate = D('Cate')->order('sort Asc')->select();
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

//
//查找栏目下的各个模型列表
/*
 * get_company_list('1|Article|5');
 *
 * 参数  |  分割
 * 第一个参数  栏目id
 * 第二个参数  模型名称
 * 第三个参数  条数
 * 第四个参数  区分
 *
 */
function get_company_list($canshu) {
    $can = explode('|', $canshu);
    $cat_pid = $can[0];
    $model = $can[1];
    $limit = $can[2];
    $diff = $can[3];
	$ishot = $can[4];
    if ($ishot) {
        $map['ishot'] = array('eq', $ishot);
    }
    $cate = D('Cate')->order('sort Asc')->select();
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
	$map['diff'] = $diff;

    $list = D($model)->where($map)->order('id desc')->limit($limit)->select();
//    echo D($model)->getLastSql();
    return $list;
}

/*
 * get_list('10|buiness|5');
 * 栏目ID cat_pid
 * 所在模型 model
 * 所得条数 limit
 */

function get_yewu($canshu) {

    $can = explode('|', $canshu);
    $cat_pid = $can[0];
    $model = $can[1];
    $limit = $can[2];
    $cate = D('Cate')->order('sort Asc')->select();

    //栏目查询
    if (is_numeric($cat_pid)) {
        $c = get_childsid($cate, $cat_pid);
        $cids = $cat_pid;
        foreach ($c as $v) {
            $cids.="," . $v;
        }
        $map['cat_id'] = array('in', $cids);
    }
	$userid = $_SESSION['uid'];
	$map['userid'] = array('eq',$userid );
    $list = D($model)->where($map)->order('id desc')->limit($limit)->select();

    return $list;
}



/*
 *
 * 顶部导航
 *
 */

//传入一个父级id，返回子类信息（只查找一级）
function get_menu($cate, $pid) {
    $arr = array();
    foreach ($cate as $v) {
        if ($v['pid'] == $pid) {
            if ($v['ismenu'] == 1) {
                $arr[] = $v;
            }
        }
    }
    return $arr;
}

/*
 *
 * 房间绑定申请记录(房间地址、房间编号)
 *
 */

//传入一个房间编号，返回房间信息
function get_infor($applist, $housecode) {
    $arr = array();
    foreach ($applist as $v) {
		/*$code = explode(",",$v['housename']);
        if ($code[1] == $housecode) {
			$id = D('User_message')->where('mid='.$v['id'])->find();
			$name = D('User')->where('id='.$id['fromid'])->find();
            $arr[] = $name;
        }*/
		$name = D('User')->where('id='.$v['uid'])->find();
		$arr[] = $name;
    }

    return $arr;
}



/*
 *{~$ban = get_tag('2|id|DESC')}
 *根据用户订阅标签获得所订阅文章
 *limit 显示数目
 *orderkey 排序字段
 *orderval 排序方式
*/

function get_tag($canshu){

	$userid = $_SESSION['uid'];
	$can = explode('|', $canshu);

	$limit = $can[0];
	$orderkey = $can[1];
	$orderval = $can[2];

	$info = D('User')->where(" id = ".$userid)->find();

	$tag = trim($info['tag']);


	if(!empty($tag)){
		$tag= substr($tag, 0, -1);
		$tagarr = explode(',', $tag);
		//取出结果
		foreach($tagarr as $key => $val){
			$tagsql = "tag like '%".$tagarr[$key]."%'";
			$list[] = D('article')->where($tagsql)->select();
		}

		//合并数组
		foreach( $list as $k => $v){

			foreach($list[$k] as $ke => $va){
						if(!in_array($list[$k][$ke],$retlist)){
				$retlist[] = $list[$k][$ke];
						}
			}

		}

		//排序
		$retlist = array_sort($retlist,$orderkey,$orderval);

		//取出指定条数
		$list = array_slice($retlist,0,$limit);
	}
	return $list;
}
//根据标签 查找 文章列表
/*
 * {~$tag = get_by_tag('Article|tag|5');}
 *
 * 参数  |  分割
 * 第1个参数  模型名称
 * 第2个参数  所用标签tag
 * 第3个参数  条数
 *
 */
function get_by_tag($canshu){

	$can = explode('|', $canshu);
	$model = $can[0];
	$tag = $can[1];
	$limit = $can[2];

	//有效期
    $map['start_time'] = array('elt', time()); //发布时间小于现在时间
    $map['_string'] = 'end_time = 0 OR end_time > ' . time(); //结束时间大于现在时间或者等于0
	//状态
    $map['status'] = 1;

	$map['tag'] = array('like', '%' . $tag . '%');
    $list = D($model)->where($map)->order('id desc')->limit($limit)->select();
	return $list;

}

// 数组排序
function array_sort($arr,$keys,$type='asc'){
	$keysvalue = $new_array = array();
	foreach ($arr as $k=>$v){
		$keysvalue[$k] = $v[$keys];
	}
	if($type == 'asc' || $type == 'ASC'){
		asort($keysvalue);
	}else{
		arsort($keysvalue);
	}
	reset($keysvalue);
	foreach ($keysvalue as $k=>$v){
		$new_array[$k] = $arr[$k];
	}
	return $new_array;
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

function sendPhoneVerifyCode1($phone, $code) {
	try {
		$wsdl = C('api_verify_code_url');
		if (check_url_status($wsdl) == false) {
			$data['error'] = 1;
			return($data);
		}

		$client = new SoapClient($wsdl);
		//传入的参数
		$a = array(
				//手机号码
				'mobiles' => $phone,
				//认证密码
				'verifyCode' => $code,
		);
		$ret = $client->sendMessage($a);
	} catch (SoapFault $e) {
		$data['error'] = 1;
		return($data);
	}
	$data['error'] = $ret->return;
	return($data);
}

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
?>
