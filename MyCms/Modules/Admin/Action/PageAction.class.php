<?php

class PageAction extends AdminAction {

    public function index() {

        //栏目类别
        $cate = D('Cate')->order('sort Asc')->relation(true)->select();
        $this->list = get_cate($cate, '&nbsp;&nbsp;├&nbsp;&nbsp;');
        //栏目查询
        if (is_numeric($_GET['cat_id'])) {
            $cids = $_GET['cat_id'];
            $map['id'] = array('eq', $cids);
            $index['id'] = $_GET['cat_id'];
        }
		//dump($_GET['id']);
        //每页条数
        if (!empty($_GET['limit'])) {
            $limit = $_GET['limit'];
            $index['limit'] = $_GET['limit'];
        } else {
            $limit = C('PAGE_SIZE');
            $index['limit'] = C('PAGE_SIZE');
        }
        //关键词
        if (!empty($_GET['keyword'])) {
            $map[$_GET['types']] = array('like', '%' . $_GET['keyword'] . '%');
            $index['types'] = $_GET['types'];
            $index['keyword'] = $_GET['keyword'];
        }
		//区分管理员发布的单页文章
		if (false === $_SESSION['is_supper_admin']) {
			if(($_SESSION['common_admin_id'] == 5) or ($_SESSION['common_admin_id'] == 12)){
				$map['diff'] = 5;
			}
			else if(($_SESSION['common_admin_id'] == 6) or ($_SESSION['common_admin_id'] == 13) or ($_SESSION['common_admin_id'] == 14) or ($_SESSION['common_admin_id'] == 15)){
				$map['diff'] = 6;
			}
			else{
				$map['diff'] = $_SESSION['common_admin_id'];
			}
			
		}
		//区分管理员
		$this->assign('common_id', $_SESSION['common_admin_id']);
		//dump($map);
		//return;
        //文章列表
        import("ORG.Util.Page");
        $count = D('Page')->where($map)->count();
        $page = new Page($count, $limit);
        $page_list = D('Page')->where($map)->limit($page->firstRow . ',' . $page->listRows)->order('id DESC')->relation(true)->select();

        $this->assign('page_list', $page_list);
        //分页显示及默认页数
        $show = $page->show();
        $this->assign('only_page', $show); // 赋值分页输出
        $this->assign('p', C('PAGE_SIZE'));
        //输出搜索的条件
        $this->assign($index);

        $this->display();
    }

    public function edit() {
        $id = intval($this->_param('id'));
        if ($id <= 0) {
            $this->error('参数错误！');
        } else {

            //根据id查找内容
            $list = D('Page')->where('id=' . $id)->relation(true)->find();
            if (!empty($list['pics'])) {
                $v = explode(":::", $list['pics']);
                foreach ($v as $a => $r) {
                    $t = explode('||', $r);
                    $s[$a]['pic'] = $t[0];
                    $s[$a]['title'] = $t[1];
                    $sname = explode('/', $s[$a]['pic']);
                    $s[$a]['savename'] = $sname[4];
                }
                $this->assign('piclist', $s);
            }
            $this->assign($list);
            $this->assign('type', 'edit');
            $this->display('add');
        }
    }

    public function update() {
        $data = D('Page')->create();
        $pics = $_POST['pics'];
        $ptitle = $_POST['pictitle'];
        //存储数据

        if (is_array($pics)) {
            $data['pics'] = "";
            foreach ($pics as $k => $val) {

                $data['pics'].=$val . "||" . $ptitle[$k] . ":::";
            }
            $data['pics'] = substr($data['pics'], 0, -3);
        }
        $data['admin_id'] = $_SESSION['myid'];
        $r = D('Page')->save($data);
        if ($r === FALSE) {
            $this->error('修改失败', '__URL__/index');
        } else {
            $this->success('修改成功', '__URL__/index');
        }
    }

	//供热优惠申请
	public function apply() {
		//每页条数
		if (!empty($_GET['limit'])) {
			$limit = $_GET['limit'];
			$index['limit'] = $_GET['limit'];
		} else {
			$limit = C('PAGE_SIZE');
			$index['limit'] = C('PAGE_SIZE');
		}
		//用户名
		if (!empty($_GET['username'])) {
			$username = $_GET['username'];
			$data['username'] = array('like', '%' . $username . '%');
            $index['username'] = $username;
        }
		if (!empty($_POST['username'])) {
			 $username = $_POST['username'];
			 $data['username'] = array('like', '%' . $username . '%');
             $index['username'] = $username;
        }
		//房间编号
		if (!empty($_GET['housecode'])) {
			$housecode = $_GET['housecode'];
			$data['housecode'] = array('like', '%' .  $housecode . '%');
            $index['housecode'] = $housecode;
        }
		if (!empty($_POST['housecode'])) {
			 $housecode = $_POST['housecode'];
			 $data['housecode'] = array('like', '%' .  $housecode . '%');
             $index['housecode'] = $housecode;
        }
		//联系电话
		if (!empty($_GET['phone'])) {
			$phone = $_GET['phone'];
			$data['phone'] = array('like', '%' . $phone . '%');
            $index['phone'] = $phone;
        }
		if (!empty($_POST['phone'])) {
			$phone = $_POST['phone'];
			 $data['phone'] = array('like', '%' . $phone . '%');
             $index['phone'] = $phone;
        }
		//开始时间
        if (!empty($_GET['qishishijian'])) {
			$qishishijian = $_GET['qishishijian'];
            $index['qishishijian'] = $_GET['qishishijian'];
        }
		if (!empty($_POST['qishishijian'])) {
			$qishishijian = $_POST['qishishijian'];
            $index['qishishijian'] = $_POST['qishishijian'];
        }
		//结束时间
        if (!empty($_GET['jieshushijian'])) {
          $jieshushijian = $_GET['jieshushijian'];
          $index['jieshushijian'] = $_GET['jieshushijian'];
        }
		if (!empty($_POST['jieshushijian'])) {
			$jieshushijian = $_POST['jieshushijian'];
            $index['jieshushijian'] = $_POST['jieshushijian'];
        }
		
		//查询条件
		if ((!empty($qishishijian)) && (!empty($jieshushijian))) {
			 $qishishijian = $qishishijian."00:00:00";
			 $jieshushijian = $jieshushijian."23:59:59";
			 $data['createtime'] = array(between,array(strtotime($qishishijian),strtotime($jieshushijian)));
        }
		if((!empty($qishishijian)) && (empty($jieshushijian))){
			$qishishijian = $qishishijian."00:00:00";
			$data['createtime'] = array(between,array(strtotime($qishishijian),time()));
		}
		if((empty($qishishijian)) && (!empty($jieshushijian))){
			$jieshushijian = $jieshushijian."23:59:59";
			$data['createtime'] = array(between,array(strtotime("23 March 2015"),strtotime($jieshushijian)));
		}

		//优惠申请结果
		//文章列表
		import("ORG.Util.Page");
		$count = D('Apply')->where($data)->count();
		$page = new Page($count, $limit);
		$result_list = D('Apply')->where($data)->limit($page->firstRow . ',' . $page->listRows)->order('id DESC')->select();
		//分页显示及默认页数
		$show = $page->show();
		$this->assign('page', $show); // 赋值分页输出
		$this->assign('p', C('PAGE_SIZE'));
		//平台信息查询
		$index['bangding'] = $bangding;
		$this->assign('result_list', $result_list);

		$this->assign($index);

        $this->display();
    }

	//供热优惠申请
	public function apply1() {
		//每页条数
		if (!empty($_GET['limit'])) {
			$limit = $_GET['limit'];
			$index['limit'] = $_GET['limit'];
		} else {
			$limit = C('PAGE_SIZE');
			$index['limit'] = C('PAGE_SIZE');
		}
		//用户名
		if (!empty($_GET['username'])) {
			$username = $_GET['username'];
			$data['username'] = array('like', '%' . $username . '%');
            $index['username'] = $username;
        }
		if (!empty($_POST['username'])) {
			 $username = $_POST['username'];
			 $data['username'] = array('like', '%' . $username . '%');
             $index['username'] = $username;
        }
		//房间编号
		if (!empty($_GET['housecode'])) {
			$housecode = $_GET['housecode'];
			$data['housecode'] = array('like', '%' .  $housecode . '%');
            $index['housecode'] = $housecode;
        }
		if (!empty($_POST['housecode'])) {
			 $housecode = $_POST['housecode'];
			 $data['housecode'] = array('like', '%' .  $housecode . '%');
             $index['housecode'] = $housecode;
        }
		//联系电话
		if (!empty($_GET['phone'])) {
			$phone = $_GET['phone'];
			$data['phone'] = array('like', '%' . $phone . '%');
            $index['phone'] = $phone;
        }
		if (!empty($_POST['phone'])) {
			$phone = $_POST['phone'];
			 $data['phone'] = array('like', '%' . $phone . '%');
             $index['phone'] = $phone;
        }
		//开始时间
        if (!empty($_GET['qishishijian'])) {
			$qishishijian = $_GET['qishishijian'];
            $index['qishishijian'] = $_GET['qishishijian'];
        }
		if (!empty($_POST['qishishijian'])) {
			$qishishijian = $_POST['qishishijian'];
            $index['qishishijian'] = $_POST['qishishijian'];
        }
		//结束时间
        if (!empty($_GET['jieshushijian'])) {
          $jieshushijian = $_GET['jieshushijian'];
          $index['jieshushijian'] = $_GET['jieshushijian'];
        }
		if (!empty($_POST['jieshushijian'])) {
			$jieshushijian = $_POST['jieshushijian'];
            $index['jieshushijian'] = $_POST['jieshushijian'];
        }
		
		//查询条件
		if ((!empty($qishishijian)) && (!empty($jieshushijian))) {
			 $qishishijian = $qishishijian."00:00:00";
			 $jieshushijian = $jieshushijian."23:59:59";
			 $data['createtime'] = array(between,array(strtotime($qishishijian),strtotime($jieshushijian)));
        }
		if((!empty($qishishijian)) && (empty($jieshushijian))){
			$qishishijian = $qishishijian."00:00:00";
			$data['createtime'] = array(between,array(strtotime($qishishijian),time()));
		}
		if((empty($qishishijian)) && (!empty($jieshushijian))){
			$jieshushijian = $jieshushijian."23:59:59";
			$data['createtime'] = array(between,array(strtotime("23 March 2015"),strtotime($jieshushijian)));
		}

		//优惠申请结果
		//文章列表
		import("ORG.Util.Page");
		$count = D('Apply1')->where($data)->count();
		$page = new Page($count, $limit);
		$result_list = D('Apply1')->where($data)->limit($page->firstRow . ',' . $page->listRows)->order('id DESC')->select();
		//分页显示及默认页数
		$show = $page->show();
		$this->assign('page', $show); // 赋值分页输出
		$this->assign('p', C('PAGE_SIZE'));
		//平台信息查询
		$index['bangding'] = $bangding;
		$this->assign('result_list', $result_list);

		$this->assign($index);

        $this->display();
    }

	//优惠申请详细信息
	 public function applyview() {
        $id = intval($this->_param('id'));
		$i = 1;
        if ($id <= 0) {
            $this->error('参数错误！');
        } else {
			$list = D('Apply')->where('id=' . $id)->find();
			//查询用户已绑定房间
			$this->assign('list', $list);
			$this->assign('id', $id);
            $this->display();
        }
    }

	//优惠申请详细信息
	 public function applyview1() {
        $id = intval($this->_param('id'));
		$i = 1;
        if ($id <= 0) {
            $this->error('参数错误！');
        } else {
			$list = D('Apply1')->where('id=' . $id)->find();
			//查询用户已绑定房间
			$this->assign('list', $list);
			$this->assign('id', $id);
            $this->display();
        }
    }


	//优惠申请详细信息结果导出
	public function exportapply(){
		//业务名称
		if (!empty($_POST['username'])) {
			 $username = $_POST['username'];
			 $data['username'] = array('like', '%' . $username . '%');
        }
		//房间编号
		if (!empty($_POST['housecode'])) {
			 $housecode = $_POST['housecode'];
			 $data['housecode'] = array('like', '%' .  $housecode . '%');
        }
		//联系电话
		if (!empty($_POST['phone'])) {
			$phone = $_POST['phone'];
			 $data['phone'] = array('like', '%' . $phone . '%');
        }
		//开始时间
		if (!empty($_POST['qishishijian'])) {
			$qishishijian = $_POST['qishishijian'];
        }
		//结束时间
		if (!empty($_POST['jieshushijian'])) {
			$jieshushijian = $_POST['jieshushijian'];
        }
		//查询条件
		if ((!empty($qishishijian)) && (!empty($jieshushijian))) {
			 $qishishijian = $qishishijian."00:00:00";
			 $jieshushijian = $jieshushijian."23:59:59";
			 $data['createtime'] = array(between,array(strtotime($qishishijian),strtotime($jieshushijian)));
        }
		if((!empty($qishishijian)) && (empty($jieshushijian))){
			$qishishijian = $qishishijian."00:00:00";
			$data['createtime'] = array(between,array(strtotime($qishishijian),time()));
		}
		if((empty($qishishijian)) && (!empty($jieshushijian))){
			$jieshushijian = $jieshushijian."23:59:59";
			$data['createtime'] = array(between,array(strtotime("01 June 2015"),strtotime($jieshushijian)));
		}
        $ResultsData = D('Apply')->where($data)->select();  //查询数据得到$ResultsData二维数组  
        vendor("PHPExcel.PHPExcel");  
        // 建立PHPExcel对象  
        $objPHPExcel = new PHPExcel();  
        //设置宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(50);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(50);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(40);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(40);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(30);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(50);  
  
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
  
        // 设置首行文字  
        $objPHPExcel->setActiveSheetIndex(0)  
            ->setCellValue('A1', '房间地址')  
            ->setCellValue('B1', '房间编号')  
            ->setCellValue('C1', '业主名称')  
            ->setCellValue('D1', '能源卡号')  
            ->setCellValue('E1', '采暖面积')  
            ->setCellValue('F1', '联系电话')  
            ->setCellValue('G1', '身份证号')  
            ->setCellValue('H1', '验证码')  
            ->setCellValue('I1', '申请时间'); 
  
        // 填充数据(UTF-8)  
        for($i=0;$i<count($ResultsData);$i++){ 
			$idcard = $ResultsData[$i]['idcard']." ";
			$cardnumber = $ResultsData[$i]['cardnumber']." ";
			$objPHPExcel->getActiveSheet(0)->setCellValue('A'.($i+2), $ResultsData[$i]['housename']);  
			$objPHPExcel->getActiveSheet(0)->setCellValue('B'.($i+2), $ResultsData[$i]['housecode']);  
			$objPHPExcel->getActiveSheet(0)->setCellValue('C'.($i+2), $ResultsData[$i]['username']); 
			$objPHPExcel->getActiveSheet(0)->setCellValue('D'.($i+2), $cardnumber);   
			$objPHPExcel->getActiveSheet(0)->setCellValue('E'.($i+2), $ResultsData[$i]['area']); 
			$objPHPExcel->getActiveSheet(0)->setCellValue('F'.($i+2), $ResultsData[$i]['phone']);  
			$objPHPExcel->getActiveSheet(0)->setCellValue('G'.($i+2), $idcard);  
			$objPHPExcel->getActiveSheet(0)->setCellValue('H'.($i+2), $ResultsData[$i]['code']);  
			$objPHPExcel->getActiveSheet(0)->setCellValue('I'.($i+2), date('Y-m-d H:i:s',$ResultsData[$i]['createtime']));  //时间戳转换
        }  
        // sheet命名  
        $objPHPExcel->getActiveSheet()->setTitle('2015-2016供热优惠申请');  
  
        //设置第一个页签
        $objPHPExcel->setActiveSheetIndex(0);  
  
        // excel头参数  
        header('Content-Type: application/vnd.ms-excel');  
        header('Content-Disposition: attachment;filename="2016-2017供热优惠申请('.date('Y-m-d').').xls"');  //日期为文件名后缀  
        header('Cache-Control: max-age=0');  
  
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  //excel5为xls格式，excel2007为xlsx格式  
        $objWriter->save('php://output'); 
	}


	//优惠申请详细信息结果导出
	public function exportapply1(){
		//业务名称
		if (!empty($_POST['username'])) {
			 $username = $_POST['username'];
			 $data['username'] = array('like', '%' . $username . '%');
        }
		//房间编号
		if (!empty($_POST['housecode'])) {
			 $housecode = $_POST['housecode'];
			 $data['housecode'] = array('like', '%' .  $housecode . '%');
        }
		//联系电话
		if (!empty($_POST['phone'])) {
			$phone = $_POST['phone'];
			 $data['phone'] = array('like', '%' . $phone . '%');
        }
		//开始时间
		if (!empty($_POST['qishishijian'])) {
			$qishishijian = $_POST['qishishijian'];
        }
		//结束时间
		if (!empty($_POST['jieshushijian'])) {
			$jieshushijian = $_POST['jieshushijian'];
        }
		//查询条件
		if ((!empty($qishishijian)) && (!empty($jieshushijian))) {
			 $qishishijian = $qishishijian."00:00:00";
			 $jieshushijian = $jieshushijian."23:59:59";
			 $data['createtime'] = array(between,array(strtotime($qishishijian),strtotime($jieshushijian)));
        }
		if((!empty($qishishijian)) && (empty($jieshushijian))){
			$qishishijian = $qishishijian."00:00:00";
			$data['createtime'] = array(between,array(strtotime($qishishijian),time()));
		}
		if((empty($qishishijian)) && (!empty($jieshushijian))){
			$jieshushijian = $jieshushijian."23:59:59";
			$data['createtime'] = array(between,array(strtotime("01 June 2015"),strtotime($jieshushijian)));
		}
        $ResultsData = D('Apply1')->where($data)->select();  //查询数据得到$ResultsData二维数组  
        vendor("PHPExcel.PHPExcel");  
        // 建立PHPExcel对象  
        $objPHPExcel = new PHPExcel();  
        //设置宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(50);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(50);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(40);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(40);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(30);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(50);  
  
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
  
        // 设置首行文字  
        $objPHPExcel->setActiveSheetIndex(0)  
            ->setCellValue('A1', '房间地址')  
            ->setCellValue('B1', '房间编号')  
            ->setCellValue('C1', '业主名称')  
            ->setCellValue('D1', '能源卡号')  
            ->setCellValue('E1', '采暖面积')  
            ->setCellValue('F1', '联系电话')  
            ->setCellValue('G1', '身份证号')  
            ->setCellValue('H1', '验证码')  
            ->setCellValue('I1', '申请时间'); 
  
        // 填充数据(UTF-8)  
        for($i=0;$i<count($ResultsData);$i++){ 
			$idcard = $ResultsData[$i]['idcard']." ";
			$cardnumber = $ResultsData[$i]['cardnumber']." ";
			$objPHPExcel->getActiveSheet(0)->setCellValue('A'.($i+2), $ResultsData[$i]['housename']);  
			$objPHPExcel->getActiveSheet(0)->setCellValue('B'.($i+2), $ResultsData[$i]['housecode']);  
			$objPHPExcel->getActiveSheet(0)->setCellValue('C'.($i+2), $ResultsData[$i]['username']); 
			$objPHPExcel->getActiveSheet(0)->setCellValue('D'.($i+2), $cardnumber);   
			$objPHPExcel->getActiveSheet(0)->setCellValue('E'.($i+2), $ResultsData[$i]['area']); 
			$objPHPExcel->getActiveSheet(0)->setCellValue('F'.($i+2), $ResultsData[$i]['phone']);  
			$objPHPExcel->getActiveSheet(0)->setCellValue('G'.($i+2), $idcard);  
			$objPHPExcel->getActiveSheet(0)->setCellValue('H'.($i+2), $ResultsData[$i]['code']);  
			$objPHPExcel->getActiveSheet(0)->setCellValue('I'.($i+2), date('Y-m-d H:i:s',$ResultsData[$i]['createtime']));  //时间戳转换
        }  
        // sheet命名  
        $objPHPExcel->getActiveSheet()->setTitle('2015-2016供热优惠申请');  
  
        //设置第一个页签
        $objPHPExcel->setActiveSheetIndex(0);  
  
        // excel头参数  
        header('Content-Type: application/vnd.ms-excel');  
        header('Content-Disposition: attachment;filename="2015-2016供热优惠申请('.date('Y-m-d').').xls"');  //日期为文件名后缀  
        header('Cache-Control: max-age=0');  
  
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  //excel5为xls格式，excel2007为xlsx格式  
        $objWriter->save('php://output'); 
	}


}

?>
