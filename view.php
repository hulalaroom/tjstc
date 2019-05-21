<!DOCTYPE HTML>
<html>
<HEAD>
<meta http-equiv="Content-Type" name="viewport" content="text/html; charset=utf-8; user-scalable=no" />
<link type="text/css" rel="stylesheet" href="/public/css/business.css"/>
<TITLE>文章详情</TITLE>
</HEAD>
<body>
    <?php
        //引入配置文件
        include("mysql.php");
		//获取id
        $id=$_REQUEST['id'];
        /* sql语句 */
		$sql = "select title,content,tag,from_unixtime(start_time) from ad_article where id=$id";
		/* sql执行 */
		$rs = mysql_query($sql, $dbh);
        /* 定义变量 rs ,函数mysql_query()的意思是:送出 query 字串供 MySQL 做相关的处理或者执行.由于php是从右往左执行的,所以,rs的值是服务器运行mysql_query()函数后返回的值 */
		if(!$rs){die("Valid result!");}
		/* 数据循环输出 */
		$row = mysql_fetch_array($rs);
		//内容去除html
		//echo $content = strip_tags($row[1],'<br>');
		//时间转换
		//print_r(date("Y-m-d H:i:s",$row[3]));
		echo "<div class=bb_title>$row[0]</div>  
		  <div class=bb_info_box> <span>信息来源：$row[2]</span><br/>
		  <span>发布时间：$row[3]</span></div>
		  <div class=bb_box1>$row[1]</div>";
		/* 关闭到mysql数据库的连接 */ 
		@mysql_close($dbh); 
    ?>  
</body>
</html>