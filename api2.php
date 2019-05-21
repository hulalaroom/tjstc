<?php
header('content-type:text/html;charset=utf-8');
$url = "http://www.66885890.com:9898/TjstcWebImplTiaoShi/GetInvolveTypeServlet";
$post_data ="vINVOLVEINDUSTRY=能源经营服务";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
$output = curl_exec($ch);
$date=json_decode($output,true);
var_dump($date);
curl_close($ch);