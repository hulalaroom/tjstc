<?php


class WeatherAction extends HomeAction {
    
   
   
    
   public function index(){
       $qixian = array(
           "00"=>"晴",
           "01"=>"多云",
           "02"=>"阴",
           "03"=>"阵雨",
           "04"=>"雷阵雨",
           "05"=>"雷阵雨伴有冰雹",
           "06"=>"雨夹雪",
           "07"=>"小雨",
           "08"=>"中雨",
           "09"=>"大雨",
           "10"=>"暴雨",
           "11"=>"大暴雨",
           "12"=>"特大暴雨",
           "13"=>"阵雪",
           "14"=>"小雪",
           "15"=>"中雪",
           "16"=>"大雪",
           "17"=>"暴雪",
           "18"=>"雾",
           "19"=>"冻雨",
           "20"=>"沙尘暴",
           "21"=>"小到中雨",
           "22"=>"中到大雨",
           "23"=>"大到暴雨",
           "24"=>"暴雨到大暴雨",
           "25"=>"大暴雨到特大暴雨",
           "26"=>"小到中雪",
           "27"=>"中到大雪",
           "28"=>"大到暴雪",
           "29"=>"浮尘",
           "30"=>"扬沙",
           "31"=>"强沙尘暴",
           "53"=>"霾",
           "99"=>"无"
       );
       $fengli = array(
           "0"=>"微风",
           "1"=>"3-4级",
           "2"=>"4-5级",
           "3"=>"5-6级",
           "4"=>"6-7级",
           "5"=>"7-8级",
           "6"=>"8-9级",
           "7"=>"9-10级",
           "8"=>"10-11级",
           "9"=>"11-12级"
       );
       $fengxiang = array(
           "0"=>"无持续风向",
           "1"=>"东北风",
           "2"=>"东风",
           "3"=>"东南风",
           "4"=>"南风",
           "5"=>"西南风",
           "6"=>"西风",
           "7"=>"西北风",
           "8"=>"北风",
           "9"=>"旋转风"
       );
        set_time_limit(0);
        $private_key = 'b79610_SmartWeatherAPI_255f7d8';
        $appid='5ad611ef04be26e9';
        $appid_six=substr($appid,0,6);
        $areaid = '101030100';
        $type='forecast_f';
        $date=date("YmdHi");
        $public_key="http://open.weather.com.cn/data/?areaid=".$areaid."&type=".$type."&date=".$date."&appid=".$appid;
        
        $key = base64_encode(hash_hmac('sha1',$public_key,$private_key,TRUE));
        
        $URL="http://open.weather.com.cn/data/?areaid=".$areaid."&type=".$type."&date=".$date."&appid=".$appid_six."&key=".urlencode($key);
        //echo $URL."<br />";
        
        $string=file_get_contents($URL);
        if($string==null || ""==$string){
            $out_weather = array(
                "weather" => "天津",
                "temp1" =>"0℃",
                "temp2" => "0℃",
                "city" => "晴"
            );
        }else{
            $temp = json_decode($string);
            $weather= null;
            if($temp->{'f'}->{'f1'}[0]->{'fa'}=$temp->{'f'}->{'f1'}[0]->{'fb'}){
                $weather= $qixian[$temp->{'f'}->{'f1'}[0]->{'fa'}];
            }else{
                $weather= $qixian[$temp->{'f'}->{'f1'}[0]->{'fa'}]."转".$qixian[$temp->{'f'}->{'f1'}[0]->{'fb'}];
            }
            $out_weather = array(
                "weather" => $weather,
                "temp1" =>$temp->{'f'}->{'f1'}[0]->{'fa'}."℃",
                "temp2" => $temp->{'f'}->{'f1'}[0]->{'fa'}."℃",
                "city" => $temp->{'c'}->{'c3'}
            );
        }
        
        
        echo json_encode($out_weather);
    }
    
}