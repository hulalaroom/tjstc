<?php
include("conf/config.php");//引入配置文件
include("common/function.php");//引入公共函数

//创建菜单
function createMenu($data){
    $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];//返回回复数据
    $ch = curl_init();
    $ACCESS_TOKEN = getAccessToken();
    curl_setopt($ch, CURLOPT_URL, "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$ACCESS_TOKEN);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $tmpInfo = curl_exec($ch);
    if (curl_errno($ch)) {
      return curl_error($ch);
    }

    curl_close($ch);
    return $tmpInfo;

}

//获取菜单
function getMenu(){
	return file_get_contents("https://api.weixin.qq.com/cgi-bin/menu/get?access_token=".$ACCESS_TOKEN);
}

//删除菜单
function deleteMenu(){
	return file_get_contents("https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=".$ACCESS_TOKEN);
}

$data = ' {
     "button":[
     
      {
           "name":"信息查询",
           "sub_button":[
            {
               "type":"click",
               "name":"基础信息",
               "key":"jcxx"
            },
            {
               "type":"click",
               "name":"用量信息",
               "key":"ylxx"
            },
			{
               "type":"click",
               "name":"费用信息",
               "key":"fyxx"
            }]
       },
	   {
           "name":"热门资讯",
           "sub_button":[
            {
               "type":"click",
               "name":"服务公告",
               "key":"fwgg"
            },
			{
               "type":"click",
               "name":"信息公开",
               "key":"xxgk"
            }]
       },
	   {
           "name":"便民服务",
           "sub_button":[
            {
               "type":"click",
               "name":"天气查询",
               "key":"tqcx"
            },
            {
               "type":"click",
               "name":"交通出行",
               "key":"jtcx"
            },
			{
              "type":"view",
               "name":"应用下载",
               "url":"http://stckf.sinaapp.com/tpl/bx.php"
            }
			]
       }
	   
	   
	   
	   
	   ]
 }';

echo createMenu($data);
