<?php
	
class DatabaseAction extends CommonAction {
		
	//读取已备份文件列表
    public function index() {
		$dir = "./Backup/backdb/";
		$handler = opendir($dir);
		$dblist = array();
		$i = 0;
		while( ($filename = readdir($handler)) !== false )
		{	
			if($filename != '.' && $filename != '..'){
				
				$dblist[$i]['filename'] = $filename;
				$dblist[$i]['download'] = $dir.$filename;
				$i++;
			}
		}
		closedir($handler);//关闭文件
		$this->assign('dblist',$dblist);
		$this->display();  
    }
	//删除备份文件
	public function delete(){
		
		$dir = "./Backup/backdb/";
		$filename = trim($_GET['filename']);
		$file = $dir.$filename;
		if (!unlink($file))
		  {
			$this->error('删除失败');
		  }
		$this->success('删除成功！');
	}
	//恢复数据库
	public function recdb(){
		$filename = trim($_GET['filename']);
		if(!empty($filename)){
			import("@.ORG.MySQLReback");
			$DataDir = "./Backup/backdb/";
			//获得数据库信息
			$config = array(
				'host' => C('DB_HOST'),
				'port' => C('DB_PORT'),
				'userName' => C('DB_USER'),
				'userPassword' => C('DB_PWD'),
				'dbprefix' => C('DB_PREFIX'),
				'charset' => 'UTF8',
				'path' => $DataDir,
			);
		$mr = new MySQLReback($config);
		$r = $mr ->recover($filename);
		}
		if($r == true){
			$this->success( '数据库恢复成功！','__URL__/index');
		}
			
		
	}
	//数据库备份
	public function backdb() {
			
			import("@.ORG.MySQLReback");
			$DataDir = "./Backup/backdb/";
			//获得数据库信息
			$config = array(
				'host' => C('DB_HOST'),
				'port' => C('DB_PORT'),
				'userName' => C('DB_USER'),
				'userPassword' => C('DB_PWD'),
				'dbprefix' => C('DB_PREFIX'),
				'charset' => 'UTF8',
				'path' => $DataDir,
				'isCompress' => 0, //是否开启gzip压缩
				'isDownload' => 0  //是否开启下载
			);
			$mr = new MySQLReback($config);
            $mr->setDBName(C('DB_NAME'));
			if($mr->backup()){
				 $this->success( '数据库备份成功！','__URL__/index');
			}
           
    }
	
	// 清除后台缓存
	public function clear_admin()
	{	
		 $path = "./Cache/Admin/";
		 $r = delDirAndFile($path,true);
		 if($r == false){
			$this->error('操作失败！');
		 }
		 $this->success( '后台清除缓存成功！');	
	}
	// 清除前台缓存
	public function clear_home()
	{
		 $path = "./Cache/Home/";
		 $r = delDirAndFile($path,true);
		 
		 $this->success( '前台清除缓存成功！');	
	}
}
?>
