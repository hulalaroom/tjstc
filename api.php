<?php
$url = "http://127.0.0.1/TjstcWebImpl/GetHouseInfoServlet";
$post_data = array(
    'vHOUSECODE' => '5003-003-02-03-02'
);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
$output = curl_exec($ch);
$date=json_decode($output,true);
curl_close($ch);
var_dump($output);
