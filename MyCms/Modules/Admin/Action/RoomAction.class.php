<?php

class RoomAction extends AdminAction {

    public function index() {
        //用户组列表
        $this->user_list('');
        //用户组查询
        if (is_numeric($_GET['uid'])) {
            $map['uid'] = array('eq', $_GET['uid']);
            $index['uid'] = $_GET['uid'];
        }
		//var_dump($_GET);
		//注册用户名
		if (!empty($_GET['username'])) {
            $map['a.username'] = array('eq', $_GET['username']);
			$map['_logic'] = 'and';
            $index['username'] = $_GET['username'];
        }
		//注册手机号
		if (!empty($_GET['phone'])) {
            $map['a.phone'] = array('eq', $_GET['phone']);
			$map['_logic'] = 'and';
            $index['phone'] = $_GET['phone'];
        }
		//房间编号-模糊
		if (!empty($_GET['housecode'])) {
            $map['b.housecode'] = array('like', '%' . $_GET['housecode'] . '%');
			$map['_logic'] = 'and';
            $index['housecode'] = $_GET['housecode'];
        }
		 if (is_numeric($_GET['limit'])) { 
			$index['limit'] = $_GET['limit'];
        }
		//小区
		if (!empty($_GET['xiaoqu'])) {
            $map['b.housecode'] = array('like', $_GET['xiaoqu'] . '%');
			$map['_logic'] = 'and';
            $index['xiaoqu'] = $_GET['xiaoqu'];
        }
		//房间地址-模糊
		if (!empty($_GET['address'])) {
            $map['b.address'] = array('like', '%' . $_GET['address'] . '%');
			$map['_logic'] = 'and';
            $index['address'] = $_GET['address'];
        }
		//业主姓名-模糊
		if (!empty($_GET['ownerName'])) {
            $map['b.ownerName'] = array('eq', $_GET['ownerName']);
			$map['_logic'] = 'and';
            $index['ownerName'] = $_GET['ownerName'];
        }
		//身份证号
		if (!empty($_GET['paperCode'])) {
            $map['b.paperCode'] = array('eq', $_GET['paperCode']);
			$map['_logic'] = 'and';
            $index['paperCode'] = $_GET['paperCode'];
        }
		//手机号码（绑定房间）
		if (!empty($_GET['bphone'])) {
            $map['b.phone'] = array('eq', $_GET['bphone']);
			$map['_logic'] = 'and';
            $index['bphone'] = $_GET['bphone'];
        }
		//能源卡号
		if (!empty($_GET['bankNumber'])) {
            $map['b.bankNumber'] = array('eq', $_GET['bankNumber']);
			$map['_logic'] = 'and';
            $index['bankNumber'] = $_GET['bankNumber'];
        }
		//环保积分卡号
		if (!empty($_GET['cardnum'])) {
            $map['b.cardnum'] = array('eq', $_GET['cardnum']);
			$map['_logic'] = 'and';
            $index['cardnum'] = $_GET['cardnum'];
        }
		
		//是否解绑
		if ($_GET['sfjb']!='') {
            $map['b.ifBind'] = array('eq', $_GET['sfjb']);
			$map['_logic'] = 'and';
            $index['sfjb'] = $_GET['sfjb'];
        }
        //关键词
        /*if (!empty($_GET['keyword'])) {
			if($_GET['types']=="phone")
			{   
				
				
			$id=array();
			$wid=array();
			$id['phone']=$_GET['keyword'];
			
			
			$wid=  D('User')->where($id)->getField('id',true);
			
            $map['uid'] = array('like',$wid[0]);
            $index['types'] = $_GET['types'];
            $index['keyword'] =$_GET['keyword'];
			
			
			}
            else{$map[$_GET['types']] = array('like', '%' . $_GET['keyword'] . '%');
            $index['types'] = $_GET['types'];
            $index['keyword'] = $_GET['keyword'];
			
			}
        }*/
        //每页条数
        if (!empty($_GET['limit'])) {
            $limit = $_GET['limit'];
            $index['limit'] = $_GET['limit'];
        } else {
            $limit = C('PAGE_SIZE');
            $index['limit'] = C('PAGE_SIZE');
        }
        //列表
		//var_dump($map);
        import("ORG.Util.Page");
        $count = D('User')
					->alias('a')
					->join('left JOIN ad_user_room b on a.id=b.uid')
					->where($map)
					->relation(true)
					->count();
        $page = new Page($count, $limit);
		$order='b.bindtime  DESC';
        $room_list = D('User')
					->alias('a')
					->join('left JOIN ad_user_room b on a.id=b.uid')
					->Field('a.phone as aphone,b.phone as bphone,a.*,b.*')
					//->join('ad_user_room on a.id=ad_user_room.uid','LEFT')
					->where($map)->limit($page->firstRow . ',' . $page->listRows)
					->order($order)
					->relation(true)
					->select();
		//echo   D('User')->getLastSql();
        //var_dump($room_list);exit;
	   for($i=0;$i<count($room_list);$i++){
			$abc=$room_list[$i]["ifBind"];
			if($abc==1){
				$room_list[$i]["ifBind"]='未解绑';
			}else{
				$room_list[$i]["ifBind"]='已解绑';
			}
		}
        $this->assign('room_list', $room_list);
		$xiaoqulist = D('survey_heating_address')->select();
        $this->assign('xiaoqulist', $xiaoqulist);
        //分页显示及默认页数
        $show = $page->show();
        $this->assign('page', $show); // 赋值分页输出
        $this->assign('p', C('PAGE_SIZE'));
        //输出搜索的条件
        $this->assign($index);
        $this->display();
    }

    public function user_list($w) {
        $ulist = D('User')->where($w)->select();
        $this->assign('ulist', $ulist);
        return;
    }

	//绑定房间
	public function add() {
		$username=$_GET['username'];
		$this->assign('username', $username);
        $this->display();
    }
	
	//绑定房间insert
	public function insert() {
		//插入房间
		$room = D('User_room');
		//获取房间基本信息
		$map['username'] = $_POST['username'];
		$data['houseCode'] = $_POST['houseCode'];
		$data['houseName'] = $_POST['address'];
		$data['address'] = $_POST['address'];
		$data['ifBind'] = 1;
		$data['bindtime'] = strtotime(date('Y-m-d H:i:s',time()));
		//获取用户ID
		$id = D('User')->where($map)->getField('id');
		if(empty($id)){
			$this->error('用户不存在', U('Room/add'));
		}
		$data['uid'] = $id;
        try {
			$ret = $room->add($data);
		} catch (Exception $ex) {
			$ret = false;
		}
		if ($ret === false) {
			$this->error('绑定失败', U('Room/add'));
		} else {
			$this->success("绑定成功", U('Room/index'));
		}
    }

    //批量操作
    public function bat() {
        $command = $this->_param('command');
        $ids = $this->_param('id');
        if (empty($command)) {
            $this->error('没有选择操作！');
            exit();
        }
        if (is_array($ids)) {
            $ids = implode(',', $ids);
        }
//         dump($ids);
        if (empty($ids)) {
            $this->error('没有选择记录！');
            exit();
        }
        $map['uid'] = array('in', $ids);
        $where['mid'] = array('in', $ids);
        switch ($command) {
            case 'delete':
				$data['ifBind']=0;
                $re = D('UserRoom')->where($map)->save($data);
                $type = "解绑";
				//echo   D('UserRoom')->getLastSql();
                break;
        }
        if ($re !== false) {
            $this->success('成功' . $type);
        } else {
            $this->error('失败！');
        }
    }

	public function roomexcel(){
        $ResultsData = D('User')
					->alias('a')
					->join('left JOIN ad_user_room b on a.id=b.uid')
					->Field('a.phone as aphone,b.phone as bphone,a.*,b.*')
					->relation(true)
					->select();
        vendor("PHPExcel.PHPExcel");  
        // 建立PHPExcel对象  
        $objPHPExcel = new PHPExcel();  
        //设置宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(50);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(50);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(50);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(50);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(50);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(50);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(50);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(50);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(50);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(50);  
		$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(50);  
		$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(50);  
		$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(50);

        //设置水平居中   
        $objPHPExcel->getActiveSheet()->getStyle('A')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);  
        $objPHPExcel->getActiveSheet()->getStyle('B')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
		$objPHPExcel->getActiveSheet()->getStyle('C')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
        $objPHPExcel->getActiveSheet()->getStyle('D')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
		$objPHPExcel->getActiveSheet()->getStyle('E')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
        $objPHPExcel->getActiveSheet()->getStyle('F')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);  
        $objPHPExcel->getActiveSheet()->getStyle('G')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);  
        $objPHPExcel->getActiveSheet()->getStyle('H')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);  
        $objPHPExcel->getActiveSheet()->getStyle('I')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);  
		$objPHPExcel->getActiveSheet()->getStyle('J')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);  
		$objPHPExcel->getActiveSheet()->getStyle('K')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
		$objPHPExcel->getActiveSheet()->getStyle('L')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->getStyle('M')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

  
        // 设置首行文字  
        $objPHPExcel->setActiveSheetIndex(0)  
            ->setCellValue('A1', '注册用户名')  
            ->setCellValue('B1', '注册手机号')  
            ->setCellValue('C1', '房间编号')  
            ->setCellValue('D1', '房间地址')  
            ->setCellValue('E1', '业主姓名')  
            ->setCellValue('F1', '身份账号')  
            ->setCellValue('G1', '绑定房间手机号')  
            ->setCellValue('H1', '能源卡号')  
            ->setCellValue('I1', '卡片期限')  
            ->setCellValue('J1', '环保积分卡号')
			->setCellValue('K1', '住户人数')
			->setCellValue('L1', '绑定房间时间')
			->setCellValue('M1', '房间是否绑定'); 
  
        // 填充数据(UTF-8)  
        for($i=0;$i<count($ResultsData);$i++){  
			
				$objPHPExcel->getActiveSheet(0)->setCellValue('A'.($i+2), $ResultsData[$i]['username']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('B'.($i+2), $ResultsData[$i]['aphone']); 
				$objPHPExcel->getActiveSheet(0)->setCellValue('C'.($i+2), $ResultsData[$i]['houseCode']);   
				$objPHPExcel->getActiveSheet(0)->setCellValue('D'.($i+2), $ResultsData[$i]['address']);
				$objPHPExcel->getActiveSheet(0)->setCellValue('E'.($i+2), $ResultsData[$i]['ownerName']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('F'.($i+2), $ResultsData[$i]['paperCode']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('G'.($i+2), $ResultsData[$i]['bphone']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('H'.($i+2), $ResultsData[$i]['bankNumber']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('I'.($i+2), $ResultsData[$i]['bankEndData']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('J'.($i+2), $ResultsData[$i]['cardnum']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('K'.($i+2), $ResultsData[$i]['houseHoldSize']);
				$objPHPExcel->getActiveSheet(0)->setCellValue('L'.($i+2), date('Y-m-d H:i:s',$ResultsData[$i]['bindtime']));
				$objPHPExcel->getActiveSheet(0)->setCellValue('M'.($i+2), $ResultsData[$i]['ifBind']); 
			
        }  
  
  
        // sheet命名  
        $objPHPExcel->getActiveSheet()->setTitle('用户房间信息');  
  
  
        //设置第一个页签
        $objPHPExcel->setActiveSheetIndex(0);  
  
        // excel头参数  
        header('Content-Type: application/vnd.ms-excel');  
        header('Content-Disposition: attachment;filename="用户房间信息('.date('Y-m-d').').xls"');  //日期为文件名后缀  
        header('Cache-Control: max-age=0');  
  
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  //excel5为xls格式，excel2007为xlsx格式  
        $objWriter->save('php://output'); 
	}
}

?>
