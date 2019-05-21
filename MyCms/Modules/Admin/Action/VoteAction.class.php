<?php

class VoteAction extends AdminAction {

    //投票主页
    public function index() {

      
        //栏目类别
        $cate = D('Cate')->order('sort Asc')->relation(true)->select();
        $this->list = get_cate($cate, '&nbsp;&nbsp;├&nbsp;&nbsp;');
        
        $m_vote = D('Vote');
    

        //栏目查询
        if (is_numeric($_GET['cat_id'])) {
            $c = get_childsid($cate, $_GET['cat_id']);
            $cids = $_GET['cat_id'];
            foreach ($c as $v) {
                $cids.="," . $v;
            }
            $map['cat_id'] = array('in', $cids);
            $index['cat_id'] = $_GET['cat_id'];
        }
        //每页条数
        if (!empty($_GET['limit'])) {
            $limit = $_GET['limit'];
            $index['limit'] = $_GET['limit'];
        } else {
            $limit = C('PAGE_SIZE');
            $index['limit'] = C('PAGE_SIZE');
        }
        //状态查询
        if (is_numeric($_GET['status'])) {
            $map['status'] = $_GET['status'];
            $index['status'] = $_GET['status'];
        }
        //关键词
        if (!empty($_GET['keyword'])) {
            $map[$_GET['types']] = array('like', '%' . $_GET['keyword'] . '%');
            $index['types'] = $_GET['types'];
            $index['keyword'] = $_GET['keyword'];
        }
        //文章列表
        import("ORG.Util.Page");
        $count = $m_vote->where($map)->count();
        $page = new Page($count, $limit);
        $votelist = $m_vote->where($map)->limit($page->firstRow . ',' . $page->listRows)->order('id  DESC')->relation(true)->select();

        $this->assign('votelist', $votelist);
        //分页显示及默认页数
         $show = $page->show();
        $this->assign('page', $show); // 赋值分页输出
        $this->assign('p', C('PAGE_SIZE'));
        //输出搜索的条件
        $this->assign($index);

        $this->display();
    }

    public function add() {
        $cate = D('Cate')->order('sort Asc')->relation(true)->select();
        $this->list = get_cate($cate, '&nbsp;&nbsp;├&nbsp;&nbsp;');
        $this->assign('now', time());
        $this->assign('type', 'add');
        $this->display();
    }

    //插入数据库

    public function insert() {
        //投票主题
        $data = D('vote')->create();
        $data['create_time'] = time();
        $data['start_time'] = strtotime($data['start_time']);
        if (!empty($data['end_time'])) {
            $data['end_time'] = strtotime($data['end_time']);
        }
        $data['admin_id'] = $_SESSION['myid'];
        $vote_return = D('vote')->add($data); //主题添加是否成功
        //标题
        $listname = $_REQUEST['listname'];
        $is_mc = $_REQUEST['is_mc'];
        //选项
        $option = $_REQUEST['option'];

        if (!empty($listname) && $vote_return == true) { //如果主题添加成功 >> 添加主题下的所有标题
            $voteid = mysql_insert_id(); // 栏目主题ID
            foreach ($listname as $key => $vo) {
                $save['listname'] = $listname[$key];
                $save['is_mc'] = $is_mc[$key];
                $save['voteid'] = $voteid;
                $save['updatetime'] = time();

                $list_return = D('vote_list')->add($save);

                if ($vote_return == true) {//如果标题添加成功 >> 添加主题下的所有选项
                    $listid = mysql_insert_id();
                    foreach ($option[$key] as $k => $v) {
                        $add['option'] = $option[$key][$k];
                        $add['listid'] = $listid;
                        $add['updatetime'] = time();

                        $option_return = D('vote_option')->add($add);
                        if ($option_return == false) {
                            $this->error('选项添加失败!', '__URL__/index');
                        }
                    }
                } else {
                    $this->error('题目添加失败!', '__URL__/index');
                }
            }
        } else {
            $this->error('主题添加失败，请重试！', '__URL__/add');
        }
        $this->success('成功！', '__URL__/index');
    }

    public function edit() {
        $cate = D('Cate')->order('sort Asc')->relation(true)->select();
        $this->list = get_cate($cate, '&nbsp;&nbsp;├&nbsp;&nbsp;');
        $m_vote = D('vote');
        $m_vote_list = D('vote_list');
        $m_vote_option = D('vote_option');

        $voteid = intval($_GET['id']);

        $vote = $m_vote->where("id = $voteid")->find();
        $votelist = $m_vote_list->where(" voteid = $voteid")->order('listid asc')->select();


        foreach ($votelist as $key => $val) {
            $listid = $votelist[$key]['listid'];
            $option[$listid] = $m_vote_option->where("listid = $listid")->order('id asc')->select();
            $sum[$listid] = $m_vote_option->where("listid = $listid")->sum('poll');
            foreach ($option[$listid] as $k => $v) {
                $width = 200;  //外层DIV宽度
                $option[$listid][$k]['percent'] = round($option[$listid][$k]['poll'] / $sum[$listid] * 100, 2); //所占比例
                $ratio = ($option[$listid][$k]['poll'] / $sum[$listid]) * $width; //所占比例的宽度
                $option[$listid][$k]['ratio'] = $ratio;
            }
        }
        $data = array(
            'vote' => $vote,
            'votelist' => $votelist,
            'option' => $option,
        );

        $this->assign($data);
        //print_r($data);exit;
        $this->assign('type', 'edit');
        $this->display('add');
    }

    //更改
    public function update() {
        $data = D('vote')->create();
        $m_vote_list = D('vote_list');
        $m_vote_option = D('vote_option');
        $id = intval($_POST['id']);

        $data['update_time'] = time();
        $data['start_time'] = strtotime($data['start_time']);
        if (!empty($data['end_time'])) {
            $data['end_time'] = strtotime($data['end_time']);
        }
        $r = D('vote')->save($data);
        $voteid = $id; // 栏目主题ID
        //标题
        $listname = $_REQUEST['listname'];
        $is_mc = $_REQUEST['is_mc'];
        //选项
        $option = $_REQUEST['option'];

        foreach ($listname as $key => $vo) {
            $save['listname'] = $listname[$key];
            $save['is_mc'] = $is_mc[$key];
            $save['voteid'] = $voteid;
            $save['updatetime'] = time();

            $list_return = D('vote_list')->add($save);

            $listid = mysql_insert_id();
            foreach ($option[$key] as $k => $v) {
                $add['option'] = $option[$key][$k];
                $add['listid'] = $listid;
                $add['updatetime'] = time();

                $option_return = D('vote_option')->add($add);
                if ($option_return == false) {
                    $this->error('选项添加失败!', '__URL__/index');
                }
            }
        }

        if ($r == false) {
            $this->error('修改失败，请重试');
        }
        $this->success('修改成功！', '__URL__/index');
    }

	//生态城2014调查结果查询
	public function result() {
       
        //查询条件获取
		$housename = $_POST['housename'];
		$username = $_POST['username'];
		$phone = $_POST['phone'];
		$qishishijian = $_POST['qishishijian'];
		$jieshushijian = $_POST['jieshushijian'];
		$bangding = $_POST['bangding'];
		//小区名称
		if (!empty($_POST['housename'])) {
			 $data['housename'] = array('like', '%' . $_POST['housename'] . '%');
			 $index['housename'] = $housename;
        }
		//联系电话
		if (!empty($_POST['phone'])) {
             $data['phone'] = array('like', '%' . $_POST['phone'] . '%');
			 $index['phone'] = $phone;
        }
		//业主名称 
		if (!empty($_POST['username'])) {
             $data['username'] = array('like', '%' . $_POST['username'] . '%');
			 $index['username'] = $username;
        }
		//投票时间
		if ((!empty($_POST['qishishijian'])) && (!empty($_POST['jieshushijian']))) {
			 $data['updatetime'] = array(between,array(strtotime($qishishijian),strtotime($jieshushijian)));
			 $index['qishishijian'] = $qishishijian;
			 $index['jieshushijian'] = $jieshushijian;
        }
		if((!empty($_POST['qishishijian'])) && (empty($_POST['jieshushijian']))){
			$data['updatetime'] = array(between,array(strtotime($qishishijian),time()));
			$index['qishishijian'] = $qishishijian;
		}
		if((empty($_POST['qishishijian'])) && (!empty($_POST['jieshushijian']))){
			$data['updatetime'] = array(between,array(strtotime("28 December 2014"),strtotime($jieshushijian)));
			$index['jieshushijian'] = $jieshushijian;
		}
		//查询调查结果
		if($bangding == 1){
			$result_list = D('Survey')->where($data)->select();
		}
		else{
			$result_list = 0;
		}
		//dump($data);
		//dump(D('Survey')->getLastSql());
		
		//平台信息查询
		$index['bangding'] = $bangding;
		
		$this->assign('result_list', $result_list);

		$this->assign($index);

        $this->display();
    }
	
	//生态城2014调查结果查询详细信息
	 public function infor() {
        $id = intval($this->_param('id'));
		$i = 1;
        if ($id <= 0) {
            $this->error('参数错误！');
        } else {
			$list = D('Survey')->where('id=' . $id)->find();
			//查询用户已绑定房间
			$this->assign('list', $list);
			$this->assign('id', $id);
            $this->display();
        }
    }

	//调查结果导出

	/*public function exportexcel(){
		header("Content-type:application/octet-stream");
		header("Accept-Ranges:bytes");
		header("Content-type:application/vnd.ms-excel"); 
		header("Content-Disposition:attachment;filename=生态城2014年物业满意度调查问卷统计表".date("Y-m-d").".xls");
		header("Pragma: no-cache");
		header("Expires: 0");
		//导出xls 开始
		$tag0 = iconv("UTF-8", "GB2312",'业主名称');
		$tag1 = iconv("UTF-8", "GB2312",'房间地址');
		$tag2 = iconv("UTF-8", "GB2312",'联系电话');
		$tag3 = iconv("UTF-8", "GB2312",'房屋状态');
		$tag4 = iconv("UTF-8", "GB2312",'投票时间');
		$tag5 = iconv("UTF-8", "GB2312",'综合服务表现');
		$tag6 = iconv("UTF-8", "GB2312",'环境清洁服务');
		$tag7 = iconv("UTF-8", "GB2312",'绿化景观服务');
		$tag8 = iconv("UTF-8", "GB2312",'治安安全管理');
		$tag9 = iconv("UTF-8", "GB2312",'停车场及车辆管理');
		$tag10 = iconv("UTF-8", "GB2312",'电梯公共照明服务');
		$tag11 = iconv("UTF-8", "GB2312",'社区文化活动');
		echo "$tag0\t$tag1\t$tag2\t$tag3\t$tag4\t$tag5\t$tag6\t$tag7\t$tag8\t$tag9\t$tag10\t$tag11\n";
		//查询数据
		$map['housename'] != '';
		$arr = D('Survey')->where($map)->select();
		//dump(M ('textpage')->getLastSql());die; 
		foreach($arr as $key=>$val){
		  if(!empty($val['firchoose'])){
			//$date = date('Y-m-d',$val['pay_time']);
			$username = iconv("UTF-8", "GB2312", $val['username']);
			$username=$username?$username:'';
			$roomname = iconv("UTF-8", "GB2312", $val['roomname']);
			$roomname=$roomname?$roomname:'';
			$phone = iconv("UTF-8", "GB2312", $val['phone']);
			$phone=$phone?$phone:'';
			$status = iconv("UTF-8", "GB2312", $val['status']);
			$status=$status?$status:'';
			$updatetime = date('Y-m-d H:i:s',iconv("UTF-8", "GB2312", $val['updatetime']));
			$updatetime=$updatetime?$updatetime:'';
			$firchoose = iconv("UTF-8", "GB2312", $val['firchoose']);
			$firchoose=$firchoose?$firchoose:'';
			$secchoose = iconv("UTF-8", "GB2312", $val['secchoose']);
			$secchoose=$secchoose?$secchoose:'';
			$thichoose = iconv("UTF-8", "GB2312", $val['thichoose']);
			$thichoose=$thichoose?$thichoose:'';
			$fouchoose = iconv("UTF-8", "GB2312", $val['fouchoose']);
			$fouchoose=$fouchoose?$fouchoose:'';
			$fifchoose = iconv("UTF-8", "GB2312", $val['fifchoose']);
			$fifchoose=$fifchoose?$fifchoose:'';
			$sixchoose = iconv("UTF-8", "GB2312", $val['sixchoose']);
			$sixchoose=$sixchoose?$sixchoose:'';
			$sevchoose = iconv("UTF-8", "GB2312", $val['sevchoose']);
			$sevchoose=$sevchoose?$sevchoose:'';
	echo "$username\t$roomname\t$phone\t$status\t$updatetime\t$firchoose\t$secchoose\t$thichoose\t$fouchoose\t$fifchoose\t$sixchoose\t$sevchoose\n";
		  }	
		}
	}*/
	public function exportexcel(){
		$con['firchoose']=array('NEQ','');
        $ResultsData = D('Survey')->where($con)->select();  //查询数据得到$ResultsData二维数组  
        vendor("PHPExcel.PHPExcel");  
        // 建立PHPExcel对象  
        $objPHPExcel = new PHPExcel();  
        //设置宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(40);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);  
		$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);  
		$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(20);  
		$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(200);
  
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
            ->setCellValue('A1', '业主名称')  
            ->setCellValue('B1', '房间地址')  
            ->setCellValue('C1', '联系电话')  
            ->setCellValue('D1', '房屋状态')  
            ->setCellValue('E1', '投票时间')  
            ->setCellValue('F1', '综合服务表现')  
            ->setCellValue('G1', '环境清洁服务')  
            ->setCellValue('H1', '绿化景观服务')  
            ->setCellValue('I1', '治安安全管理')  
            ->setCellValue('J1', '停车场及车辆管理')  
            ->setCellValue('K1', '电梯公共照明服务')
			->setCellValue('L1', '社区文化活动')
			->setCellValue('M1', '留言'); 
  
        // 填充数据(UTF-8)  
        for($i=0;$i<count($ResultsData);$i++){  
			if(!empty($ResultsData[$i]['firchoose'])){
				$objPHPExcel->getActiveSheet(0)->setCellValue('A'.($i+2), $ResultsData[$i]['username']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('B'.($i+2), $ResultsData[$i]['roomname']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('C'.($i+2), $ResultsData[$i]['phone']); 
				$objPHPExcel->getActiveSheet(0)->setCellValue('D'.($i+2), $ResultsData[$i]['status']);   
				$objPHPExcel->getActiveSheet(0)->setCellValue('E'.($i+2), date('Y-m-d H:i:s',$ResultsData[$i]['updatetime'])); //时间戳转换  
				$objPHPExcel->getActiveSheet(0)->setCellValue('F'.($i+2), $ResultsData[$i]['firchoose']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('G'.($i+2), $ResultsData[$i]['secchoose']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('H'.($i+2), $ResultsData[$i]['thichoose']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('I'.($i+2), $ResultsData[$i]['fouchoose']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('J'.($i+2), $ResultsData[$i]['fifchoose']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('K'.($i+2), $ResultsData[$i]['sixchoose']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('L'.($i+2), $ResultsData[$i]['sevchoose']);
				$objPHPExcel->getActiveSheet(0)->setCellValue('M'.($i+2), $ResultsData[$i]['content']);
			}
        }  
  
  
        // sheet命名  
        $objPHPExcel->getActiveSheet()->setTitle('生态城2014年物业满意度调查问卷统计表');  
  
  
        //设置第一个页签
        $objPHPExcel->setActiveSheetIndex(0);  
  
        // excel头参数  
        header('Content-Type: application/vnd.ms-excel');  
        header('Content-Disposition: attachment;filename="生态城2014年物业满意度调查问卷统计表('.date('Y-m-d').').xls"');  //日期为文件名后缀  
        header('Cache-Control: max-age=0');  
  
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  //excel5为xls格式，excel2007为xlsx格式  
        $objWriter->save('php://output'); 
	}
	public function exportexcel2(){
        $ResultsData = D('vote_service')->select();  //查询数据得到$ResultsData二维数组  
        vendor("PHPExcel.PHPExcel");  
        // 建立PHPExcel对象  
        $objPHPExcel = new PHPExcel();  
        //设置宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(50);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(50);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(50);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(50);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(50);  
		$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(50);  
		$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(50);  
		$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(50);
		$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(50);
		$objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(100);
		$objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(100);
		$objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(50);
		$objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(500);
		$objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(50);
		$objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(50);
  
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
		$objPHPExcel->getActiveSheet()->getStyle('N')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
		$objPHPExcel->getActiveSheet()->getStyle('O')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->getStyle('P')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->getStyle('Q')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
		$objPHPExcel->getActiveSheet()->getStyle('R')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->getStyle('S')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->getStyle('T')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
  
        // 设置首行文字  
        $objPHPExcel->setActiveSheetIndex(0)  
            ->setCellValue('A1', '业主姓名')  
            ->setCellValue('B1', '房间编号')  
            ->setCellValue('C1', '联系电话')  
            ->setCellValue('D1', '居住小区')  
            ->setCellValue('E1', '投票时间')  
            ->setCellValue('F1', '您对物业工作人员的行为规范、服务热情是否满意？')  
            ->setCellValue('G1', '您对本小区物业报事处置响应是否满意？')  
            ->setCellValue('H1', '保安巡逻值勤情况是否满意？')  
            ->setCellValue('I1', '您对小区机动车辆、非机动车管理是否满意？')  
            ->setCellValue('J1', '您对小区景观、绿化养护服务是否满意？')
			->setCellValue('K1', '您对小区楼内走楼、电梯等公共部位清洁度是否满意？')
			->setCellValue('L1', '公共照明维护管理是否满意？')
			->setCellValue('M1', '电梯运行情况是否满意？')
			->setCellValue('N1', '您对本小区门禁、消防设施等公共设施设备维护程度是否满意？')
			->setCellValue('O1', '您认为本小区主要问题有哪些？')
			->setCellValue('P1', '您认为小区物业服务应重点改进的地方？')
			->setCellValue('Q1', '您认为生态城内物业服务整体水平如何？')
			->setCellValue('R1', '您对目前小区物业工作有何其他方面的建议和意见？')
			->setCellValue('S1', '您认为本小区主要问题有哪些？（选项）')
			->setCellValue('T1', '您认为小区物业服务应重点改进的地方？（选项）'); 
  
        // 填充数据(UTF-8)  
        for($i=0;$i<count($ResultsData);$i++){  
			
				$objPHPExcel->getActiveSheet(0)->setCellValue('A'.($i+2), $ResultsData[$i]['username']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('B'.($i+2), $ResultsData[$i]['housecode']); 
				$objPHPExcel->getActiveSheet(0)->setCellValue('C'.($i+2), $ResultsData[$i]['phone']);   
				$objPHPExcel->getActiveSheet(0)->setCellValue('D'.($i+2), $ResultsData[$i]['village']);
				$objPHPExcel->getActiveSheet(0)->setCellValue('E'.($i+2), date('Y-m-d H:i:s',$ResultsData[$i]['vote_time']));  
				$objPHPExcel->getActiveSheet(0)->setCellValue('F'.($i+2), $ResultsData[$i]['firchoose']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('G'.($i+2), $ResultsData[$i]['secchoose']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('H'.($i+2), $ResultsData[$i]['thichoose']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('I'.($i+2), $ResultsData[$i]['fourchoose']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('J'.($i+2), $ResultsData[$i]['fifchoose']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('K'.($i+2), $ResultsData[$i]['sixchoose']);
				$objPHPExcel->getActiveSheet(0)->setCellValue('L'.($i+2), $ResultsData[$i]['sevchoose']);
				$objPHPExcel->getActiveSheet(0)->setCellValue('M'.($i+2), $ResultsData[$i]['eightchoose']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('N'.($i+2), $ResultsData[$i]['ninchoose']);
				$objPHPExcel->getActiveSheet(0)->setCellValue('O'.($i+2), $ResultsData[$i]['zxfirchoose']);
				$objPHPExcel->getActiveSheet(0)->setCellValue('P'.($i+2), $ResultsData[$i]['zxsecchoose']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('Q'.($i+2), $ResultsData[$i]['zxthichoose']);
				$objPHPExcel->getActiveSheet(0)->setCellValue('R'.($i+2), $ResultsData[$i]['zxfourchoose']);
				$objPHPExcel->getActiveSheet(0)->setCellValue('S'.($i+2), $ResultsData[$i]['vote1']);
				$objPHPExcel->getActiveSheet(0)->setCellValue('T'.($i+2), $ResultsData[$i]['vote2']);  
			
        }  
  
  
        // sheet命名  
        $objPHPExcel->getActiveSheet()->setTitle('住宅小区物业满意度调查问卷');  
  
  
        //设置第一个页签
        $objPHPExcel->setActiveSheetIndex(0);  
  
        // excel头参数  
        header('Content-Type: application/vnd.ms-excel');  
        header('Content-Disposition: attachment;filename="住宅小区物业满意度调查问卷('.date('Y-m-d').').xls"');  //日期为文件名后缀  
        header('Cache-Control: max-age=0');  
  
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  //excel5为xls格式，excel2007为xlsx格式  
        $objWriter->save('php://output'); 
	}
//登录用户数据查询
	public function userlogin() {
		//每页条数
		if (!empty($_GET['limit'])) {
			$limit = $_GET['limit'];
			$index['limit'] = $_GET['limit'];
		} else {
			$limit = C('PAGE_SIZE');
			$index['limit'] = C('PAGE_SIZE');
		}
		
		//查询调查结果
		//文章列表
		import("ORG.Util.Page");
		$count = D('User')->where('login_state=1')->count();
		$page = new Page($count, $limit);
		$result_list = D('User')->where('login_state=1')->limit($page->firstRow . ',' . $page->listRows)->order('id DESC')->select();
	
		//分页显示及默认页数
		$show = $page->show();
		$this->assign('page', $show); // 赋值分页输出
		$this->assign('p', C('PAGE_SIZE'));
		//平台信息查询
		$index['bangding'] = $bangding;
		$this->assign('result_list', $result_list);
         $this->assign('count', $count);
		$this->assign($index);

        $this->display();
    }
	//公交车满意度调查结果查询
	public function survey() {
		//每页条数
		if (!empty($_GET['limit'])) {
			$limit = $_GET['limit'];
			$index['limit'] = $_GET['limit'];
		} else {
			$limit = C('PAGE_SIZE');
			$index['limit'] = C('PAGE_SIZE');
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

		//查询调查结果
		//文章列表
		import("ORG.Util.Page");
		$count = D('Survey_bus')->where($data)->count();
		$page = new Page($count, $limit);
		$result_list = D('Survey_bus')->where($data)->limit($page->firstRow . ',' . $page->listRows)->order('id DESC')->select();
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
	
	//公交车满意度调查结果详细信息
	 public function view() {
        $id = intval($this->_param('id'));
		$i = 1;
        if ($id <= 0) {
            $this->error('参数错误！');
        } else {
			$list = D('Survey_bus')->where('id=' . $id)->find();
			//查询用户已绑定房间
			$this->assign('list', $list);
			$this->assign('id', $id);
            $this->display();
        }
    }
	
	//公交车满意度调查结果导出
	public function exportresult(){
		$con['firchoose']=array('NEQ','');
		$qishishijian = $_POST['qishishijian'];
		$jieshushijian = $_POST['jieshushijian'];
		//查询条件
		if ((!empty($qishishijian)) && (!empty($jieshushijian))) {
			 $qishishijian = $qishishijian."00:00:00";
			 $jieshushijian = $jieshushijian."23:59:59";
			 $con['createtime'] = array(between,array(strtotime($qishishijian),strtotime($jieshushijian)));
        }
		if((!empty($qishishijian)) && (empty($jieshushijian))){
			$qishishijian = $qishishijian."00:00:00";
			$con['createtime'] = array(between,array(strtotime($qishishijian),time()));
		}
		if((empty($qishishijian)) && (!empty($jieshushijian))){
			$jieshushijian = $jieshushijian."23:59:59";
			$con['createtime'] = array(between,array(strtotime("23 March 2015"),strtotime($jieshushijian)));
		}
        $ResultsData = D('Survey_bus')->where($con)->select();  //查询数据得到$ResultsData二维数组  
        vendor("PHPExcel.PHPExcel");  
        // 建立PHPExcel对象  
        $objPHPExcel = new PHPExcel();  
        //设置宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(40);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(50);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(70);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(70);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(70);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(90);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);  
		$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);  
		$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(20);  
		$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(200);
  
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
            ->setCellValue('A1', '年龄范围')  
            ->setCellValue('B1', '所属情况')  
            ->setCellValue('C1', '出行情况')  
            ->setCellValue('D1', '出行起始点')  
            ->setCellValue('E1', '乘坐公交时间段')  
            ->setCellValue('F1', '满意度评价')  
            ->setCellValue('G1', '存在问题')  
            ->setCellValue('H1', '生态城外出行方式')  
            ->setCellValue('I1', '投诉解决情况存在问题')  
            ->setCellValue('J1', '生态城内使用私家车次数')  
            ->setCellValue('K1', '生态城外使用私家车次数')
			->setCellValue('L1', '投票时间')
			->setCellValue('M1', '留言'); 
  
        // 填充数据(UTF-8)  
        for($i=0;$i<count($ResultsData);$i++){  
			if(!empty($ResultsData[$i]['firchoose'])){
				$place = $ResultsData[$i]['startplace'] . "—" . $ResultsData[$i]['endplace'];
				$objPHPExcel->getActiveSheet(0)->setCellValue('A'.($i+2), $ResultsData[$i]['firchoose']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('B'.($i+2), $ResultsData[$i]['secchoose']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('C'.($i+2), $ResultsData[$i]['thichoose']); 
				$objPHPExcel->getActiveSheet(0)->setCellValue('D'.($i+2), $place);   
				$objPHPExcel->getActiveSheet(0)->setCellValue('E'.($i+2), $ResultsData[$i]['fifchoose']); 
				$objPHPExcel->getActiveSheet(0)->setCellValue('F'.($i+2), $ResultsData[$i]['sixchoose']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('G'.($i+2), $ResultsData[$i]['sevchoose']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('H'.($i+2), $ResultsData[$i]['eightchoose']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('I'.($i+2), $ResultsData[$i]['ninchoose']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('J'.($i+2), $ResultsData[$i]['cartimes']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('K'.($i+2), $ResultsData[$i]['cartime']);    
				$objPHPExcel->getActiveSheet(0)->setCellValue('L'.($i+2), date('Y-m-d H:i:s',$ResultsData[$i]['createtime']));//时间戳转换
				$objPHPExcel->getActiveSheet(0)->setCellValue('M'.($i+2), $ResultsData[$i]['content']);
			}
        }  
  
  
        // sheet命名  
        $objPHPExcel->getActiveSheet()->setTitle('公交车满意度调查问卷统计表');  
  
        //设置第一个页签
        $objPHPExcel->setActiveSheetIndex(0);  
  
        // excel头参数  
        header('Content-Type: application/vnd.ms-excel');  
        header('Content-Disposition: attachment;filename="公交车满意度调查问卷统计表('.date('Y-m-d').').xls"');  //日期为文件名后缀  
        header('Cache-Control: max-age=0');  
  
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  //excel5为xls格式，excel2007为xlsx格式  
        $objWriter->save('php://output'); 
	}


	//公用事业服务热线66885890问卷调查
	public function custom() {
		//每页条数
		if (!empty($_GET['limit'])) {
			$limit = $_GET['limit'];
			$index['limit'] = $_GET['limit'];
		} else {
			$limit = C('PAGE_SIZE');
			$index['limit'] = C('PAGE_SIZE');
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

		//查询调查结果
		//文章列表
		import("ORG.Util.Page");
		$data['year'] = date('Y');
		$count = D('Vote_custom')->where($data)->count();
		$page = new Page($count, $limit);
		$result_list = D('Vote_custom')->where($data)->limit($page->firstRow . ',' . $page->listRows)->order('id DESC')->select();
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

	//公用事业服务热线66885890问卷调查详细信息
	 public function custom_view() {
        $id = intval($this->_param('id'));
		$i = 1;
        if ($id <= 0) {
            $this->error('参数错误！');
        } else {
			$list = D('Vote_custom')->where('id=' . $id)->find();
			//查询用户已绑定房间
			$this->assign('list', $list);
			$this->assign('id', $id);
            $this->display();
        }
    }

	//公用事业服务热线66885890问卷调查结果导出
	public function exportcustom(){
		$con['year'] = date('Y');
		$con['firchoose']=array('NEQ','');
        $ResultsData = D('Vote_custom')->where($con)->select();  //查询数据得到$ResultsData二维数组  
        vendor("PHPExcel.PHPExcel");  
        // 建立PHPExcel对象  
        $objPHPExcel = new PHPExcel();  
        //设置宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(50);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(50);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);  
		$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(40);
		$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(40);
		$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(40);
		$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(200);      
		
  
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
		$objPHPExcel->getActiveSheet()->getStyle('N')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->getStyle('O')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->getStyle('P')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		
        // 设置首行文字  
        $objPHPExcel->setActiveSheetIndex(0)  
            ->setCellValue('A1', '用户姓名')  
            ->setCellValue('B1', '联系电话')  
            ->setCellValue('C1', '居住小区')  
            ->setCellValue('D1', '是否拨打服务热线')  
            ->setCellValue('E1', '未曾使用原因')  
            ->setCellValue('F1', '服务态度是否满意')  
            ->setCellValue('G1', '不满意原因')  
            ->setCellValue('H1', '诉求处理是否满意')  
            ->setCellValue('I1', '领会诉求是否满意')  
            ->setCellValue('J1', '全面性是否满意')  
            ->setCellValue('K1', '是否登录过66885890客服平台')
			->setCellValue('L1', '是否通过66885890.com或5890 APP办理过相关业务')
			->setCellValue('M1', '最常用哪种方式办理')
			->setCellValue('N1', '最常用的缴费方式')
			->setCellValue('O1', '投票时间')
			->setCellValue('P1', '留言'); 
  
        // 填充数据(UTF-8)  
        for($i=0;$i<count($ResultsData);$i++){  
			if(!empty($ResultsData[$i]['summary2'])){
				$secchoose = $ResultsData[$i]['secchoose'].'('.$ResultsData[$i]['summary2'].')';
			}
			else{
				$secchoose = $ResultsData[$i]['secchoose'];
			}
			if(!empty($ResultsData[$i]['summary3'])){
				$fourchoose = $ResultsData[$i]['fourchoose'].'('.$ResultsData[$i]['summary3'].')';
			}
			else{
				$fourchoose = $ResultsData[$i]['fourchoose'];
			}
			if(!empty($ResultsData[$i]['firchoose'])){
				$objPHPExcel->getActiveSheet(0)->setCellValue('A'.($i+2), $ResultsData[$i]['username']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('B'.($i+2), $ResultsData[$i]['phone']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('C'.($i+2), $ResultsData[$i]['address']); 
				$objPHPExcel->getActiveSheet(0)->setCellValue('D'.($i+2), $ResultsData[$i]['firchoose']);   
				$objPHPExcel->getActiveSheet(0)->setCellValue('E'.($i+2), $secchoose); 
				$objPHPExcel->getActiveSheet(0)->setCellValue('F'.($i+2), $ResultsData[$i]['thichoose']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('G'.($i+2), $fourchoose);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('H'.($i+2), $ResultsData[$i]['fifchoose']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('I'.($i+2), $ResultsData[$i]['sixchoose']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('J'.($i+2), $ResultsData[$i]['sevchoose']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('K'.($i+2), $ResultsData[$i]['eightchoose']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('L'.($i+2), $ResultsData[$i]['ninchoose']);
				$objPHPExcel->getActiveSheet(0)->setCellValue('M'.($i+2), $ResultsData[$i]['tenchoose']);
				$objPHPExcel->getActiveSheet(0)->setCellValue('N'.($i+2), $ResultsData[$i]['elechoose']);
				$objPHPExcel->getActiveSheet(0)->setCellValue('O'.($i+2), date('Y-m-d H:i:s',$ResultsData[$i]['createtime']));
				$objPHPExcel->getActiveSheet(0)->setCellValue('P'.($i+2), $ResultsData[$i]['content']);  
			}
        }  
  
  
        // sheet命名  
        $objPHPExcel->getActiveSheet()->setTitle('公用事业服务热线66885890调查问卷统计表');  
  
        //设置第一个页签
        $objPHPExcel->setActiveSheetIndex(0);  
  
        // excel头参数  
        header('Content-Type: application/vnd.ms-excel');  
        header('Content-Disposition: attachment;filename="公用事业服务热线66885890调查问卷统计表('.date('Y-m-d').').xls"');  //日期为文件名后缀  
        header('Cache-Control: max-age=0');  
  
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  //excel5为xls格式，excel2007为xlsx格式  
        $objWriter->save('php://output'); 
	}
	
	
	//能源公司用户满意度调查结果查询
	public function energy() {
		//每页条数
		if (!empty($_GET['limit'])) {
			$limit = $_GET['limit'];
			$index['limit'] = $_GET['limit'];
		} else {
			$limit = C('PAGE_SIZE');
			$index['limit'] = C('PAGE_SIZE');
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

		//查询调查结果
		//文章列表
		import("ORG.Util.Page");
		$count = D('Survey_energy')->where($data)->count();
		$page = new Page($count, $limit);
		$result_list = D('Survey_energy')->where($data)->limit($page->firstRow . ',' . $page->listRows)->order('id DESC')->select();
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

	//2017能源公司用户满意度调查结果查询
	public function energy1() {
		//每页条数
		if (!empty($_GET['limit'])) {
			$limit = $_GET['limit'];
			$index['limit'] = $_GET['limit'];
		} else {
			$limit = C('PAGE_SIZE');
			$index['limit'] = C('PAGE_SIZE');
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

		//查询调查结果
		//文章列表
		import("ORG.Util.Page");
		$count = D('Survey_energy1')->where($data)->count();
		$page = new Page($count, $limit);
		$result_list = D('Survey_energy1')->where($data)->limit($page->firstRow . ',' . $page->listRows)->order('id DESC')->select();
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

	//能源公司计量管理
	public function meter() {
		$housecode=$_POST['housecode'];
		//var_dump($_POST);
		//每页条数
		if (!empty($_GET['limit'])) {
			$limit = $_GET['limit'];
			$index['limit'] = $_GET['limit'];
		} else {
			$limit = C('PAGE_SIZE');
			$index['limit'] = C('PAGE_SIZE');
		}
		//开始时间
        if (!empty($_GET['sj'])) {
			$sj = $_GET['sj'];
            $index['sj'] = $_GET['sj'];
        }
		if (!empty($_POST['sj'])) {
			$sj = $_POST['sj'];
            $index['sj'] = $_POST['sj'];
        }
		
		//查询条件
		if ((!empty($sj))) {
			 
			 $data['operateTime'] = $sj;
        }
		if ((!empty($housecode))) {
			 
			 $data['housecode'] = $housecode;
        }
		

		//查询调查结果
		//文章列表
		import("ORG.Util.Page");
		$count = D('Energy_meter')->where($data)->count();
		$page = new Page($count, $limit);
		$result_list = D('Energy_meter')->where($data)->limit($page->firstRow . ',' . $page->listRows)->order('id DESC')->select();

		//分页显示及默认页数
		$show = $page->show();
		$this->assign('page', $show); // 赋值分页输出
        //var_dump($show);
		$this->assign('p', 15);
		//平台信息查询
		$index['bangding'] = $bangding;
		$this->assign('result_list', $result_list);
        $string= $data['operateTime'];
		//var_dump($result_list);
      $this->assign('test',$string);
		$this->assign($index);

        $this->display();
    }

	//能源公司计量管理
	public function meter1() {
		
		//文章列表
		$result_list = D('Energy_meter_view')->order("meterTime desc")->select();
		//dump(D('Energy_meter_view')->getLastSql());
		//平台信息查询
		$this->assign('result_list', $result_list);
        $this->display();
    }
	

	/**
   * 上传Excel文件
   */
  public function upload() {
    //引入ThinkPHP上传文件类
    import('ORG.Net.UploadFile');
	//计量时间
	$metetTime = $_POST['year'].$_POST['month'];
    //实例化上传类
    $upload = new UploadFile();
    //设置附件上传文件大小200Kib
    //$upload->mixSize = 2000000;
    //设置附件上传类型
    $upload->allowExts = array('xls', 'xlsx');
    //设置附件上传目录在/Home/temp下
    $upload->savePath = './file/';
    //保持上传文件名不变
    $upload->saveRule = '';
    //存在同名文件是否是覆盖
    $upload->uploadReplace = false;
    if (!$upload->upload()) {  //如果上传失败,提示错误信息
      $this->error($upload->getErrorMsg());
    } else {  //上传成功
      //获取上传文件信息
      $info = $upload->getUploadFileInfo();

      //获取上传保存文件名
      $fileName = $info[0]['savename'];
      //重定向,把$fileName文件名传给importExcel()方法
      $this->redirect('Vote/importExcel', array('fileName' => $fileName,'metetTime' => $metetTime), 1, '正在导入数据，请耐心等待！');
    }
  }

  /**
   *
   * 导入Excel文件
   */
  public function importExcel() {
    //引入PHPExcel类
    vendor('PHPExcel.PHPExcel');
    vendor('PHPExcel.PHPExcel.IOFactory');
    vendor('PHPExcel.PHPExcel.Reader.Excel5');
	$i = 0;
	$sj = '';
	$pd= 1;
	$shijian=date('Y-m-d H:i:s');
    //redirect传来的文件名
    $fileName = $_GET['fileName'];
	//计量时间
	$metetTime = $_GET['metetTime'];
    
    //文件路径
   $filePath = './file/' . $fileName;
    //实例化PHPExcel类
    $PHPExcel = new PHPExcel();
    //默认用excel2007读取excel，若格式不对，则用之前的版本进行读取
    $PHPReader = new PHPExcel_Reader_Excel2007();

    if (!$PHPReader->canRead($filePath)) {
      $PHPReader = new PHPExcel_Reader_Excel5();
      if (!$PHPReader->canRead($filePath)) {
        $this->error("未找到文件！");
        return;
      }
    }

    //读取Excel文件
     $PHPExcel = $PHPReader->load($filePath);
    //读取excel文件中的第一个工作表
    $sheet = $PHPExcel->getSheet(0);

    //取得最大的列号
    $allColumn = $sheet->getHighestColumn();
    //取得最大的行号
    $allRow = $sheet->getHighestRow();
	 $m = M('Energy_meter');
	   $m->startTrans();//建立事务
    //从第二行开始插入,第一行是列名
	 
	//$m = new Model();
	// $m->startTrans();//建立事务
    for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {

	  //获取A列的值
      $metetTime = $PHPExcel->getActiveSheet()->getCell("A" . $currentRow)->getValue();
	
      //获取B列的值
     $housecode = $PHPExcel->getActiveSheet()->getCell("B" . $currentRow)->getValue();
      //获取C列的值
      $account = $PHPExcel->getActiveSheet()->getCell("C" . $currentRow)->getValue();
      //获取D列的值
      $startNum = $PHPExcel->getActiveSheet()->getCell("D" . $currentRow)->getValue();
	  //获取E列的值
      $nowNum = $PHPExcel->getActiveSheet()->getCell("E" . $currentRow)->getValue();
	  //获取F列的值
      $allTotal = $PHPExcel->getActiveSheet()->getCell("F" . $currentRow)->getValue();
	  //获取G列的值
      $meterAccount = $PHPExcel->getActiveSheet()->getCell("G" . $currentRow)->getValue();
	  //获取H列的值
      $percent = $PHPExcel->getActiveSheet()->getCell("H" . $currentRow)->getValue();
	  //获取I列的值
      $percent_1 = $PHPExcel->getActiveSheet()->getCell("I" . $currentRow)->getValue();
  
     
	 // echo $i.'-------'.$metetTime;

	  

	  if($metetTime!=''){
		  //$num = $m->add(array( 'metetTime' => $metetTime,'housecode' => $housecode, 'account' => $account, 'startNum' => $startNum, 'nowNum' => $nowNum, 'allTotal' => $allTotal, 'meterAccount' => $meterAccount, 'percent' => //$percent,'percent_1' => $percent_1,'operateTime'=> date('Y-m-d H:i:s')));
		 
		  $num =  $m->execute('INSERT INTO ad_energy_meter'  
			  . '(metetTime,housecode,account,startNum,nowNum,allTotal,meterAccount,percent,percent_1,operateTime) VALUES 
		  ("'.$metetTime.'","'.$housecode.'","'.$account.'","'.$startNum.'","'.$nowNum.'","'.$allTotal.'","'.$meterAccount.'","'.$percent.'","'.$percent_1.'","'.$shijian.'")');

		 // echo  $num;
		 //dump(D('Energy_meter')->getLastSql());
//echo  '<br>';
		  $i++;
		  $sj = $metetTime;
		 
		   if($num=="")
		  {

			   $pd=0;
		  }
	  }
	

    }


 if($pd==1)
	  {$m->commit();
	  }
	  else
	  {$m->rollback();

	  }  


      if($pd==1){

	  $e = M('Energy_meter_view');
	  
      $e->add(array('meterTime' => $metetTime,'houseNum' => $i, 'uploadTime' => $shijian));
	 // dump(D('Energy_meter_view')->getLastSql());

	  $this->error("添加成功！");
    } else {
	  $this->error("添加失败！");
   }

  }
	
	//能源公司用户满意度调查结果详细信息
	 public function with() {
        $id = intval($this->_param('id'));
		$i = 1;
        if ($id <= 0) {
            $this->error('参数错误！');
        } else {
			$list = D('Survey_energy')->where('id=' . $id)->find();
			//查询用户已绑定房间
			$this->assign('list', $list);
			$this->assign('id', $id);
            $this->display();
        }
    }

	//2018年生态城公用事业满意度调查问卷页面
	public function cause() {
		//每页条数
		if (!empty($_GET['limit'])) {
			$limit = $_GET['limit'];
			$index['limit'] = $_GET['limit'];
		} else {
			$limit = C('PAGE_SIZE');
			$index['limit'] = C('PAGE_SIZE');
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

		//查询调查结果
		//显示列表
		import("ORG.Util.Page");
		$count = D('vote_cause')->where($data)->count();
		$page = new Page($count, $limit);
		$result_list = D('vote_cause')->where($data)->limit($page->firstRow . ',' . $page->listRows)->order('id DESC')->select();
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

	//2017能源公司用户满意度调查结果详细信息
	 public function with1() {
        $id = intval($this->_param('id'));
		$i = 1;
        if ($id <= 0) {
            $this->error('参数错误！');
        } else {
			$list = D('Survey_energy1')->where('id=' . $id)->find();
			//查询用户已绑定房间
			$this->assign('list', $list);
			$this->assign('id', $id);
            $this->display();
        }
    }
	
	//能源公司用户满意度调查结果导出
	public function exportenergy(){
		$con['firchoose']=array('NEQ','');
        $ResultsData = D('Survey_energy')->where($con)->select();  //查询数据得到$ResultsData二维数组  
        vendor("PHPExcel.PHPExcel");  
        // 建立PHPExcel对象  
        $objPHPExcel = new PHPExcel();  
        //设置宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(50);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);  
		$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(20);  
		$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(80);
		$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(50);      
		$objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(50);  
		$objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(200);
  
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
		$objPHPExcel->getActiveSheet()->getStyle('N')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->getStyle('O')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->getStyle('P')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
  
        // 设置首行文字  
        $objPHPExcel->setActiveSheetIndex(0)  
            ->setCellValue('A1', '供热收费方式')  
            ->setCellValue('B1', '服务工作总体评价')  
            ->setCellValue('C1', '主要问题')  
            ->setCellValue('D1', '维修效率满意度')  
            ->setCellValue('E1', '窗口服务态度满意度')  
            ->setCellValue('F1', '热线服务态度满意度')  
            ->setCellValue('G1', '维修服务态度满意度')  
            ->setCellValue('H1', '缴费方式满意度')  
            ->setCellValue('I1', '室内平均温度')  
            ->setCellValue('J1', '供热计量政策了解程度')  
            ->setCellValue('K1', '倾向哪种供热收费方式')
			->setCellValue('L1', '计量收费预期')
			->setCellValue('M1', '能源公司供暖的相关信息')
			->setCellValue('N1', '房间地址')
			->setCellValue('O1', '投票时间')
			->setCellValue('P1', '留言'); 
  
        // 填充数据(UTF-8)  
        for($i=0;$i<count($ResultsData);$i++){  
			if(!empty($ResultsData[$i]['firchoose'])){
				$objPHPExcel->getActiveSheet(0)->setCellValue('A'.($i+2), $ResultsData[$i]['firchoose']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('B'.($i+2), $ResultsData[$i]['secchoose']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('C'.($i+2), $ResultsData[$i]['thichoose']); 
				$objPHPExcel->getActiveSheet(0)->setCellValue('D'.($i+2), $ResultsData[$i]['fourthchoose']);   
				$objPHPExcel->getActiveSheet(0)->setCellValue('E'.($i+2), $ResultsData[$i]['fifchoose']); 
				$objPHPExcel->getActiveSheet(0)->setCellValue('F'.($i+2), $ResultsData[$i]['sixchoose']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('G'.($i+2), $ResultsData[$i]['sevchoose']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('H'.($i+2), $ResultsData[$i]['eightchoose']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('I'.($i+2), $ResultsData[$i]['ninchoose']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('J'.($i+2), $ResultsData[$i]['tenchoose']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('K'.($i+2), $ResultsData[$i]['elechoose']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('L'.($i+2), $ResultsData[$i]['twchoose']);
				$objPHPExcel->getActiveSheet(0)->setCellValue('M'.($i+2), $ResultsData[$i]['thrchoose']);
				$objPHPExcel->getActiveSheet(0)->setCellValue('N'.($i+2), $ResultsData[$i]['address']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('O'.($i+2), date('Y-m-d H:i:s',$ResultsData[$i]['createtime']));//时间戳转换
				$objPHPExcel->getActiveSheet(0)->setCellValue('P'.($i+2), $ResultsData[$i]['content']);
			}
        }  
  
  
        // sheet命名  
        $objPHPExcel->getActiveSheet()->setTitle('能源公司用户满意度调查问卷统计表');  
  
        //设置第一个页签
        $objPHPExcel->setActiveSheetIndex(0);  
  
        // excel头参数  
        header('Content-Type: application/vnd.ms-excel');  
        header('Content-Disposition: attachment;filename="能源公司用户满意度调查问卷统计表('.date('Y-m-d').').xls"');  //日期为文件名后缀  
        header('Cache-Control: max-age=0');  
  
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  //excel5为xls格式，excel2007为xlsx格式  
        $objWriter->save('php://output'); 
	}


	//2017能源公司用户满意度调查结果导出
	public function exportenergy1(){
		$con['firchoose']=array('NEQ','');
        $ResultsData = D('Survey_energy1')->where($con)->select();  //查询数据得到$ResultsData二维数组  
        vendor("PHPExcel.PHPExcel");  
        // 建立PHPExcel对象  
        $objPHPExcel = new PHPExcel();  
        //设置宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(50);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(50);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(50);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(50);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(50);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(50);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(50);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);  
		$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(50);
		$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(20);  
		$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(50);
		$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(50);      
		$objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(200);  
  
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
		$objPHPExcel->getActiveSheet()->getStyle('N')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->getStyle('O')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		
  
        // 设置首行文字  
        $objPHPExcel->setActiveSheetIndex(0)  
            ->setCellValue('A1', '整体运营服务是否满意')  
            ->setCellValue('B1', '故障维修服务是否满意')  
            ->setCellValue('C1', '能源服务窗口服务是否满意')  
            ->setCellValue('D1', '反馈问题处置结果是否满意')  
            ->setCellValue('E1', '自来水供应是否满意')  
            ->setCellValue('F1', '不满意的原因')  
            ->setCellValue('G1', '天然气供应是否满意')  
            ->setCellValue('H1', '不满意的原因')  
            ->setCellValue('I1', '采暖供应是否满意')  
            ->setCellValue('J1', '不满意的原因')  
            ->setCellValue('K1', '相关政策、信息公示等工作是否满意')
			->setCellValue('L1', '常用的交费方式')
			->setCellValue('M1', '房间地址')
			->setCellValue('N1', '投票时间')
			->setCellValue('O1', '留言'); 
  
        // 填充数据(UTF-8)  
        for($i=0;$i<count($ResultsData);$i++){  
			if(!empty($ResultsData[$i]['firchoose'])){
				$objPHPExcel->getActiveSheet(0)->setCellValue('A'.($i+2), $ResultsData[$i]['firchoose']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('B'.($i+2), $ResultsData[$i]['secchoose']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('C'.($i+2), $ResultsData[$i]['thichoose']); 
				$objPHPExcel->getActiveSheet(0)->setCellValue('D'.($i+2), $ResultsData[$i]['fourthchoose']);   
				$objPHPExcel->getActiveSheet(0)->setCellValue('E'.($i+2), $ResultsData[$i]['fifchoose']); 
				$objPHPExcel->getActiveSheet(0)->setCellValue('F'.($i+2), $ResultsData[$i]['sixchoose']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('G'.($i+2), $ResultsData[$i]['sevchoose']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('H'.($i+2), $ResultsData[$i]['eightchoose']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('I'.($i+2), $ResultsData[$i]['ninchoose']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('J'.($i+2), $ResultsData[$i]['tenchoose']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('K'.($i+2), $ResultsData[$i]['elechoose']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('L'.($i+2), $ResultsData[$i]['twchoose']);
				$objPHPExcel->getActiveSheet(0)->setCellValue('M'.($i+2), $ResultsData[$i]['address']);
				$objPHPExcel->getActiveSheet(0)->setCellValue('N'.($i+2), date('Y-m-d H:i:s',$ResultsData[$i]['createtime']));//时间戳转换  
				$objPHPExcel->getActiveSheet(0)->setCellValue('O'.($i+2), $ResultsData[$i]['content']);
			}
        }  
  
  
        // sheet命名  
        $objPHPExcel->getActiveSheet()->setTitle('2017公用事业客户服务中心满意度调查问卷-能源公司专题');  
  
        //设置第一个页签
        $objPHPExcel->setActiveSheetIndex(0);  
  
        // excel头参数  
        header('Content-Type: application/vnd.ms-excel');  
        header('Content-Disposition: attachment;filename="2017公用事业客户服务中心满意度调查问卷-能源公司专题('.date('Y-m-d').').xls"');  //日期为文件名后缀  
        header('Cache-Control: max-age=0');  
  
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  //excel5为xls格式，excel2007为xlsx格式  
        $objWriter->save('php://output'); 
	}


	//供热用户满意度调查结果查询
	public function heating() {
		//每页条数
		if (!empty($_GET['limit'])) {
			$limit = $_GET['limit'];
			$index['limit'] = $_GET['limit'];
		} else {
			$limit = C('PAGE_SIZE');
			$index['limit'] = C('PAGE_SIZE');
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

		//查询调查结果
		//文章列表
		import("ORG.Util.Page");
		$count = D('Survey_heating')->where($data)->count();
		$page = new Page($count, $limit);
		$result_list = D('Survey_heating')->where($data)->limit($page->firstRow . ',' . $page->listRows)->order('id DESC')->select();
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


	//供热用户满意度调查结果详细信息
	 public function heat() {
        $id = intval($this->_param('id'));
		$i = 1;
        if ($id <= 0) {
            $this->error('参数错误！');
        } else {
			$list = D('Survey_heating')->where('id=' . $id)->find();
			//查询用户已绑定房间
			$this->assign('list', $list);
			$this->assign('id', $id);
            $this->display();
        }
    }


	//供热用户满意度调查结果导出
	public function exportheating(){
		$con['firchoose']=array('NEQ','');
        $ResultsData = D('Survey_heating')->where($con)->select();  //查询数据得到$ResultsData二维数组  
        vendor("PHPExcel.PHPExcel");  
        // 建立PHPExcel对象  
        $objPHPExcel = new PHPExcel();  
        //设置宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(50);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(100);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(100);  
		$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(50);
		$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(200);
  
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
  
        // 设置首行文字  
        $objPHPExcel->setActiveSheetIndex(0)  
            ->setCellValue('A1', '供热收费方式')  
            ->setCellValue('B1', '供热服务工作总体评价')  
            ->setCellValue('C1', '故障维修满意度')  
            ->setCellValue('D1', '服务窗口满意度')  
            ->setCellValue('E1', '热线服务满意度')  
            ->setCellValue('F1', '起居室平均温度')  
            ->setCellValue('G1', '是否实施节能措施')  
            ->setCellValue('H1', '节能措施预期效果')  
            ->setCellValue('I1', '能源服务哪些需要改进')  
            ->setCellValue('J1', '地址')  
            ->setCellValue('K1', '投票时间')
			->setCellValue('L1', '建议'); 
  
        // 填充数据(UTF-8)  
        for($i=0;$i<count($ResultsData);$i++){  
			if(!empty($ResultsData[$i]['firchoose'])){
				$objPHPExcel->getActiveSheet(0)->setCellValue('A'.($i+2), $ResultsData[$i]['firchoose']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('B'.($i+2), $ResultsData[$i]['secchoose']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('C'.($i+2), $ResultsData[$i]['thichoose']."(".$ResultsData[$i]['summary3'].")"); 
				$objPHPExcel->getActiveSheet(0)->setCellValue('D'.($i+2), $ResultsData[$i]['fourthchoose']."(".$ResultsData[$i]['summary4'].")");   
				$objPHPExcel->getActiveSheet(0)->setCellValue('E'.($i+2), $ResultsData[$i]['fifchoose']."(".$ResultsData[$i]['summary5'].")"); 
				$objPHPExcel->getActiveSheet(0)->setCellValue('F'.($i+2), $ResultsData[$i]['sixchoose']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('G'.($i+2), $ResultsData[$i]['sevchoose']."(".$ResultsData[$i]['summary7'].")");  
				$objPHPExcel->getActiveSheet(0)->setCellValue('H'.($i+2), $ResultsData[$i]['eightchoose']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('I'.($i+2), $ResultsData[$i]['ninchoose']."(".$ResultsData[$i]['summary9'].")");  
				$objPHPExcel->getActiveSheet(0)->setCellValue('J'.($i+2), $ResultsData[$i]['address']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('K'.($i+2), date('Y-m-d H:i:s',$ResultsData[$i]['createtime']));  
				$objPHPExcel->getActiveSheet(0)->setCellValue('L'.($i+2), $ResultsData[$i]['content']);
			}
        }  
  
        // sheet命名  
        $objPHPExcel->getActiveSheet()->setTitle('供热用户满意度调查问卷统计表');  
  
        //设置第一个页签
        $objPHPExcel->setActiveSheetIndex(0);  
  
        // excel头参数  
        header('Content-Type: application/vnd.ms-excel');  
        header('Content-Disposition: attachment;filename="供热用户满意度调查问卷统计表('.date('Y-m-d').').xls"');  //日期为文件名后缀  
        header('Cache-Control: max-age=0');  
  
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  //excel5为xls格式，excel2007为xlsx格式  
        $objWriter->save('php://output'); 
	}




//生活垃圾处理收费
	public function hb() {
		//每页条数
		if (!empty($_GET['limit'])) {
			$limit = $_GET['limit'];
			$index['limit'] = $_GET['limit'];
		} else {
			$limit = C('PAGE_SIZE');
			$index['limit'] = C('PAGE_SIZE');
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

		//查询调查结果
		//文章列表
		import("ORG.Util.Page");
		$count = D('Survey_hb')->where($data)->count();
		$page = new Page($count, $limit);
		$result_list = D('Survey_hb')->where($data)->limit($page->firstRow . ',' . $page->listRows)->order('id DESC')->select();
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

	//生活垃圾处理收费调查结果详细信息
	 public function hbxx() {
        $id = intval($this->_param('id'));
		$i = 1;
        if ($id <= 0) {
            $this->error('参数错误！');
        } else {
			$list = D('Survey_hb')->where('id=' . $id)->find();
			//查询用户已绑定房间
			$this->assign('list', $list);
			$this->assign('id', $id);
            $this->display();
        }
    }

//住宅小区物业满意度调查问卷
	public function service() {
		//小区名称总
		$url0 = 'http://10.105.15.2/TjstcWebImpl/GetCommunityNameServlet';
		$post_data0 ="";
		$ch0 = curl_init();
		curl_setopt($ch0, CURLOPT_URL, $url0);
		curl_setopt($ch0, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch0, CURLOPT_POST, 1);
		curl_setopt($ch0, CURLOPT_POSTFIELDS, $post_data0);
		$output0 = curl_exec($ch0);
		$infoarr=json_decode($output0,true);
		for($k = 0; $k < count($infoarr['r_body']); $k++){
			$info[$k]['COMMUNITYNAME']=$infoarr['r_body'][$k]['COMMUNITYNAME'];
		}

		$this->villageinfo = $info;

		//每页条数
		if (!empty($_GET['limit'])) {
			$limit = $_GET['limit'];
			$index['limit'] = $_GET['limit'];
		} else {
			$limit = C('PAGE_SIZE');
			$index['limit'] = C('PAGE_SIZE');
		}
		 //查询条件获取
		$village = $_POST['housename'];
		$username = $_POST['username'];
		$phone = $_POST['phone'];
		$qishishijian = $_POST['qishishijian'];
		$jieshushijian = $_POST['jieshushijian'];
		
		//小区名称
		if (!empty($_POST['housename'])) {
			 $data['village'] = $village;
			 $index['housename'] = $_POST['housename'];
        }
		//联系电话
		if (!empty($_POST['phone'])) {
             $data['phone'] = $phone;
			 $index['phone'] = $_POST['phone'];
        }
		//业主名称 
		if (!empty($_POST['username'])) {
             $data['username'] = $username;
			 $index['username'] = $_POST['username'];
        }
		//投票时间
		if ((!empty($_POST['qishishijian'])) && (!empty($_POST['jieshushijian']))) {
			$index['qishishijian'] = $qishishijian;
			 $index['jieshushijian'] = $jieshushijian;
			$qishishijian = $qishishijian."00:00:00";
			 $jieshushijian = $jieshushijian."23:59:59";
			 $data['vote_time'] = array(between,array(strtotime($qishishijian),strtotime($jieshushijian)));
			 
        }
		if((!empty($_POST['qishishijian'])) && (empty($_POST['jieshushijian']))){
			$index['qishishijian'] = $qishishijian;
			$qishishijian = $qishishijian."00:00:00";
			$data['vote_time'] = array(between,array(strtotime($qishishijian),time()));
			
		}
		if((empty($_POST['qishishijian'])) && (!empty($_POST['jieshushijian']))){
			$index['jieshushijian'] = $jieshushijian;
			$jieshushijian = $jieshushijian."23:59:59";
			$data['vote_time'] = array(between,array(strtotime("01 March 2019"),strtotime($jieshushijian)));
			
		}

		//查询调查结果
		//文章列表
		import("ORG.Util.Page");
		$count = D('vote_service')->where($data)->count();
		$page = new Page($count, $limit);
		$result_list = D('vote_service')->where($data)->limit($page->firstRow . ',' . $page->listRows)->order('id DESC')->select();
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

	//住宅小区物业满意度调查问卷详细
	 public function servicexx() {
        $id = intval($this->_param('id'));
		$i = 1;
        if ($id <= 0) {
            $this->error('参数错误！');
        } else {
			$list = D('vote_service')->where('id=' . $id)->find();
			//查询用户已绑定房间
			$this->assign('list', $list);
			$this->assign('id', $id);
            $this->display();
        }
    }

	//生活垃圾处理收费调查结果导出
	public function exporthb(){
		$con['firchoose']=array('NEQ','');
        $ResultsData = D('Survey_hb')->where($con)->select();  //查询数据得到$ResultsData二维数组  
        vendor("PHPExcel.PHPExcel");  
        // 建立PHPExcel对象  
        $objPHPExcel = new PHPExcel();  
        //设置宽度
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20); 
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(30);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(30);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(30);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(30);  
		$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(100);
  
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
  
        // 设置首行文字  
        $objPHPExcel->setActiveSheetIndex(0)  
			->setCellValue('A1', '小区名称')  
            ->setCellValue('B1', '姓名')  
            ->setCellValue('C1', '年龄')  
            ->setCellValue('D1', '联系电话')  
            ->setCellValue('E1', '垃圾气力输送系统')  
            ->setCellValue('F1', '生活垃圾处理情况')  
            ->setCellValue('G1', '需缴纳生活垃圾处理费')  
            ->setCellValue('H1', '生活垃圾分类处理并收费')  
            ->setCellValue('I1', '生活垃圾处理费为0.3元')  
            ->setCellValue('J1', '能源服务卡代扣垃圾处理费')  
            ->setCellValue('K1', '投票时间')
			->setCellValue('L1', '生活垃圾处理收费有哪些看法和建议');
  
        // 填充数据(UTF-8)  
        for($i=0;$i<count($ResultsData);$i++){  
			if(!empty($ResultsData[$i]['firchoose'])){
				$objPHPExcel->getActiveSheet(0)->setCellValue('A'.($i+2), $ResultsData[$i]['address']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('B'.($i+2), $ResultsData[$i]['username']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('C'.($i+2), $ResultsData[$i]['age']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('D'.($i+2), $ResultsData[$i]['phone']); 
				$objPHPExcel->getActiveSheet(0)->setCellValue('E'.($i+2), $ResultsData[$i]['firchoose']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('F'.($i+2), $ResultsData[$i]['secchoose']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('G'.($i+2), $ResultsData[$i]['thichoose']); 
				$objPHPExcel->getActiveSheet(0)->setCellValue('H'.($i+2), $ResultsData[$i]['fourchoose']); 
				$objPHPExcel->getActiveSheet(0)->setCellValue('I'.($i+2), $ResultsData[$i]['fifchoose']); 
				$objPHPExcel->getActiveSheet(0)->setCellValue('J'.($i+2), $ResultsData[$i]['sixchoose']); 
				$objPHPExcel->getActiveSheet(0)->setCellValue('K'.($i+2), date('Y-m-d H:i:s',$ResultsData[$i]['createtime']));  
				$objPHPExcel->getActiveSheet(0)->setCellValue('L'.($i+2), $ResultsData[$i]['content']); 
				
			}
        }  
  
        // sheet命名  
        $objPHPExcel->getActiveSheet()->setTitle('生态城生活垃圾处理收费问卷统计表');  
  
        //设置第一个页签
        $objPHPExcel->setActiveSheetIndex(0);  
  
        // excel头参数  
        header('Content-Type: application/vnd.ms-excel');  
        header('Content-Disposition: attachment;filename="生态城生活垃圾处理收费问卷统计表('.date('Y-m-d').').xls"');  //日期为文件名后缀  
        header('Cache-Control: max-age=0');  
  
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  //excel5为xls格式，excel2007为xlsx格式  
        $objWriter->save('php://output'); 
	}

	//2018年生态城公用事业满意度调查问卷查看
	 public function causeView() {
        $id = intval($this->_param('id'));
		$i = 1;
        if ($id <= 0) {
            $this->error('参数错误！');
        } else {
			$list = D('vote_cause')->where('id=' . $id)->find();
			//查询用户已绑定房间
			$this->assign('list', $list);
			$this->assign('id', $id);
            $this->display();
        }
    }
	

	//2018年生态城公用事业满意度调查问卷结果导出
	public function exportCause(){
		$con['twChoose']=array('NEQ','');
        $ResultsData = D('vote_cause')->where($con)->select();  //查询数据得到$ResultsData二维数组  
        vendor("PHPExcel.PHPExcel");  
        // 建立PHPExcel对象  
        $objPHPExcel = new PHPExcel();  
        //设置宽度
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20); 
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(30);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(30);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(30);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(30);  
		$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(30);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(50);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(50);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(50);  
		$objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(100);
  
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
        $objPHPExcel->getActiveSheet()->getStyle('N')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
		$objPHPExcel->getActiveSheet()->getStyle('O')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
        $objPHPExcel->getActiveSheet()->getStyle('P')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);  
		$objPHPExcel->getActiveSheet()->getStyle('Q')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
		$objPHPExcel->getActiveSheet()->getStyle('R')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
  
        // 设置首行文字  
        $objPHPExcel->setActiveSheetIndex(0)  
			->setCellValue('A1', '居住小区')  
            ->setCellValue('B1', '客户姓名')  
            ->setCellValue('C1', '用户电话')  
            ->setCellValue('D1', '房号')  
            ->setCellValue('E1', '自来水供应及服务')  
            ->setCellValue('F1', '天然气供应及服务')  
            ->setCellValue('G1', '供热供应及服务')  
            ->setCellValue('H1', '再生水（即中水）供应及服务')  
            ->setCellValue('I1', '水体、水质')  
            ->setCellValue('J1', '道路、桥梁、排水、路灯等市政设施整体运营')  
            ->setCellValue('K1', '景观绿化的管理')
			->setCellValue('L1', '道路保洁与环境卫生')
			->setCellValue('M1', '智能物回（积分兑换、大件回收等）')  
            ->setCellValue('N1', '公交（1、2、3、4号线路）车辆的安全保障')  
            ->setCellValue('O1', '公交（1、2、3、4号线路）驾驶员的仪容仪表和服务态度')  
            ->setCellValue('P1', '公交（1、2、3、4号线路）车身及车内部的清洁卫生')  
            ->setCellValue('Q1', '投票时间')
			->setCellValue('R1', '公用事业相关工作有意见');
  
        // 填充数据(UTF-8)  
        for($i=0;$i<count($ResultsData);$i++){  
			if(!empty($ResultsData[$i]['twChoose'])){
				$objPHPExcel->getActiveSheet(0)->setCellValue('A'.($i+2), $ResultsData[$i]['rDistrict']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('B'.($i+2), $ResultsData[$i]['customerName']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('C'.($i+2), $ResultsData[$i]['telephone']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('D'.($i+2), $ResultsData[$i]['roomNumber']); 
				$objPHPExcel->getActiveSheet(0)->setCellValue('E'.($i+2), $ResultsData[$i]['twChoose']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('F'.($i+2), $ResultsData[$i]['ngChoose']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('G'.($i+2), $ResultsData[$i]['hsChoose']); 
				$objPHPExcel->getActiveSheet(0)->setCellValue('H'.($i+2), $ResultsData[$i]['rwChoose']); 
				$objPHPExcel->getActiveSheet(0)->setCellValue('I'.($i+2), $ResultsData[$i]['wqChoose']); 
				$objPHPExcel->getActiveSheet(0)->setCellValue('J'.($i+2), $ResultsData[$i]['mfChoose']); 
				$objPHPExcel->getActiveSheet(0)->setCellValue('K'.($i+2), $ResultsData[$i]['lgChoose']); 
				$objPHPExcel->getActiveSheet(0)->setCellValue('L'.($i+2), $ResultsData[$i]['rcChoose']);
				$objPHPExcel->getActiveSheet(0)->setCellValue('M'.($i+2), $ResultsData[$i]['agentChoose']); 
				$objPHPExcel->getActiveSheet(0)->setCellValue('N'.($i+2), $ResultsData[$i]['busgChoose']); 
				$objPHPExcel->getActiveSheet(0)->setCellValue('O'.($i+2), $ResultsData[$i]['bdrChoose']); 
				$objPHPExcel->getActiveSheet(0)->setCellValue('P'.($i+2), $ResultsData[$i]['cleanupChoose']); 
				$objPHPExcel->getActiveSheet(0)->setCellValue('Q'.($i+2), date('Y-m-d H:i:s',$ResultsData[$i]['createtime']));  
				$objPHPExcel->getActiveSheet(0)->setCellValue('R'.($i+2), $ResultsData[$i]['opinionChoose']); 
				
			}
        }  
  
        // sheet命名  
        $objPHPExcel->getActiveSheet()->setTitle('2018年生态城公用事业满意度调查问卷统计表');  
  
        //设置第一个页签
        $objPHPExcel->setActiveSheetIndex(0);  
  
        // excel头参数  
        header('Content-Type: application/vnd.ms-excel');  
        header('Content-Disposition: attachment;filename="2018年生态城公用事业满意度调查问卷统计表('.date('Y-m-d').').xls"');  //日期为文件名后缀  
        header('Cache-Control: max-age=0');  
  
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  //excel5为xls格式，excel2007为xlsx格式  
        $objWriter->save('php://output'); 
	}

	//垃圾气力输送系统调查问卷
	public function dust() {
		//每页条数
		if (!empty($_GET['limit'])) {
			$limit = $_GET['limit'];
			$index['limit'] = $_GET['limit'];
		} else {
			$limit = C('PAGE_SIZE');
			$index['limit'] = C('PAGE_SIZE');
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

		//查询调查结果
		//文章列表
		import("ORG.Util.Page");
		$count = D('Vote_dust')->where($data)->count();
		$page = new Page($count, $limit);
		$result_list = D('Vote_dust')->where($data)->limit($page->firstRow . ',' . $page->listRows)->order('id DESC')->select();
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

	
	//垃圾气力输送系统调查问卷详细信息
	 public function dustxx() {
        $id = intval($this->_param('id'));
		$i = 1;
        if ($id <= 0) {
            $this->error('参数错误！');
        } else {
			$list = D('Vote_dust')->where('id=' . $id)->find();
			//查询用户已绑定房间
			$this->assign('list', $list);
			$this->assign('id', $id);
            $this->display();
        }
    }

	//垃圾气力输送系统调查问卷详细信息结果导出
	public function exportdust(){
		$con['firchoose']=array('NEQ','');
        $ResultsData = D('Vote_dust')->where($con)->select();  //查询数据得到$ResultsData二维数组  
        vendor("PHPExcel.PHPExcel");  
        // 建立PHPExcel对象  
        $objPHPExcel = new PHPExcel();  
        //设置宽度
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(50);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30); 
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(50);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(30);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(30);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(30);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(30);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(30);  
		$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(70);
		$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(200);
  
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
			->setCellValue('A1', '小区名称')  
            ->setCellValue('B1', '是否了解应用了气力垃圾输送系统')  
            ->setCellValue('C1', '垃圾中转站建在什么位置好')  
            ->setCellValue('D1', '小区采用哪种形式投放口')  
            ->setCellValue('E1', '是否愿意将垃圾投放至室外投放口')  
            ->setCellValue('F1', '是否会给您的生活带来方便')  
            ->setCellValue('G1', '应建在什么位置')  
            ->setCellValue('H1', '最担心的问题')  
            ->setCellValue('I1', '投放口有哪些不足')  
            ->setCellValue('J1', '投放口的外观颜色') 
			->setCellValue('K1', '使用过程中有哪些不足')
            ->setCellValue('L1', '投票时间')
			->setCellValue('M1', '对气力垃圾收运工作的宝贵意见和建议');
  
        // 填充数据(UTF-8)  
        for($i=0;$i<count($ResultsData);$i++){  
			if(!empty($ResultsData[$i]['firchoose'])){
				$objPHPExcel->getActiveSheet(0)->setCellValue('A'.($i+2), $ResultsData[$i]['housename']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('B'.($i+2), $ResultsData[$i]['firchoose']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('C'.($i+2), $ResultsData[$i]['secchoose']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('D'.($i+2), $ResultsData[$i]['thichoose']); 
				$objPHPExcel->getActiveSheet(0)->setCellValue('E'.($i+2), $ResultsData[$i]['fourthchoose']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('F'.($i+2), $ResultsData[$i]['fifchoose']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('G'.($i+2), $ResultsData[$i]['sixchoose']); 
				$objPHPExcel->getActiveSheet(0)->setCellValue('H'.($i+2), $ResultsData[$i]['sevchoose']); 
				$objPHPExcel->getActiveSheet(0)->setCellValue('I'.($i+2), $ResultsData[$i]['eightchoose']); 
				$objPHPExcel->getActiveSheet(0)->setCellValue('J'.($i+2), $ResultsData[$i]['ninchoose']); 
				$objPHPExcel->getActiveSheet(0)->setCellValue('K'.($i+2), $ResultsData[$i]['tenchoose']); 
				$objPHPExcel->getActiveSheet(0)->setCellValue('L'.($i+2), date('Y-m-d H:i:s',$ResultsData[$i]['createtime']));  
				$objPHPExcel->getActiveSheet(0)->setCellValue('M'.($i+2), $ResultsData[$i]['content']); 
				
			}
        }  
  
        // sheet命名  
        $objPHPExcel->getActiveSheet()->setTitle('垃圾气力输送系统调查问卷统计表');  
  
        //设置第一个页签
        $objPHPExcel->setActiveSheetIndex(0);  
  
        // excel头参数  
        header('Content-Type: application/vnd.ms-excel');  
        header('Content-Disposition: attachment;filename="垃圾气力输送系统调查问卷统计表('.date('Y-m-d').').xls"');  //日期为文件名后缀  
        header('Cache-Control: max-age=0');  
  
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  //excel5为xls格式，excel2007为xlsx格式  
        $objWriter->save('php://output'); 
	}
	


    //修改题目
    public function editlist() {
        $m_vote = D('vote');
        $m_vote_list = D('vote_list');
        $m_vote_option = D('vote_option');

        $voteid = intval($_GET['voteid']);
        if ($voteid != 0) {
            $vote = $m_vote->where("id = $voteid")->find();
            $votelist = $m_vote_list->where(" voteid = $voteid")->select();
            foreach ($votelist as $key => $val) {
                $listid = $votelist[$key]['listid'];
                $option[$listid] = $m_vote_option->where("listid = $listid")->select();
                $sum[$listid] = $m_vote_option->where("listid = $listid")->sum('poll');
                foreach ($option[$listid] as $k => $v) {
                    $width = 200;  //外层DIV宽度
                    $option[$listid][$k]['percent'] = round($option[$listid][$k]['poll'] / $sum[$listid] * 100, 2); //所占比例
                    $ratio = ($option[$listid][$k]['poll'] / $sum[$listid]) * $width; //所占比例的宽度
                    $option[$listid][$k]['ratio'] = $ratio;
                }
            }
            $data = array(
                'vote' => $vote,
                'votelist' => $votelist,
                'option' => $option,
            );
        }
        $this->assign($data);
        $this->display();
    }

    //修改已存在
    public function updatelist() {

        $m_vote_list = D('vote_list');
        $m_vote_option = D('vote_option');

        $listname = $_REQUEST['listname'];
        $listid = $_REQUEST['listid'];
        $is_mc = $_REQUEST['is_mc'];
        //选项
        $option = $_REQUEST['option'];
        foreach ($listid as $key => $val) {
            $save['listname'] = $listname[$key];
            $save['listid'] = $listid[$key];
            $save['is_mc'] = $is_mc[$key];
            $save['updatetime'] = time();
            $r = D('vote_list')->save($save);
            if (!empty($option[$key])) {
                foreach ($option[$key] as $k => $v) {

                    $op['id'] = $k;
                    $op['option'] = $option[$key][$k];
                    $op['listid'] = $listid[$key];
                    D('vote_option')->save($op);
                }
            }

            if ($r == FALSE) {
                $this->error('修改失败！');
            }
        }
        $this->success('修改成功！');
    }

    //删除主题
    public function delete() {

        $id = intval($_GET['id']);

        $m_vote = D('vote');
        $m_vote_list = D('vote_list');
        $m_vote_option = D('vote_option');

        $listarray = $m_vote_list->where("voteid = $id")->select();
        foreach ($listarray as $k => $v) {
            $listarr[] = $listarray[$k]['listid'];
        }
        //print_r($listarr);exit; 
        $map['listid'] = array('in', $listarr);
        $mo = $m_vote_option->where($map)->delete();

        $mv = $m_vote->where("id = $id")->delete();
        $ml = $m_vote_list->where("voteid = $id")->delete();
        if ($mv != false && $ml != false && $mo != false) {
            $this->success('删除成功！', '__URL__/index');
        } else {
            $this->error('删除失败');
        }
    }

    //删除标题
    public function dellist() {
        $m_vote_list = D('vote_list');
        $m_vote_option = D('vote_option');

        $listid = intval($_GET['listid']);
        if ($m_vote_list->where(" listid = $listid")->delete() == false) {
            $this->error('删除失败');
        }
        $option = $m_vote_option->where(" listid = $listid")->select();
        if (!empty($option)) {
            if ($m_vote_option->where(" listid = $listid")->delete() == false) {
                $this->error('选项删除失败！');
            }
        }
        $this->success('删除成功！');
    }

    //删除选项
    public function deloption() {

        $m_vote_option = D('vote_option');

        $id = intval($_GET['id']);
        if ($m_vote_option->where("id = $id")->delete() == true) {
            $this->success('选项删除成功！');
        } else {
            $this->error('删除失败！');
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
        $map['id'] = array('in', $ids);
        switch ($command) {
            case 'delete':
                $re = D('Vote')->where($map)->delete();
                $type = "删除";
                break;
            case 'status':
                $re = D('Vote')->where($map)->setField('status', '1');
                $type = "启用";
                break;
            case 'unstatus':
                $re = D('Vote')->where($map)->setField('status', '0');
                $type = "停用";
                break;
        }
        if ($re !== false) {
            $this->success('成功' . $type);
        } else {
            $this->error('失败！');
        }
    }

}

?>