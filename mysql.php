<? 
$dbh = @mysql_connect("10.102.12.63:3306","root","root"); 
/* 定义变量dbh , mysql_connect()函数的意思是连接mysql数据库, "@"的意思是屏蔽报错 */ 
if(!$dbh){die("error");} 
/* die()函数的意思是将括号里的字串送到浏览器并中断PHP程式 (Script)。括号里的参数为欲送出的字串。 */
@mysql_select_db("mycms", $dbh); 
?> 
