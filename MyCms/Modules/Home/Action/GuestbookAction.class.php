<?php
header('content-type:text/html;charset=utf-8');
class GuestbookAction extends HomeAction {
	public function _empty(){
		$this->redirect("Public/404");
	}

    //投诉建议lists方法
    public function lists() {
        $this->check();
        $this->id = intval($this->_param('id'));
        $this->nav = $this->nav($this->cate, $this->id, ''); //当前位置
        $p = get_parents($this->cate, $this->id);
		if(empty($p)){
			$this->redirect("Public/404");
		}
        $this->topid = $p[0]['id']; //顶部导航选中效果

        if (is_numeric($this->id)) {
            $c = get_childsid($this->cate, $this->id);
            $cids = $this->id;
            foreach ($c as $v) {
                $cids.="," . $v;
            }
            $map['cat_id'] = array('in', $cids);
        }

        $userid = $_SESSION['uid'];
        $cat_id = intval($_GET['id']);
        $map['userid'] = array('eq', $userid);
        import("ORG.Util.Page");
        $count = D('guestbook')->where($map)->count();
        $page = new Page($count, C('PAGE_SIZE'));
        $page->setConfig('first', '首页');
        $page->setConfig('last', '末页');
        $page->setConfig('theme', ' %upPage%  %first%  %prePage%  %linkPage%  %nextPage% %end%%downPage%<span>%totalRow%条</span> <span>%nowPage%/%totalPage% 页</span>');

        $list = D('Guestbook')->where($map)->limit($page->firstRow . ',' . $page->listRows)->order('id  DESC')->select();

        $this->assign('list', $list);
        $show = $page->show();
        $this->assign('page', $show);
		$this->assign('uid', $userid);
        $this->display();
    }

    //投诉建议默认方法
    public function index() {
		session('varname','ts');
		//var_dump($_SESSION);exit;
		if (!isset($_SESSION['checklogin'])) {
            $this->redirect("Public/login");
        }

		//var_dump($_SESSION);exit;
        /*$this->check();
        $this->id = intval($this->_param('id'));
        $this->pid = D('Cate')->where('id=' . $this->id)->getField('pid'); //左侧导航
        $this->nav = $this->nav($this->cate, $this->id, ''); //当前位置
        $p = get_parents($this->cate, $this->id);
		
        $this->topid = $p[0]['id']; //顶部导航选中效果*/		
        $userid = intval($_SESSION['uid']);
        $this->assign('uid', $userid);
        $this->display();
    }
	//在线报修
	public function onlineRepair() {
		
		session('varname','bs');
		//var_dump($_SESSION);exit;
		if (!isset($_SESSION['checklogin'])) {
            $this->redirect("Public/login");
        }
        $this->id = intval($this->_param('id'));
        $this->pid = D('Cate')->where('id=' . $this->id)->getField('pid'); //左侧导航
        $this->nav = $this->nav($this->cate, $this->id, ''); //当前位置
        $p = get_parents($this->cate, $this->id);
		
        $this->topid = $p[0]['id']; //顶部导航选中效果

        $userid = intval($_SESSION['uid']);
		//$map['uid'] = $userid;
        //$map['ifBind'] = 1;
		//$info = D('User_room')->where($map)->select();
		$url0 = 'http://10.105.15.2/TjstcWebImpl/GetAppUserBindHouseServlet';
		$post_data0 ="vusername=$userid";
		$ch0 = curl_init();
		curl_setopt($ch0, CURLOPT_URL, $url0);
		curl_setopt($ch0, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch0, CURLOPT_POST, 1);
		curl_setopt($ch0, CURLOPT_POSTFIELDS, $post_data0);
		$output0 = curl_exec($ch0);
		$infoarr=json_decode($output0,true);
		for($k = 0; $k < count($infoarr['r_body']); $k++){
			$info[$k]['HOUSECODE']=$infoarr['r_body'][$k]['HOUSECODE'];
			$info[$k]['ADDRESS']=$infoarr['r_body'][$k]['ADDRESS'];
		}

		$this->roominfo = $info;

        
		$this->assign('uid', $userid);
        $this->display();
    }
	//受理记录
	public function acceptanceRecord() {
        $this->check();
        $this->id = intval($this->_param('id'));
        $this->pid = D('Cate')->where('id=' . $this->id)->getField('pid'); //左侧导航
        $this->nav = $this->nav($this->cate, $this->id, ''); //当前位置
        $p = get_parents($this->cate, $this->id);
		
        $this->topid = $p[0]['id']; //顶部导航选中效果

        $userid = intval($_SESSION['uid']);
		$url2 = 'http://10.105.15.2/TjstcWebImpl/GetAcceptanceRecordServlet';
		$post_data2 ="vUID=$userid&vTYPE=1";
		$ch2 = curl_init();
		curl_setopt($ch2, CURLOPT_URL, $url2);
		curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch2, CURLOPT_POST, 1);
		curl_setopt($ch2, CURLOPT_POSTFIELDS, $post_data2);
		$output2 = curl_exec($ch2);
		$shouliarr=json_decode($output2,true);
		for($k = 0; $k < count($shouliarr['r_body']); $k++){
			$shouli[$k]['id']=$shouliarr['r_body'][$k]['id'];
			$shouli[$k]['proposer']=$shouliarr['r_body'][$k]['proposer'];
			$shouli[$k]['operatedate']=$shouliarr['r_body'][$k]['operatedate'];
			$shouli[$k]['Content']=$shouliarr['r_body'][$k]['Content'];
			$shouli[$k]['state']=$shouliarr['r_body'][$k]['state'];
		}
		$this->shouli = $shouli;
        $this->assign('uid', $userid);
        $this->display();
    }
	//投诉建议受理记录
	public function acceptanceRecordtx() {
        $this->check();
        $this->id = intval($this->_param('id'));
        $this->pid = D('Cate')->where('id=' . $this->id)->getField('pid'); //左侧导航
        $this->nav = $this->nav($this->cate, $this->id, ''); //当前位置
        $p = get_parents($this->cate, $this->id);
		
        $this->topid = $p[0]['id']; //顶部导航选中效果

        $userid = intval($_SESSION['uid']);
		$url2 = 'http://10.105.15.2/TjstcWebImpl/GetAcceptanceRecordServlet';
		$post_data2 ="vUID=$userid&vTYPE=2";
		$ch2 = curl_init();
		curl_setopt($ch2, CURLOPT_URL, $url2);
		curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch2, CURLOPT_POST, 1);
		curl_setopt($ch2, CURLOPT_POSTFIELDS, $post_data2);
		$output2 = curl_exec($ch2);
		$shouliarr=json_decode($output2,true);
		for($k = 0; $k < count($shouliarr['r_body']); $k++){
			$shouli[$k]['id']=$shouliarr['r_body'][$k]['id'];
			$shouli[$k]['proposer']=$shouliarr['r_body'][$k]['proposer'];
			$shouli[$k]['operatedate']=$shouliarr['r_body'][$k]['operatedate'];
			$shouli[$k]['Content']=$shouliarr['r_body'][$k]['Content'];
			$shouli[$k]['state']=$shouliarr['r_body'][$k]['state'];
			$shouli[$k]['type']=$shouliarr['r_body'][$k]['type'];
		}
		$this->shouli = $shouli;
        $this->assign('uid', $userid);
        $this->display();
    }
	//受理记录详情
	public function acceptanceRecordView() {
        $this->check();
        $this->id = intval($this->_param('id'));
        $this->pid = D('Cate')->where('id=' . $this->id)->getField('pid'); //左侧导航
        $this->nav = $this->nav($this->cate, $this->id, ''); //当前位置
        $p = get_parents($this->cate, $this->id);
		
        $this->topid = $p[0]['id']; //顶部导航选中效果

        $userid = intval($_SESSION['uid']);
		$aid=$_GET['aid'];
		$url2 = 'http://10.105.15.2/TjstcWebImpl/GetAcceptanceRecordDetailServlet';
		$post_data2 ="vAID=$aid";
		$ch2 = curl_init();
		curl_setopt($ch2, CURLOPT_URL, $url2);
		curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch2, CURLOPT_POST, 1);
		curl_setopt($ch2, CURLOPT_POSTFIELDS, $post_data2);
		$output2 = curl_exec($ch2);
		$shouliarr=json_decode($output2,true);
		//var_dump($shouliarr);exit;
		for($k = 0; $k < count($shouliarr['r_body']); $k++){
			$shouli[$k]['id']=$shouliarr['r_body'][$k]['id'];
			$shouli[$k]['proposer']=$shouliarr['r_body'][$k]['proposer'];
			$shouli[$k]['operatedate']=$shouliarr['r_body'][$k]['operatedate'];
			$shouli[$k]['Content']=$shouliarr['r_body'][$k]['Content'];
			$shouli[$k]['state']=$shouliarr['r_body'][$k]['state'];
			$shouli[$k]['contactnumber']=$shouliarr['r_body'][$k]['contactnumber'];
			$shouli[$k]['involveindustry']=$shouliarr['r_body'][$k]['involveindustry'];
			$shouli[$k]['involvetype']=$shouliarr['r_body'][$k]['involvetype'];
			$shouli[$k]['handletype']=$shouliarr['r_body'][$k]['handletype'];
			$shouli[$k]['answercontent']=$shouliarr['r_body'][$k]['answercontent'];
			$shouli[$k]['answertime']=$shouliarr['r_body'][$k]['answertime'];
			$pic=$shouliarr['r_body'][$k]['filename'];
			$picvar=explode(",",$pic);
			$shouli[$k]['pic1']=$picvar[0];
			$shouli[$k]['pic2']=$picvar[1];
			$shouli[$k]['pic3']=$picvar[2];
		}
		$aaa=$shouliarr['r_body'][0]['id'];
		$this->assign('aaa', $aaa);
		$this->shouli = $shouli;
        $this->assign('uid', $userid);
        $this->display();
    }
		//投诉建议受理记录详情
	public function acceptanceRecordtxView() {
        $this->check();
        $this->id = intval($this->_param('id'));
        $this->pid = D('Cate')->where('id=' . $this->id)->getField('pid'); //左侧导航
        $this->nav = $this->nav($this->cate, $this->id, ''); //当前位置
        $p = get_parents($this->cate, $this->id);
		
        $this->topid = $p[0]['id']; //顶部导航选中效果

        $userid = intval($_SESSION['uid']);
		$aid=$_GET['aid'];
		$url2 = 'http://10.105.15.2/TjstcWebImpl/GetAcceptanceRecordDetailServlet';
		$post_data2 ="vAID=$aid";
		$ch2 = curl_init();
		curl_setopt($ch2, CURLOPT_URL, $url2);
		curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch2, CURLOPT_POST, 1);
		curl_setopt($ch2, CURLOPT_POSTFIELDS, $post_data2);
		$output2 = curl_exec($ch2);
		$shouliarr=json_decode($output2,true);
		//var_dump($shouliarr);exit;
		for($k = 0; $k < count($shouliarr['r_body']); $k++){
			$shouli[$k]['id']=$shouliarr['r_body'][$k]['id'];
			$shouli[$k]['proposer']=$shouliarr['r_body'][$k]['proposer'];
			$shouli[$k]['operatedate']=$shouliarr['r_body'][$k]['operatedate'];
			$shouli[$k]['Content']=$shouliarr['r_body'][$k]['Content'];
			$shouli[$k]['state']=$shouliarr['r_body'][$k]['state'];
			$shouli[$k]['contactnumber']=$shouliarr['r_body'][$k]['contactnumber'];
			$shouli[$k]['involveindustry']=$shouliarr['r_body'][$k]['involveindustry'];
			$shouli[$k]['involvetype']=$shouliarr['r_body'][$k]['involvetype'];
			$shouli[$k]['handletype']=$shouliarr['r_body'][$k]['handletype'];
			$shouli[$k]['answercontent']=$shouliarr['r_body'][$k]['answercontent'];
			$shouli[$k]['answertime']=$shouliarr['r_body'][$k]['answertime'];
			$pic=$shouliarr['r_body'][$k]['filename'];
			$picvar=explode(",",$pic);
			$shouli[$k]['pic1']=$picvar[0];
			$shouli[$k]['pic2']=$picvar[1];
			$shouli[$k]['pic3']=$picvar[2];
		}
		$aaa=$shouliarr['r_body'][0]['id'];
		$this->assign('aaa', $aaa);
		$this->shouli = $shouli;
        $this->assign('uid', $userid);
        $this->display();
    }
	//根据房间编号查涉及行业
	public function hs() {
		$housecode=$_POST[housecode];
		$url = 'http://10.105.15.2/TjstcWebImpl/GetInvolveIndustryServlet';
		$post_data ="vHOUSECODE=$housecode";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		$output = curl_exec($ch);
		$involvearr=json_decode($output,true);

		$involvearr1=$involvearr['r_body'][0]['involveindustry'];
		for($k = 0; $k < count($involvearr['r_body']); $k++){
			$involveindustry[$k]['involveindustry']=$involvearr['r_body'][$k]['involveindustry'];
		}
		echo $this->ajaxReturn($involveindustry,'JSON');
        exit();
	}
	//根据涉及行业名称查询涉及种类
	public function sjhy() {
		$sjhy=$_POST['sjhy'];
		$url2 = 'http://10.105.15.2/TjstcWebImpl/GetInvolveTypeServlet';
		$post_data2 ="vINVOLVEINDUSTRY=$sjhy";
		$ch2 = curl_init();
		curl_setopt($ch2, CURLOPT_URL, $url2);
		curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch2, CURLOPT_POST, 1);
		curl_setopt($ch2, CURLOPT_POSTFIELDS, $post_data2);
		$output2 = curl_exec($ch2);
		$involvetypearr=json_decode($output2,true);
		for($k = 0; $k < count($involvetypearr['r_body']); $k++){
			$involvetype[$k]['involvetype']=$involvetypearr['r_body'][$k]['involvetype'];
			$involvetype[$k]['involvetypeid']=$involvetypearr['r_body'][$k]['id'];
		}
		echo $this->ajaxReturn($involvetype,'JSON');
        exit();
	}
	//根据涉及种类ID查询办理类别、工单类别
	public function sjzl() {
		$sjzl=$_POST['sjzl'];
		$url2 = 'http://10.105.15.2/TjstcWebImpl/GetOnlineRepairTypeServlet';
		$post_data2 ="vINVOLVETYPE=$sjzl";
		$ch2 = curl_init();
		curl_setopt($ch2, CURLOPT_URL, $url2);
		curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch2, CURLOPT_POST, 1);
		curl_setopt($ch2, CURLOPT_POSTFIELDS, $post_data2);
		$output2 = curl_exec($ch2);
		$handletypearr=json_decode($output2,true);
		for($k = 0; $k < count($handletypearr['r_body']); $k++){
			$handletype[$k]['handletype']=$handletypearr['r_body'][$k]['handletype'];
			$handletype[$k]['handletypeid']=$handletypearr['r_body'][$k]['id'];
		}
		echo $this->ajaxReturn($handletype,'JSON');
        exit();
	}
    //投诉建议提交方法
    public function insert() {
		
		$name=$_POST['name'];
		$phone=$_POST['phone'];
		$neirong=$_POST['neirong'];
		$uid=$_POST['uid'];
		//var_dump($uid);
		$lx=$_POST['lx'];
		$url2 = 'http://10.105.15.2/TjstcWebImpl/InsertAcceptanceRecordServlet';
		$post_data2 ="vPROPOSER=$name&vCONTACTNUMBER=$phone&vCONTENT=$neirong&vTYPE=$lx&vSOURCE=网站&vUID=$uid";
		$ch2 = curl_init();
		curl_setopt($ch2, CURLOPT_URL, $url2);
		curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch2, CURLOPT_POST, 1);
		curl_setopt($ch2, CURLOPT_POSTFIELDS, $post_data2);
		$output2 = curl_exec($ch2);
		$data=json_decode($output2,true);
		//var_dump($data);exit;
		$end=$data['r_code'];
		
		if($end==0000){
			echo "<script>alert('提交成功');</script>";
			echo "<script language='javascript'>";
			echo 'window.top.location="http://www.66885890.com/index.php?s=/user/index"';
			echo "</script>";
		}else{
			echo "<script>alert('提交失败，请稍后再试');</script>";
			echo "<script language='javascript'>";
			echo 'window.top.location="http://www.66885890.com/index.php?s=/guestbook/index/id/18"';
			echo "</script>";		
		}
        
    }

//在线报修提交方法
    public function bxinsert() {
		$this->check();
        $fileBase64=$_POST['epc'];//图片路径名称数组
		//$filecount=count($_POST['epc']);
		$vHOUSECODE=$_POST['housecode'];
		$vHOUSENAME=$_POST['housename'];
		$vUID=$_POST['uid'];
		$vPROPOSER=$_POST['name'];
		$vCONTACTNUMBER=$_POST['phone'];
		$ss=$_POST['sjhy'];
		$vOID=$_POST['sjzl'];
		$vTID=$_POST['bllb'];
		$vCONTENT=$_POST['neirong'];
		$vTYPE="报修";
		$vSOURCE="网站";
		
		$url2 = 'http://10.105.15.2/TjstcWebImpl/InsertAcceptanceRecordServlet';
		$post_data2 ="vOID=$vOID&vTID=$vTID&vPROPOSER=$vPROPOSER&vHOUSECODE=$vHOUSECODE&vHOUSENAME=$vHOUSENAME&vCONTACTNUMBER=$vCONTACTNUMBER&vCONTENT=$vCONTENT&vTYPE=$vTYPE&vSOURCE=$vSOURCE&vUID=$vUID&fileBase64=$fileBase64";
		header('Content-Type:application/x-www-form-urlencoded');
		$ch2 = curl_init();
		curl_setopt($ch2, CURLOPT_URL, $url2);
		curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch2, CURLOPT_POST, 1);
		curl_setopt($ch2, CURLOPT_POSTFIELDS, $post_data2);
		$output2 = curl_exec($ch2);
		$data2=json_decode($output2,true);
		$end=$data2['r_code'];
		//print_r($end);
		if($end==0000){
			$data=1;
			echo  json_encode($data);
			
		}else{
			$data=1;
			echo  json_encode($data);
			
		}
       
    }
    //预览
    function view() {
        $id = intval($this->_param('id'));
        $infor = D('Guestbook')->where('id=' . $id)->find();
		if(empty($infor)){
			$this->redirect("Public/404");
		}
        $this->assign($infor);
        $this->display();
    }

    public function verify() {
        $type = isset($_GET['type']) ? $_GET['type'] : 'gif';
        import("ORG.Util.Image");
        Image::buildImageVerify(4, 1, $type);
    }

}

?>
