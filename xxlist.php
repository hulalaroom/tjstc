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
			$sql = "select id,title,content,pic,url from ad_article where (cat_id=4 or cat_id=5 or cat_id=6 or cat_id=7 or cat_id=174 or cat_id=175 or cat_id=176 or cat_id=177 or cat_id=178 or cat_id=179 or cat_id=180 or cat_id=181 or cat_id=183 or cat_id=184 or cat_id=185 or cat_id=186 or cat_id=187 or cat_id=188 or cat_id=189 or cat_id=190 or cat_id=191 or cat_id=192 or cat_id=193 or cat_id=194 or cat_id=195) and status=1 and isre=1 and ishot=1 order by id desc limit 30";
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