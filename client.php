<?php
	header("content-Type: text/html; charset=Utf-8");  

	/*//数据库服务器	
		$mysql_server_name='localhost';
		//数据库用户名
		$mysql_username='root';
		//数据库密码
		$mysql_password='';
		//数据库名
		$mysql_database='shop';


		$mysqli = new mysqli($mysql_server_name,$mysql_username,$mysql_password,$mysql_database);
		$mysqli->query("set names utf8");
		
		//$sql="select * from fx_order where order_statues = '2' and is_font_show = 1 order by add_time desc limit 0,10";
		$sql="UPDATE fx_order SET post_com_name = '顺丰123',post_no = '123123123123',order_statues = 3 WHERE order_no = '124184806'";
		$result = $mysqli->query($sql);
		if(mysqli_affected_rows($mysqli)){
			echo mysqli_affected_rows($mysqli);
		}
		else{
			echo mysqli_affected_rows($mysqli);
		}*/
		/*$tjjg=array();
        while($row=mysqli_fetch_array($result,MYSQLI_ASSOC))
        {
            echo $row['title']."<br/>";//这里可以输出结果
        }*/
		
    $client = new SoapClient("http://weixin1.qdtaineng.cn:90/service.php?wsdl");
	
    $data = $client->getQf('0450030230601');
	
	//$data1 = $client->updateOrderLogistics('124184806','顺丰','6252626173411');

	//$data2 = $client->updateOrderStatus('124184806');
	//$obj = json_decode($json);　　

	
	print_r($data."<br/>");
	//print($data1."<br/>");
	//print($data2."<br/>");

	/*require_once("common.php");
	$ownerName = "张辉";
	$paperCode = "120108196505200528";
	$bankNumber = "6259061285990237";
	$houseCode = "5010-031-01-05-03";
	$itemcode = 'B';
	$year = '2015';
	
	$re= new common();
	$str = $re->ifBind($ownerName,$paperCode,$bankNumber);
	$str1 = $re->getNowCharge($houseCode);
	$str2 = $re->getLastCharge($houseCode,$itemcode,$year);
	$str3 = $re->getLastAmount($houseCode,$itemcode,$year);
	

    print($str."<br/><br/><br/>");
	print($str1."<br/><br/><br/>");
	print($str2."<br/><br/><br/>");
	print($str3."<br/><br/><br/>");*/

	 