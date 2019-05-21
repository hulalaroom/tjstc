<!DOCTYPE HTML>
<html>
<HEAD>
<meta http-equiv="Content-Type" name="viewport" content="text/html; charset=utf-8; user-scalable=no" />
<link type="text/css" rel="stylesheet" href="/public/css/business.css"/>
<TITLE>服务公告</TITLE>
</HEAD>
<body>
    <div class="bb_title">服务公告
        <div class="bb_home_btn"><a><img src="http://stckf.sinaapp.com/public/images/home.png" width="30" height="30"></a></div>
    </div>
    <div class="bb_box">
        <ul>
		  <?php
			//引入配置文件
			include("mysql.php");
			/* sql语句 */
			$sql = "select id,title,content,pic,url from ad_article where (cat_id= 1 or cat_id=2 or cat_id=3 or cat_id= 167 or cat_id= 168 or cat_id= 169 or cat_id= 182 or cat_id= 170 or cat_id= 171 or cat_id= 172 or cat_id= 173) and status=1 and isre=1 and ishot=1 order by id desc limit 30";
			/* sql执行 */
			$rs = mysql_query($sql, $dbh);
			/* 定义变量 rs ,函数mysql_query()的意思是:送出 query 字串供 MySQL 做相关的处理或者执行.由于php是从右往左执行的,所以,rs的值是服务器运行mysql_query()函数后返回的值 */
			if(!$rs){die("Valid result!");}
			/* 数据循环输出 */
			while($row = mysql_fetch_row($rs))	echo "<li><a href='http://www.66885890.com:9098/view.php?id=$row[0]'>$row[1]</a></li>";
			/* 关闭到mysql数据库的连接 */ 
			@mysql_close($dbh); 
          ?>  
        </ul>
    </div>
</body>
</html>