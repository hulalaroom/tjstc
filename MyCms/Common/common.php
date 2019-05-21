<?php

//项目公共函数库

//传入级别id，返回级别信息（只查找一级）
function get_info($cate, $id) {
    $arr = array();
    foreach ($cate as $v) {
        if ($v['id'] == $id) {
            $arr[] = $v;
        }
    }
    return $arr;
}

//查询所有子孙栏目.组合为一维数组
function get_cate($cate, $html = '┣', $pid = 0, $level = 0) {
    $arr = array();
	
    foreach ($cate as $v) {
        if ($v['pid'] == $pid) {
            $v['level'] = $level + 1;
            $v['html'] = str_repeat($html, $level);

            $child = get_cate($cate, $html, $v['id'], $level + 1);
            if (!empty($child)) {
                $v['child'] = 1;
            }
            $arr[] = $v;
            $arr = array_merge($arr, get_cate($cate, $html, $v['id'], $level + 1));
        }
    }
    return $arr;
}

//查询所有子孙栏目，组合为多维数组
function get_catarray($cate, $name = 'child', $pid = 0) {
    $arr = array();
    foreach ($cate as $v) {
        if ($v['pid'] == $pid) {
            $child = get_catarray($cate, $name, $v['id']);
            //如果多维数组不为空则添加
            if (!empty($child)) {
                $v['id'] = $child;
            }
            $arr[] = $v;
        }
    }
    return $arr;
}

//传入分类id，返回所有父级分类所有信息（面包屑）
function get_parents($cate, $id) {
    $arr = array();
    foreach ($cate as $v) {
        if ($v['id'] == $id) {
            $arr[] = $v;
            $arr = array_merge(get_parents($cate, $v['pid']), $arr);
        }
    }
    return $arr;
}

//传入一个父级id，返回所有子类id
function get_childsid($cate, $pid) {
    $arr = array();
    foreach ($cate as $v) {
        if ($v['pid'] == $pid) {
            $arr[] = $v['id'];
            $arr = array_merge($arr, get_childsid($cate, $v['id']));
        }
    }
    return $arr;
}

//传入一个父级id，返回子类信息（只查找一级）
function get_child($cate, $pid) {
    $arr = array();
    foreach ($cate as $v) {
        if ($v['pid'] == $pid) {
            $arr[] = $v;
        }
    }
    return $arr;
}

/**
  +-----------------------------------------------------------------------------------------
 * 删除目录及目录下所有文件或删除指定文件
  +-----------------------------------------------------------------------------------------
 * @param str $path   待删除目录路径
 * @param int $delDir 是否删除目录，1或true删除目录，0或false则只删除文件保留目录（包含子目录）
  +-----------------------------------------------------------------------------------------
 * @return bool 返回删除状态
  +-----------------------------------------------------------------------------------------
 */
function delDirAndFile($path, $delDir = FALSE) {
    $handle = opendir($path);
    if ($handle) {
        while (false !== ( $item = readdir($handle) )) {
            if ($item != "." && $item != "..")
                is_dir("$path/$item") ? delDirAndFile("$path/$item", $delDir) : unlink("$path/$item");
        }
        closedir($handle);
        if ($delDir)
            return rmdir($path);
    }else {
        if (file_exists($path)) {
            return unlink($path);
        } else {
            return FALSE;
        }
    }
}

/* 递归创建目录 */

function mkdirs($dir) {
    if (!is_dir($dir)) {
        if (!mkdirs(dirname($dir))) {
            return false;
        }
        if (!mkdir($dir, 0777)) {
            return false;
        }
    }
    return true;
}

/* 去除html样式、js、css样式的  文字 截取* */

function cutstr_html($string, $sublen) {
    $string = strip_tags($string);
    $string = preg_replace('/\n/is', '', $string);
    $string = preg_replace('/ |　/is', '', $string);
    $string = preg_replace('/&nbsp;/is', '', $string);

    preg_match_all("/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/", $string, $t_string);
    if (count($t_string[0]) - 0 > $sublen)
        $string = join('', array_slice($t_string[0], 0, $sublen)) . "…";
    else
        $string = join('', array_slice($t_string[0], 0, $sublen));

    return $string;
}

function echo_json($status,$title,$message,$url,$time){
	$data['status']=$status;
	$data['title']=$title;
	$data['message']=$message;
	$data['url']=$url;
	$data['time']=$time;
	echo json_encode($data);
        exit();
	
}

// Xml 转 数组, 包括根键 
function xml_to_array($xml) {
    $reg = "/<(\w+)[^>]*>([\\x00-\\xFF]*)<\\/\\1>/";
    if (preg_match_all($reg, $xml, $matches)) {
        $count = count($matches[0]);
        for ($i = 0; $i < $count; $i++) {
            $subxml = $matches[2][$i];
            $key = $matches[1][$i];
            if (preg_match($reg, $subxml)) {
                $arr[$key] = xml_to_array($subxml);
            } else {
                $arr[$key] = $subxml;
            }
        }
    }
    return $arr;
}

// Xml 转 数组, 不包括根键 
function xmltoarray($xml) {
    $arr = xml_to_array($xml);
    $key = array_keys($arr);
    return $arr[$key[0]];
}

// 截取中文字符串 
function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true)  
{  
    if(function_exists("mb_substr")){  
        if($suffix)  
             return mb_substr($str, $start, $length, $charset)."";  
        else 
             return mb_substr($str, $start, $length, $charset);  
    }  
    elseif(function_exists('iconv_substr')) {  
        if($suffix)  
             return iconv_substr($str,$start,$length,$charset)."";  
        else 
             return iconv_substr($str,$start,$length,$charset);  
    }  
    $re['utf-8']   = "/[x01-x7f]|[xc2-xdf][x80-xbf]|[xe0-xef][x80-xbf]{2}|[xf0-xff][x80-xbf]{3}/";  
    $re['gb2312'] = "/[x01-x7f]|[xb0-xf7][xa0-xfe]/";  
    $re['gbk']    = "/[x01-x7f]|[x81-xfe][x40-xfe]/";  
    $re['big5']   = "/[x01-x7f]|[x81-xfe]([x40-x7e]|xa1-xfe])/";  
    preg_match_all($re[$charset], $str, $match);  
    $slice = join("",array_slice($match[0], $start, $length));  
    if($suffix) return $slice."…";  
    return $slice;
}

//上传文件重命名
function getrand(){
	$now = $_SERVER['REQUEST_TIME'];//当前系统时间，比time()多5秒
	return md5(uniqid($now));
}



?>
