<?php
    //���ݿ������	
	/*$mysql_server_name='10.102.12.63';
    //���ݿ��û���
	$mysql_username='root';
    //���ݿ�����
	$mysql_password='root';
    //���ݿ���
	$mysql_database='mycms';
	$conn=mysql_connect($mysql_server_name,$mysql_username,$mysql_password,$mysql_database);
	if (!$conn) {
	    die('Could not connect: ' . mysql_error());
	}
	echo 'Connected successfully';*/

	$conn=mysql_connect('10.102.12.63','root','root');
	if(!$conn) echo "ʧ��!";
		else echo "�ɹ�!";
		 // �ӱ�����ȡ��Ϣ��sql���
		$sql="SELECT * FROM ad_user";
		// ִ��sql��ѯ
		$result=mysql_db_query('info', $sql, $conn);
		// ��ȡ��ѯ���
		$row=mysql_fetch_row($result);
	 mysql_close();        
    
?>