<?php
    //数据库服务器	
	/*$mysql_server_name='10.102.12.63';
    //数据库用户名
	$mysql_username='root';
    //数据库密码
	$mysql_password='root';
    //数据库名
	$mysql_database='mycms';
	$conn=mysql_connect($mysql_server_name,$mysql_username,$mysql_password,$mysql_database);
	if (!$conn) {
	    die('Could not connect: ' . mysql_error());
	}
	echo 'Connected successfully';*/

	$conn=mysql_connect('10.102.12.63','root','root');
	if(!$conn) echo "失败!";
		else echo "成功!";
		 // 从表中提取信息的sql语句
		$sql="SELECT * FROM ad_user";
		// 执行sql查询
		$result=mysql_db_query('info', $sql, $conn);
		// 获取查询结果
		$row=mysql_fetch_row($result);
	 mysql_close();        
    
?>