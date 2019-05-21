<?php
	/*用户绑定数据库操作
	//引入配置文件
	include("mysql.php");
	//密码
	$encrypt = "46faa8ab560de8e86ee20ce678eeb8"; 
	$password = md5(md5($encrypt) . md5(trim("shandian")));
	/* sql语句 */
	$sql = "select id from ad_user where username='shandian' and password='$password' and wxusername=''";
	/* sql执行 */
	$rs = mysql_query($sql, $dbh);
	/* 定义变量 rs ,函数mysql_query()的意思是:送出 query 字串供 MySQL 做相关的处理或者执行.由于php是从右往左执行的,所以,rs的值是服务器运行mysql_query()函数后返回的值 */
	if(!$rs){die("Valid result!");}
	/* 数据循环输出 */
	$row = mysql_fetch_array($rs);	
	if($row){
		//将用户openid与该记录绑定
		$id=$row[0];
		$sql1 = "update ad_user set wxusername='owJ2ct_AF55ETBJFf5UlIozImt9M' where id=$id";
		$sql2 = "update ad_user_room set wxusername='owJ2ct_AF55ETBJFf5UlIozImt9M' where uid=$id";
		$rs1 = mysql_query($sql1, $dbh);
		$rs2 = mysql_query($sql2, $dbh);
		if(!$rs1){
			die("绑定失败！可能是资料不正确、操作超时或者该账号已经绑定，请重新输入用户名+密码进行绑定！");
		}
		else{
			die("绑定成功！请输入help或者点击下方菜单选择操作，谢谢您的配合！");
		}
		
	}
	else{
		echo "资料不吻合";
	}	
	/* 关闭到mysql数据库的连接 */ 
	@mysql_close($dbh); 



	//用户是否绑定数据库操作
	//引入配置文件
	include("mysql.php");
	/* sql语句 */
	$sql = "select * from ad_user where wxusername='owJ2ct_AF55ETBJFf5UlIozImt9M'";
	/* sql执行 */
	$rs = mysql_query($sql, $dbh);
	/* 定义变量 rs ,函数mysql_query()的意思是:送出 query 字串供 MySQL 做相关的处理或者执行.由于php是从右往左执行的,所以,rs的值是服务器运行mysql_query()函数后返回的值 */
	if(!$rs){die("Valid result!");}
	/* 数据循环输出 */
	$row = mysql_fetch_array($rs);	
	if($row){
		echo "已经绑定";
	}
	else{
		echo "未绑定";
	}	
	/* 关闭到mysql数据库的连接 */ 
	@mysql_close($dbh); */


	//获取用户绑定房间编号
	//引入配置文件
	include("mysql.php");
	/* sql语句 */
	$sql = "select houseCode from ad_user_room where wxusername='owJ2ct_AF55ETBJFf5UlIozImt9M' order by id asc limit 1";
	/* sql执行 */
	$rs = mysql_query($sql, $dbh);
	/* 定义变量 rs ,函数mysql_query()的意思是:送出 query 字串供 MySQL 做相关的处理或者执行.由于php是从右往左执行的,所以,rs的值是服务器运行mysql_query()函数后返回的值 */
	if(!$rs){die("Valid result!");}
	/* 数据循环输出 */
	while($row = mysql_fetch_row($rs))	echo "$row[0]";
	/* 关闭到mysql数据库的连接 */ 
	@mysql_close($dbh);

?>  