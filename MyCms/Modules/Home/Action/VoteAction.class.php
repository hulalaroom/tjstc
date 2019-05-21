<?php

class VoteAction extends HomeAction {
	public function _empty(){
		$this->redirect("Public/404");
	}
	public function cat() {
		$this->id = $this->_get('id');
		
		$this->nav = $this->nav($this->cate, $this->id, ''); //位置导航
		$p = get_parents($this->cate, $this->id);
		if(empty($p)){
			$this->redirect("Public/404");
		}
		$this->topid = $p[0]['id']; //顶部导航选中
		$this->display();
	}
	
	/*******建设中开始*******/
	public function jianshe() {
		$this->display();
	}
	/*******建设中结束*******/
	
    public function index() {
         $this->id = $this->_get('id');
            $this->nav = $this->nav($this->cate, $this->id, ''); //当前位置
            $p = get_parents($this->cate, $this->id);
			if(empty($p)){
			$this->redirect("Public/404");
		}
        
            $this->topid = $p[0]['id']; //顶部导航选中效果
            //文章搜索
            //栏目查询
            if (is_numeric($this->id)) {
                $c = get_childsid($this->cate, $this->id);
                $cids = $this->id;
                foreach ($c as $v) {
                    $cids.="," . $v;
                }
                $map['cat_id'] = array('in', $cids);
            }
     
        //有效期
       /* $map['start_time'] = array('elt', time()); //发布时间小于现在时间
        $map['_string'] = 'end_time = 0 OR end_time > ' . time(); //结束时间大于现在时间或者等于0*/
        //状态
        $map['status'] = 1;
        //文章列表
        import("ORG.Util.Page");
        $count = D('Vote')->where($map)->count();
        $page = new Page($count, C('PAGE_SIZE'));
		
        $vote_list = D('Vote')->where($map)->limit($page->firstRow . ',' . $page->listRows)->order('id  DESC')->select();
        $this->assign('vote_list', $vote_list);

		$show = $page->show();
        $this->assign('page', $show);
        $this->display();
    }

		//2018年生态城公用事业满意度调查问卷
	public function cause() {
		$this->flag = "cause";
		$m_vote = D('Vote');
        $m_vote_list = M('vote_list');
        $m_vote_option = M('vote_option');

        $voteid = intval($_GET['id']);
        $vote = $m_vote->where("id = $voteid")->find();
        
        /*-- 判断是否过期  */
        $temp_vote = R('Vote/valid', array($voteid));
        if(empty($temp_vote)){
            $expire = 'expire';
        }
        /* 判断是否过期 --*/
        
        //左侧导航
//        $this->pid = D('Cate')->where('id=' . $vote['cat_id'])->getField('pid'); //控制左侧子菜单
        $this->nav = $this->nav($this->cate, $vote['cat_id'], $voteid,'Vote'); //当前位置
        $p = get_parents($this->cate, $vote['cat_id']);
		if(empty($p)){
			$this->redirect("Public/404");
		}
        $this->topid = $p[0]['id']; //顶部导航选中效果
//
        $votelist = $m_vote_list->where("voteid = $voteid")->order('listid asc')->select();
        foreach ($votelist as $key => $val) {
            $listid = $votelist[$key]['listid'];
            $option[$listid] = $m_vote_option->where("listid = $listid")->order('id asc')->select();
        }
        $data = array(
            'vote' => $vote,
            'votelist' => $votelist,
            'option' => $option,
            'expire' => $expire,
        );
        $this->assign($data);
		$this->display();
	}
	public function service() {
		session('varname','vote');
		if (!isset($_SESSION['checklogin'])) {
            $this->redirect("Public/login");
        }
		$code=$_POST["houseCode"];
		$this->houselist = R('User/fjbd', array($_SESSION['uid']));
		$houselist = R('User/fjbd', array($_SESSION['uid']));
		if($code==''){
		$code=$houselist[0][houseCode];
		}
		$this->assign('code',$code);

		$this->flag = "service";
		$m_vote = D('Vote');
        $m_vote_list = M('vote_list');
        $m_vote_option = M('vote_option');
		/*-- 用户信息  */
		$url = "http://10.105.15.2/TjstcWebImpl/GetHouseInfoServlet";
		$post_data = "vHOUSECODE=$code";
		$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $output = curl_exec($ch);
        $data=json_decode($output,true);
        curl_close($ch);
		$houseva = $data["r_body"];
		$yzname=$houseva[0]["OWNERNAME"];
		$yzphone=$houseva[0]["MOBILEPHONE"];
		$jzxq=$houseva[0]["COMMUNITYNAME"];
		$this->assign('yzname',$yzname);
		$this->assign('yzphone',$yzphone);
		$this->assign('jzxq',$jzxq);
		
        $voteid = intval($_GET['id']);
        $vote = $m_vote->where("id = $voteid")->find();
        
        /*-- 判断是否过期  */
        $temp_vote = R('Vote/valid', array($voteid));
        if(empty($temp_vote)){
            $expire = 'expire';
        }
        /* 判断是否过期 --*/
        
        //左侧导航
//        $this->pid = D('Cate')->where('id=' . $vote['cat_id'])->getField('pid'); //控制左侧子菜单
        $this->nav = $this->nav($this->cate, $vote['cat_id'], $voteid,'Vote'); //当前位置
        $p = get_parents($this->cate, $vote['cat_id']);
		if(empty($p)){
			$this->redirect("Public/404");
		}
        $this->topid = $p[0]['id']; //顶部导航选中效果
//
        $votelist = $m_vote_list->where("voteid = $voteid")->order('listid asc')->select();
        foreach ($votelist as $key => $val) {
            $listid = $votelist[$key]['listid'];
            $option[$listid] = $m_vote_option->where("listid = $listid")->order('id asc')->select();
        }
        $data = array(
            'vote' => $vote,
            'votelist' => $votelist,
            'option' => $option,
            'expire' => $expire,
        );
        $this->assign($data);
		$this->display();
	}
	public function servicecg() {
		header('content-type:text/html;charset=utf-8');
		$mysql_server_name='10.105.15.2';
		$mysql_username='root';
		$mysql_password='root';
		$mysql_database='new';
		$conn = mysqli_connect($mysql_server_name,$mysql_username,$mysql_password,$mysql_database);
		$housecode=$_POST['housecode'];
		$username=$_POST['username'];
		$phone=$_POST['phone'];
		$village=$_POST['village'];
		$uid=$_SESSION['uid'];
		$ninchoose=$_POST['ninchoose'];
		$eightchoose=$_POST['eightchoose'];
		$sevchoose=$_POST['sevchoose'];
		$sixchoose=$_POST['sixchoose'];
		$fifchoose=$_POST['fifchoose'];
		$fourchoose=$_POST['fourchoose'];
		$thichoose=$_POST['thichoose'];
		$secchoose=$_POST['secchoose'];
		$firchoose=$_POST['firchoose'];
		$vote_time=time();
		$zxfirchoosearr=$_POST['zxfirchoose'];
		$zxfirchoose = implode(",", $zxfirchoosearr);
		$zxsecchoosearr=$_POST['zxsecchoose'];
		$zxsecchoose = implode(",", $zxsecchoosearr);
		$zxthichoose=$_POST['zxthichoose'];
		$zxfourchoose=$_POST['zxfourchoose'];
		$sql2="select id from ad_vote_service where housecode='$housecode'";
		$rs2= $conn->query($sql2);
		$val2=mysqli_fetch_all($rs2);
		if(!empty($val2))               //判断是否参与过调差
		{
			echo "<script>alert('该房间已参与过该问卷调查，请勿重复参与');</script>";
			echo "<script language='javascript'>";
			echo 'window.top.location="http://www.66885890.com/index.php?s=/user/index"';
			echo "</script>";
		}else{

			$sql="insert into ad_vote_service (housecode,username,phone,village,ninchoose,eightchoose,sevchoose,sixchoose,fifchoose,fourchoose,thichoose,secchoose,firchoose,vote_time,zxfirchoose,zxsecchoose,zxthichoose,zxfourchoose,uid) VALUES ('$housecode','$username','$phone','$village','$ninchoose','$eightchoose','$sevchoose','$sixchoose','$fifchoose','$fourchoose','$thichoose','$secchoose','$firchoose','$vote_time','$zxfirchoose','$zxsecchoose','$zxthichoose','$zxfourchoose','$uid')";
			//var_dump($sql);exit;
			$rs = $conn->query($sql);
			if ($rs) {
				echo "<script>alert('提交成功');</script>";
				echo "<script language='javascript'>";
				echo 'window.top.location="http://www.66885890.com/index.php?s=/user/index"';
				echo "</script>";
			} else {

				echo "<script>alert('提交失败，请稍后尝试');</script>";
				echo "<script language='javascript'>";
				echo 'window.top.location="http://www.66885890.com/index.php?s=/vote/service/id/13.html"';
				echo "</script>";
			}
		}

	}
    //调查问卷详细
    public function view() {
		
		$this->flag = "view";
        $m_vote = D('Vote');
        $m_vote_list = M('vote_list');
        $m_vote_option = M('vote_option');

        $voteid = intval($_GET['id']);
        $vote = $m_vote->where("id = $voteid")->find();
        
        /*-- 判断是否过期  */
        $temp_vote = R('Vote/valid', array($voteid));
        if(empty($temp_vote)){
            $expire = 'expire';
        }
        /* 判断是否过期 --*/
        
        //左侧导航
//        $this->pid = D('Cate')->where('id=' . $vote['cat_id'])->getField('pid'); //控制左侧子菜单
        $this->nav = $this->nav($this->cate, $vote['cat_id'], $voteid,'Vote'); //当前位置
        $p = get_parents($this->cate, $vote['cat_id']);
		if(empty($p)){
			$this->redirect("Public/404");
		}
        $this->topid = $p[0]['id']; //顶部导航选中效果
//
        $votelist = $m_vote_list->where("voteid = $voteid")->order('listid asc')->select();
        foreach ($votelist as $key => $val) {
            $listid = $votelist[$key]['listid'];
            $option[$listid] = $m_vote_option->where("listid = $listid")->order('id asc')->select();
        }
        $data = array(
            'vote' => $vote,
            'votelist' => $votelist,
            'option' => $option,
            'expire' => $expire,
        );
        $this->assign($data);
        $this->display();
    }

	//调查问卷详细(生态城2014年物业满意度调查问卷)
    public function other() {

		$this->flag = "other";
        $m_vote = D('Vote');
        $m_vote_list = M('vote_list');
        $m_vote_option = M('vote_option');

        $voteid = intval($_GET['id']);
        $vote = $m_vote->where("id = $voteid")->find();
        
        /*-- 判断是否过期  */
        $temp_vote = R('Vote/valid', array($voteid));
        if(empty($temp_vote)){
            $expire = 'expire';
        }
        /* 判断是否过期 --*/
        //左侧导航
//      $this->pid = D('Cate')->where('id=' . $vote['cat_id'])->getField('pid'); //控制左侧子菜单
        $this->nav = $this->nav($this->cate, $vote['cat_id'], $voteid,'Vote'); //当前位置
        $p = get_parents($this->cate, $vote['cat_id']);
		if(empty($p)){
			$this->redirect("Public/404");
		}
        $this->topid = $p[0]['id']; //顶部导航选中效果
//
        $votelist = $m_vote_list->where("voteid = $voteid")->order('listid asc')->select();
        foreach ($votelist as $key => $val) {
            $listid = $votelist[$key]['listid'];
            $option[$listid] = $m_vote_option->where("listid = $listid")->order('id asc')->select();
        }
        $data = array(
            'vote' => $vote,
            'votelist' => $votelist,
            'option' => $option,
            'expire' => $expire,
        );
        
        $this->assign($data);
        $this->display();
    }

	//垃圾气力输送系统调查问卷
    public function dust() {

		$this->flag = "dust";
        $m_vote = D('Vote');
        $m_vote_list = M('vote_list');
        $m_vote_option = M('vote_option');

        $voteid = intval($_GET['id']);
        $vote = $m_vote->where("id = $voteid")->find();
        
        /*-- 判断是否过期  */
        $temp_vote = R('Vote/valid', array($voteid));
        if(empty($temp_vote)){
            $expire = 'expire';
        }
        /* 判断是否过期 --*/
        //左侧导航
//      $this->pid = D('Cate')->where('id=' . $vote['cat_id'])->getField('pid'); //控制左侧子菜单
        $this->nav = $this->nav($this->cate, $vote['cat_id'], $voteid,'Vote'); //当前位置
        $p = get_parents($this->cate, $vote['cat_id']);
		if(empty($p)){
			$this->redirect("Public/404");
		}
        $this->topid = $p[0]['id']; //顶部导航选中效果
//
        $votelist = $m_vote_list->where("voteid = $voteid")->order('listid asc')->select();
        foreach ($votelist as $key => $val) {
            $listid = $votelist[$key]['listid'];
            $option[$listid] = $m_vote_option->where("listid = $listid")->order('id asc')->select();
        }
        $data = array(
            'vote' => $vote,
            'votelist' => $votelist,
            'option' => $option,
            'expire' => $expire,
        );
        
        $this->assign($data);
        $this->display();
    }

	//调查问卷详细(公交车满意度调查问卷)
    public function others() {

		$this->flag = "others";
        $m_vote = D('Vote');
        $m_vote_list = M('vote_list');
        $m_vote_option = M('vote_option');

        $voteid = intval($_GET['id']);
        $vote = $m_vote->where("id = $voteid")->find();
        
        /*-- 判断是否过期  */
        $temp_vote = R('Vote/valid', array($voteid));
        if(empty($temp_vote)){
            $expire = 'expire';
        }
        /* 判断是否过期 --*/
        //左侧导航
//      $this->pid = D('Cate')->where('id=' . $vote['cat_id'])->getField('pid'); //控制左侧子菜单
        $this->nav = $this->nav($this->cate, $vote['cat_id'], $voteid,'Vote'); //当前位置
        $p = get_parents($this->cate, $vote['cat_id']);
		if(empty($p)){
			$this->redirect("Public/404");
		}
        $this->topid = $p[0]['id']; //顶部导航选中效果
//
        $votelist = $m_vote_list->where("voteid = $voteid")->order('listid asc')->select();
        foreach ($votelist as $key => $val) {
            $listid = $votelist[$key]['listid'];
            $option[$listid] = $m_vote_option->where("listid = $listid")->order('id asc')->select();
        }
        $data = array(
            'vote' => $vote,
            'votelist' => $votelist,
            'option' => $option,
            'expire' => $expire,
        );
        
        $this->assign($data);
        $this->display();
    }
	
	//调查问卷详细(供热用户满意度调查)
    public function heating() {

		$this->flag = "heating";
        $m_vote = D('Vote');
        $m_vote_list = M('vote_list');
        $m_vote_option = M('vote_option');

        $voteid = intval($_GET['id']);
        $vote = $m_vote->where("id = $voteid")->find();
        
        /*-- 判断是否过期  */
        $temp_vote = R('Vote/valid', array($voteid));
        if(empty($temp_vote)){
            $expire = 'expire';
        }
        /* 判断是否过期 --*/
        //左侧导航
//      $this->pid = D('Cate')->where('id=' . $vote['cat_id'])->getField('pid'); //控制左侧子菜单
        $this->nav = $this->nav($this->cate, $vote['cat_id'], $voteid,'Vote'); //当前位置
        $p = get_parents($this->cate, $vote['cat_id']);
		if(empty($p)){
			$this->redirect("Public/404");
		}
        $this->topid = $p[0]['id']; //顶部导航选中效果
//
        $votelist = $m_vote_list->where("voteid = $voteid")->order('listid asc')->select();
		
        foreach ($votelist as $key => $val) {
            $listid = $votelist[$key]['listid'];
            $option[$listid] = $m_vote_option->where("listid = $listid")->order('id asc')->select();
        }
        $data = array(
            'vote' => $vote,
            'votelist' => $votelist,
            'option' => $option,
            'expire' => $expire,
        );
		//小区名称
        $this->villagelist = D('Survey_heating_address')->field('COMMUNITYNAME,COMMUNITYCODE')->select();
        $this->assign($data);
        $this->display();
    }


	//公用事业服务热线66885890问卷调查
    public function custom() {

		$this->flag = "custom";
        $m_vote = D('Vote');
        $m_vote_list = M('vote_list');
        $m_vote_option = M('vote_option');

        $voteid = intval($_GET['id']);
        $vote = $m_vote->where("id = $voteid")->find();
        
        /*-- 判断是否过期  */
        $temp_vote = R('Vote/valid', array($voteid));
        if(empty($temp_vote)){
            $expire = 'expire';
        }
        /* 判断是否过期 --*/
        //左侧导航
//      $this->pid = D('Cate')->where('id=' . $vote['cat_id'])->getField('pid'); //控制左侧子菜单
        $this->nav = $this->nav($this->cate, $vote['cat_id'], $voteid,'Vote'); //当前位置
        $p = get_parents($this->cate, $vote['cat_id']);
		if(empty($p)){
			$this->redirect("Public/404");
		}
        $this->topid = $p[0]['id']; //顶部导航选中效果
//
        $votelist = $m_vote_list->where("voteid = $voteid")->order('listid asc')->select();
		
        foreach ($votelist as $key => $val) {
            $listid = $votelist[$key]['listid'];
            $option[$listid] = $m_vote_option->where("listid = $listid")->order('id asc')->select();
        }
        $data = array(
            'vote' => $vote,
            'votelist' => $votelist,
            'option' => $option,
            'expire' => $expire,
        );
		//dump($data);
		//小区名称
        $this->villagelist = D('Survey_heating_address')->field('COMMUNITYNAME,COMMUNITYCODE')->select();
        $this->assign($data);
        $this->display();
    }


	//公用事业客户服务中心满意度调查问卷-能源公司专题
    public function energy() {

		$this->flag = "energy";
        $m_vote = D('Vote');
        $m_vote_list = M('vote_list');
        $m_vote_option = M('vote_option');

        $voteid = intval($_GET['id']);
        $vote = $m_vote->where("id = $voteid")->find();
        
        /*-- 判断是否过期  */
        $temp_vote = R('Vote/valid', array($voteid));
        if(empty($temp_vote)){
            $expire = 'expire';
        }
        /* 判断是否过期 --*/
        //左侧导航
//      $this->pid = D('Cate')->where('id=' . $vote['cat_id'])->getField('pid'); //控制左侧子菜单
        $this->nav = $this->nav($this->cate, $vote['cat_id'], $voteid,'Vote'); //当前位置
        $p = get_parents($this->cate, $vote['cat_id']);
		if(empty($p)){
			$this->redirect("Public/404");
		}
        $this->topid = $p[0]['id']; //顶部导航选中效果
//
        $votelist = $m_vote_list->where("voteid = $voteid")->order('listid asc')->select();
		
        foreach ($votelist as $key => $val) {
            $listid = $votelist[$key]['listid'];
            $option[$listid] = $m_vote_option->where("listid = $listid")->order('id asc')->select();
        }
        $data = array(
            'vote' => $vote,
            'votelist' => $votelist,
            'option' => $option,
            'expire' => $expire,
        );
		//dump($data);
		//小区名称
        $this->villagelist = D('Survey_heating_address')->field('COMMUNITYNAME,COMMUNITYCODE')->select();
        $this->assign($data);
        $this->display();
    }

	//调查问卷详细(环保公司垃圾收费问题调查)
    public function hb() {

		$this->flag = "hb";
        $m_vote = D('Vote');
        $m_vote_list = M('vote_list');
        $m_vote_option = M('vote_option');

        $voteid = intval($_GET['id']);
        $vote = $m_vote->where("id = $voteid")->find();
        
        /*-- 判断是否过期  */
        $temp_vote = R('Vote/valid', array($voteid));
        if(empty($temp_vote)){
            $expire = 'expire';
        }
        /* 判断是否过期 --*/
        //左侧导航
//      $this->pid = D('Cate')->where('id=' . $vote['cat_id'])->getField('pid'); //控制左侧子菜单
        $this->nav = $this->nav($this->cate, $vote['cat_id'], $voteid,'Vote'); //当前位置
        $p = get_parents($this->cate, $vote['cat_id']);
		if(empty($p)){
			$this->redirect("Public/404");
		}
        $this->topid = $p[0]['id']; //顶部导航选中效果
//
        $votelist = $m_vote_list->where("voteid = $voteid")->order('listid asc')->select();
		
        foreach ($votelist as $key => $val) {
            $listid = $votelist[$key]['listid'];
            $option[$listid] = $m_vote_option->where("listid = $listid")->order('id asc')->select();
        }
        $data = array(
            'vote' => $vote,
            'votelist' => $votelist,
            'option' => $option,
            'expire' => $expire,
        );

		//小区名称
        $this->villagelist = D('Survey_heating_address')->field('COMMUNITYNAME,COMMUNITYCODE')->select();
        $this->assign($data);
        $this->display();
    }
	
	
	//调查问卷详细(能源公司居民满意度调查问卷)
    public function another() {

		$this->flag = "another";
        $m_vote = D('Vote');
        $m_vote_list = M('vote_list');
        $m_vote_option = M('vote_option');

        $voteid = intval($_GET['id']);
        $vote = $m_vote->where("id = $voteid")->find();
        
        /*-- 判断是否过期  */
        $temp_vote = R('Vote/valid', array($voteid));
        if(empty($temp_vote)){
            $expire = 'expire';
        }
        /* 判断是否过期 --*/
        //左侧导航
//      $this->pid = D('Cate')->where('id=' . $vote['cat_id'])->getField('pid'); //控制左侧子菜单
        $this->nav = $this->nav($this->cate, $vote['cat_id'], $voteid,'Vote'); //当前位置
        $p = get_parents($this->cate, $vote['cat_id']);
		if(empty($p)){
			$this->redirect("Public/404");
		}
        $this->topid = $p[0]['id']; //顶部导航选中效果
//
        $votelist = $m_vote_list->where("voteid = $voteid")->order('listid asc')->select();
        foreach ($votelist as $key => $val) {
            $listid = $votelist[$key]['listid'];
            $option[$listid] = $m_vote_option->where("listid = $listid")->order('id asc')->select();
        }
        $data = array(
            'vote' => $vote,
            'votelist' => $votelist,
            'option' => $option,
            'expire' => $expire,
        );
		//小区名称
        $this->villagelist = D('Survey_address')->field('COMMUNITYNAME,COMMUNITYCODE')->group('COMMUNITYNAME')->select();
        $this->assign($data);
        $this->display();
    }
	
	//获取号楼
	function getBuilding() {
        $parameter = $_POST['parameter'];
        $data = D('Survey_address')->field('BUILDINGNAME,BUILDINGCODE,COMMUNITYCODE')->where("COMMUNITYCODE = $parameter")->group('BUILDINGCODE')->select();
        echo $this->ajaxReturn($data,'JSON');
        exit();
    }
	
	//获取单元
	function getUite() {
        $map['BUILDINGCODE'] = $_POST['parameter'];
		$map['COMMUNITYCODE'] = $_POST['parameter_1'];
        $data = D('Survey_address')->field('CELLCODE')->where($map)->group('CELLCODE')->select();
        echo $this->ajaxReturn($data,'JSON');
        exit();
    }
	
	//获取层
	function getFloor() {
        $map['CELLCODE'] = $_POST['parameter'];
		$map['COMMUNITYCODE'] = $_POST['parameter_1'];
		$map['BUILDINGCODE'] = $_POST['parameter_2'];
        $data = D('Survey_address')->field('FLOOR')->where($map)->group('FLOOR')->select();
        echo $this->ajaxReturn($data,'JSON');
        exit();
    }
	
	//获取号
	function getNumber() {
        $map['FLOOR'] = $_POST['parameter'];
		$map['COMMUNITYCODE'] = $_POST['parameter_1'];
		$map['BUILDINGCODE'] = $_POST['parameter_2'];
		$map['CELLCODE'] = $_POST['parameter_3'];
        $data = D('Survey_address')->field('DOORPLATECODE')->where($map)->group('DOORPLATECODE')->select();
        echo $this->ajaxReturn($data,'JSON');
        exit();
    }

    public function save1() {

        $voteid = intval($_REQUEST['id']);
        $m_vote_option = M('vote_option');
        $optionid = $_REQUEST['optionid'];
		$vote = R('Vote/valid', array($voteid));
		if(empty($vote)){
			echo_json('0', '操作失败', '此次调查已经结束，感谢您的关注！','', '2');
		}
        if (!empty($optionid)) {
            foreach ($optionid as $key => $val) {
                foreach ($optionid[$key] as $k => $v) {
                    $r = $m_vote_option->where('id =' . $v)->setInc('poll', 1);
                    if ($r == false) {
                        //$this->error('投票失败，请尝试重新操作！');
						 echo_json('0', '操作失败', '投票失败！','', '2');
                    }
                }
            }
        } else {
           // $this->error('您未选择任何选项！');
			 echo_json('0', '操作失败', '您未选择任何选项！','', '2');
        }
		if($_SESSION['isvoted'] ==1){
			echo_json('0', '操作失败', '您已经投过票，请勿反复提交','', '2');
		}
		$_SESSION['isvoted']= 1;
		echo_json('1', '操作成功', '投票成功！', U('Vote/result','voteid='. $voteid), '2');
//        $this->success('投票成功！', '__URL__/result/voteid/' . $voteid);
    }

	//判断调查是否到期
	public function valid($id) {
		$map['start_time'] = array('elt', time()); //发布时间小于现在时间
        $map['_string'] = 'end_time = 0 OR end_time > ' . time(); //结束时间大于现在时间或者等于0
		$map['id'] = $id;
		$vote =  D('Vote')->where($map)->find();
		return $vote;
	}

	 public function save() {
		
		$flag = $_POST['flag'];
		$voteid = intval($_REQUEST['id']);
		$vote = R('Vote/valid', array($voteid));
		if(empty($vote)){
			echo_json('0', '操作失败', '此次调查已经结束，感谢您的关注！','', '2');
		}

		if($flag == 'other'){
			$code = D('Survey')->where('code=' . $_POST['ide_code'])->find();
			if(empty($code)){
				echo_json('0', '操作失败', '您提交的验证码错误，请重新输入','', '2');
			}
			if(!empty($code['housename'])){
				echo_json('0', '操作失败', '您已经投过票，请勿反复提交','', '2');
			}
		}
		
		if($_SESSION['isvoted'] ==1){
			echo_json('0', '操作失败', '您已经投过票，请勿反复提交','', '2');
		}
		
        
        $m_vote_option = M('vote_option');
		$survey = M('survey');
        $optionid = $_REQUEST['optionid'];
        if (!empty($optionid)) {
            foreach ($optionid as $key => $val) {
                foreach ($optionid[$key] as $k => $v) {
                    $r = $m_vote_option->where('id =' . $v)->setInc('poll', 1);
					if($flag == "other"){
						$option = $m_vote_option->where('id =' . $v)->find();
						
						 switch ($option['listid']) {
							case 9:
								$res = $survey->where('code=' . $_POST['ide_code'])->setField('firchoose', $option['option']);
								break;
							case 10:
								$res = $survey->where('code=' . $_POST['ide_code'])->setField('secchoose', $option['option']);
								break;
							case 11:
								$res = $survey->where('code=' . $_POST['ide_code'])->setField('thichoose', $option['option']);
								break;
							case 12:
								$res = $survey->where('code=' . $_POST['ide_code'])->setField('fouchoose', $option['option']);
								break;
							case 13:
								$res = $survey->where('code=' . $_POST['ide_code'])->setField('fifchoose', $option['option']);
								break;
							case 14:
								$res = $survey->where('code=' . $_POST['ide_code'])->setField('sixchoose', $option['option']);
								break;
							case 15:
								$res = $survey->where('code=' . $_POST['ide_code'])->setField('sevchoose', $option['option']);
								break;
						}
					}
                    if ($r == false) {
						 echo_json('0', '操作失败', '投票失败！','', '2');
                    }
                }
            }
        } else {
           // $this->error('您未选择任何选项！');
			 echo_json('0', '操作失败', '您未选择任何选项！','', '2');
        }
		if($flag == "other"){
			$data['housename'] = $_POST['village_name'];
			$data['telephone'] = $_POST['telephone'];
			$status = $_POST['status'];
			$status = '其它' ? $_POST['qita'] : $_POST['status'];
			$data['status'] = $_POST['status'];
			$data['content'] = $_POST['content'];
			$data['updatetime'] = time();
			$re = D('Survey')->where('code=' . $_POST['ide_code'])->save($data);
		}
		$_SESSION['isvoted']= 1;
		echo_json('1', '操作成功', '投票成功！', U('Vote/index','voteid='. $voteid), '2');
//        $this->success('投票成功！', '__URL__/result/voteid/' . $voteid);
    }

	 public function save2() {
		
		$flag = $_POST['flag'];
		$voteid = $_POST['id'];
		
		$vote = R('Vote/valid', array($voteid));
		if(empty($vote)){
			echo_json('0', '操作失败', '此次调查已经结束，感谢您的关注！','', '2');
		}
		
		/*if($_SESSION['isvoted'] ==1){
			echo_json('0', '操作失败', '您已经投过票，请勿反复提交','', '2');
		}*/
		
        $m_vote_option = M('vote_option');
		$survey = M('survey_bus');
        $optionid = $_REQUEST['optionid'];
		
        if (!empty($optionid)) {
            foreach ($optionid as $key => $val) {
                foreach ($optionid[$key] as $k => $v) {
					
                   $r = $m_vote_option->where('id =' . $v)->setInc('poll', 1);
					if($flag == "others"){
						$option = $m_vote_option->where('id =' . $v)->find();
						 switch ($option['listid']) {
							case 37:
								$data['firchoose'] = $option['option'];
								break;
							case 38:
								$data['secchoose'] = $option['option'];
								break;
							case 39:
								$choose_18 .= $option['option'].",";
								$data['thichoose'] = $choose_18;
								continue;
							case 40:
								$choose_19 .= $option['option'].",";
								$data['fifchoose'] = $choose_19;
								continue;
							case 41:
								$data['sixchoose'] = $option['option'];
								break;
							case 42:
								$choose_21 .= $option['option'].",";
								$data['sevchoose'] = $choose_21;
								continue;
							case 43:
								$choose_22 .= $option['option'].",";
								$data['eightchoose'] = $choose_22;
								continue;
							case 44:
								$choose_23 .= $option['option'].",";
								$data['ninchoose'] = $choose_23;
								continue;
						}
					}
                    if ($r == false) {
						 echo_json('0', '操作失败', '投票失败！','', '2');
                    }
                }
            }
        } else {
			 echo_json('0', '操作失败', '您未选择任何选项！','', '2');
        }
		if($flag == "others"){
			$data['startplace'] = $_POST['startPlace'];
			$data['endplace'] = $_POST['endPlace'];
			$data['cartimes'] = $_POST['carTimes'];
			$data['cartime'] = $_POST['carTime'];
			$data['content'] = $_POST['content'];
			$data['createtime'] = time();
			$re = D('Survey_bus')->add($data);
		}
		//$_SESSION['isvoted']= 1;
		echo_json('1', '操作成功', '投票成功！', U('Vote/index','id=21'), '2');
//        $this->success('投票成功！', '__URL__/result/voteid/' . $voteid);
    }
	
	
	public function save3() {
		
		$flag = $_POST['flag'];
		$ide_code = $_POST['ide_code'];
		$voteid = $_POST['id'];
		
		$m_vote_option = M('vote_option');
		$survey = M('survey_energy');
        $optionid = $_REQUEST['optionid'];
		
		$vote = R('Vote/valid', array($voteid));
		if(empty($vote)){
			echo_json('0', '操作失败', '此次调查已经结束，感谢您的关注！','', '2');
		}
		
		if($_SESSION['isvoted'] ==1){
			echo_json('0', '操作失败', '您已经投过票，请勿反复提交','', '2');
		}
		
		$code = '"'.$_POST['building'].'-'.$_POST['uite'].'-'.$_POST['floor'].'-'.$_POST['number'].'"';
		
		$first = $_POST['first'];
		//获取房间编号
		$ret = D('Survey_code')->where("housecode = $code")->select();
		if(empty($ret[0]['housecode']) && $first == 'true'){
			echo_json('0', '操作失败', '该房间不是按照供热计量收费，请选择按面积收费！','', '5');
		}
		
		if(!empty($ret[0]['housecode']) && $ide_code == ''){
			echo_json('0', '操作失败', '请输入验证码！','', '5');
		}
		
		if(!empty($ret[0]['housecode']) && $ide_code != ''){
			$res['housecode'] = $ret[0]['housecode'];
			//获取验证码
			$ver = D('Survey_code')->where($res)->select();
			if($ver[0]['code'] != $ide_code){
				echo_json('0', '操作失败', '请输入正确的验证码！','', '5');
			}
		}
		
        if (!empty($optionid)) {
            foreach ($optionid as $key => $val) {
                foreach ($optionid[$key] as $k => $v) {
					
                   $r = $m_vote_option->where('id =' . $v)->setInc('poll', 1);
					if($flag == "another"){
						$option = $m_vote_option->where('id =' . $v)->find();
						 switch ($option['listid']) {
							case 24:
								$parameter = $option['option'];
								$data['firchoose'] = $option['option'];
								break;
							case 25:
								$data['secchoose'] = $option['option'];
								break;
							case 26:
								$choose_26 .= $option['option'].",";
								$data['thichoose'] = $choose_26;
								continue;
							case 27:
								$data['fourthchoose'] = $option['option'];
								break;
							case 28:
								$data['fifchoose'] = $option['option'];
								break;
							case 29:
								$data['sixchoose'] = $option['option'];
								break;
							case 30:
								$data['sevchoose'] = $option['option'];
								break;
							case 31:
								$data['eightchoose'] = $option['option'];
								break;
							case 32:
								$data['ninchoose'] = $option['option'];
								break;
							case 33:
								$data['tenchoose'] = $option['option'];
								break;
							case 34:
								$data['elechoose'] = $option['option'];
								break;
							case 35:
								if($parameter == '供热计量收费'){
									$data['twchoose'] = $option['option'];
								}
								else{
									$data['twchoose'] = "";
								}
								break;
							case 36:
								$choose_36 .= $option['option'].",";
								$data['thrchoose'] = $choose_36;
								continue;
						}
					}
                    if ($r == false) {
						 echo_json('0', '操作失败', '投票失败！','', '2');
                    }
                }
            }
        } else {
			 echo_json('0', '操作失败', '您未选择任何选项！','', '2');
        }
		if($flag == "another"){
			$parameter = $_POST['village'];
			$parameter_1 = '"'.$_POST['building'].'"';
			$result = D('Survey_address')->field('COMMUNITYNAME')->where("COMMUNITYCODE = $parameter")->group('COMMUNITYCODE')->select();
			$result_1 = D('Survey_address')->field('BUILDINGNAME')->where("BUILDINGCODE = $parameter_1")->group('BUILDINGCODE')->select();
			$data['address'] = $result[0]['COMMUNITYNAME'].'小区'.$result_1[0]['BUILDINGNAME'].$_POST['uite'].'单元'.$_POST['floor'].'层'.$_POST['number'].'号';
			$data['summary'] = $_POST['summary'];
			$data['content'] = $_POST['content'];
			$data['createtime'] = time();
			$re = D('Survey_energy')->add($data);
		}
		$_SESSION['isvoted']= 1;
		echo_json('1', '操作成功', '投票成功！', U('Vote/index','id=21'), '2');
    }


	public function save4() {
		
		$flag = $_POST['flag'];
		$voteid = $_POST['id'];
		
		$m_vote_option = M('vote_option');
		$survey = M('survey_heating');
        $optionid = $_REQUEST['optionid'];
		
		$vote = R('Vote/valid', array($voteid));
		if(empty($vote)){
			echo_json('0', '操作失败', '此次调查已经结束，感谢您的关注！','', '2');
		}
		
		if($_SESSION['isvoted'] ==1){
			echo_json('0', '操作失败', '您已经投过票，请勿反复提交','', '2');
		}
		
		$first = $_POST['first'];
		
        if (!empty($optionid)) {
            foreach ($optionid as $key => $val) {
                foreach ($optionid[$key] as $k => $v) {
                   $r = $m_vote_option->where('id =' . $v)->setInc('poll', 1);
					if($flag == "heating"){
						$option = $m_vote_option->where('id =' . $v)->find();
						 switch ($option['listid']) {
							case 37:
								$parameter = $option['option'];
								$data['firchoose'] = $option['option'];
								break;
							case 38:
								$data['secchoose'] = $option['option'];
								break;
							case 39:
								$data['thichoose'] = $option['option'];
								break;
							case 40:
								$data['fourthchoose'] = $option['option'];	
								break;
							case 41:
								$data['fifchoose'] = $option['option'];
								break;
							case 42:
								$data['sixchoose'] = $option['option'];
								break;
							case 43:
								$data['sevchoose'] = $option['option'];
								break;
							case 44:
								$data['eightchoose'] = $option['option'];
								break;
							case 45:
								$choose_45 .= $option['option'].",";
								$data['ninchoose'] = $choose_45;
								continue;
							
						}
					}
                    if ($r == false) {
						 echo_json('0', '操作失败', '投票失败！','', '2');
                    }
                }
            }
        } else {
			 echo_json('0', '操作失败', '您未选择任何选项！','', '2');
        }
		if($flag == "heating"){
			//$parameter = $_POST['village'];
			//$parameter_1 = '"'.$_POST['building'].'"';
			//$result = D('Survey_address')->field('COMMUNITYNAME')->where("COMMUNITYCODE = $parameter")->group('COMMUNITYCODE')->select();
			//$result_1 = D('Survey_address')->field('BUILDINGNAME')->where("BUILDINGCODE = $parameter_1")->group('BUILDINGCODE')->select();
			$data['address'] = $_POST['village'].'小区'.$_POST['building'].'号楼'.$_POST['floor'];
			$data['summary3'] = $_POST['summary3'];
			$data['summary4'] = $_POST['summary4'];
			$data['summary5'] = $_POST['summary5'];
			$data['summary7'] = $_POST['summary7'];
			$data['summary9'] = $_POST['summary9'];
			$data['content'] = $_POST['content'];
			$data['username'] = $_POST['username'];
			$data['phone'] = $_POST['telephone'];
			$data['createtime'] = time();
			$re = D('Survey_heating')->add($data);
		}
		$_SESSION['isvoted']= 1;
		echo_json('1', '操作成功', '投票成功！', U('Vote/index','id=21'), '2');
    }


	//提交环保垃圾收费调查
	public function save5() {
		
		$flag = $_POST['flag'];
		$voteid = $_POST['id'];

		$address = $_POST['village'];
		$username = $_POST['username'];
		$phone = $_POST['telephone'];
		
		$m_vote_option = M('vote_option');
		$survey = M('survey_hb');
        $optionid = $_REQUEST['optionid'];
		
		$vote = R('Vote/valid', array($voteid));
		if(empty($vote)){
			echo_json('0', '操作失败', '此次调查已经结束，感谢您的关注！','', '10');
		}
		
		$map['address'] = $address.'小区';
		$map['username'] = $username;
		$map['phone'] = $phone;
		$info = $survey->where($map)->find();
		
		if(!empty($info)){
			echo_json('0', '操作失败', '您已经投过票，请勿反复提交','', '10');
		}
		
        if (!empty($optionid)) {
            foreach ($optionid as $key => $val) {
                foreach ($optionid[$key] as $k => $v) {
                   $r = $m_vote_option->where('id =' . $v)->setInc('poll', 1);
					if($flag == "hb"){
						$option = $m_vote_option->where('id =' . $v)->find();
						 switch ($option['listid']) {
							case 46:
								$parameter = $option['option'];
								$data['firchoose'] = $option['option'];
								break;
							case 47:
								$data['secchoose'] = $option['option'];
								break;
							case 48:
								$data['thichoose'] = $option['option'];
								break;
							case 49:
								$data['fourchoose'] = $option['option'];	
								break;
							case 50:
								$data['fifchoose'] = $option['option'];
								break;
							case 51:
								$data['sixchoose'] = $option['option'];
								break;
							
						}
					}
                    if ($r == false) {
						 echo_json('0', '操作失败', '投票失败！','', '2');
                    }
                }
            }
        } else {
			 echo_json('0', '操作失败', '您未选择任何选项！','', '2');
        }
		if($flag == "hb"){
			//$parameter = $_POST['village'];
			//$parameter_1 = '"'.$_POST['building'].'"';
			//$result = D('Survey_address')->field('COMMUNITYNAME')->where("COMMUNITYCODE = $parameter")->group('COMMUNITYCODE')->select();
			//$result_1 = D('Survey_address')->field('BUILDINGNAME')->where("BUILDINGCODE = $parameter_1")->group('BUILDINGCODE')->select();
			$data['address'] = $address.'小区';
			
			$data['summery4'] = $_POST['summary4'];
			$data['summery5'] = $_POST['summary5'];
			$data['summery6'] = $_POST['summary6'];

			$data['content'] = $_POST['content'];
			
			$data['username'] = $username;
			$data['phone'] = $phone;
			$data['age'] = $_POST['age'];
			$data['createtime'] = time();
			$re = D('Survey_hb')->add($data);
		}
		
		echo_json('1', '操作成功', '投票成功！', U('Vote/index','id=21'), '10');
    }
	




	public function save6() {
		
		$flag = $_POST['flag'];
		$voteid = $_POST['id'];
		
		$m_vote_option = M('vote_option');
		$survey = M('vote_custom');
        $optionid = $_REQUEST['optionid'];
		
		$vote = R('Vote/valid', array($voteid));
		if(empty($vote)){
			echo_json('0', '操作失败', '此次调查已经结束，感谢您的关注！','', '2');
		}

		$map['address'] = $_POST['village'].'小区';
		$map['username'] = $_POST['username'];
		$map['phone'] = $_POST['telephone'];
        $count = D('Vote_custom')->where($map)->count();
		
		if($count == 1){
			echo_json('0', '操作失败', '您已经投过票，请勿反复提交','', '10');
		}
		
		$first = $_POST['first'];
		
        if (!empty($optionid)) {
            foreach ($optionid as $key => $val) {
                foreach ($optionid[$key] as $k => $v) {
                   $r = $m_vote_option->where('id =' . $v)->setInc('poll', 1);
					if($flag == "custom"){
						$option = $m_vote_option->where('id =' . $v)->find();
						 switch ($option['listid']) {
							case 52:
								$parameter = $option['option'];
								$data['firchoose'] = $option['option'];
								break;
							case 53:
								$data['secchoose'] = $option['option'];
								break;
							case 54:
								$data['thichoose'] = $option['option'];
								break;
							case 55:
								$data['fourchoose'] = $option['option'];	
								break;
							case 56:
								$data['fifchoose'] = $option['option'];
								break;
							case 57:
								$data['sixchoose'] = $option['option'];
								break;
							case 58:
								$data['sevchoose'] = $option['option'];
								break;
							case 59:
								$data['eightchoose'] = $option['option'];
								break;
							case 60:
								//$choose_60 .= $option['option'];
								$data['ninchoose'] = $option['option'];
								continue;
							case 87:
								$data['tenchoose'] = $option['option'];
								continue;
							case 88:
								$data['elechoose'] = $option['option'];
								continue;
							
						}
					}
                    if ($r == false) {
						 echo_json('0', '操作失败', '投票失败！','', '2');
                    }
                }
            }
        } else {
			 echo_json('0', '操作失败', '您未选择任何选项！','', '2');
        }
		if($flag == "custom"){
			//$parameter = $_POST['village'];
			//$parameter_1 = '"'.$_POST['building'].'"';
			//$result = D('Survey_address')->field('COMMUNITYNAME')->where("COMMUNITYCODE = $parameter")->group('COMMUNITYCODE')->select();
			//$result_1 = D('Survey_address')->field('BUILDINGNAME')->where("BUILDINGCODE = $parameter_1")->group('BUILDINGCODE')->select();
			
			$data['address'] = $_POST['village'].'小区';
			$data['summary2'] = $_POST['summary2'];
			$data['summary3'] = $_POST['summary3'];
			
			$data['content'] = $_POST['content'];
			
			$data['username'] = $_POST['username'];
			$data['phone'] = $_POST['telephone'];
			$data['createtime'] = time();
			$data['year'] = date('Y');
			$re = D('Vote_custom')->add($data);
		}
		
		echo_json('1', '操作成功', '投票成功！', U('Vote/index','id=21'), '2');
    }


	public function save7() {
		
		$flag = $_POST['flag'];
		$voteid = $_POST['id'];
		
		$m_vote_option = M('vote_option');
		$survey = M('Survey_energy1');
        $optionid = $_REQUEST['optionid'];
		
		$vote = R('Vote/valid', array($voteid));
		if(empty($vote)){
			echo_json('0', '操作失败', '此次调查已经结束，感谢您的关注！','', '2');
		}

		$map['username'] = $_POST['username'];
		$map['phone'] = $_POST['telephone'];
        $count = D('Survey_energy1')->where($map)->count();
		
		/*if($count == 1){
			echo_json('0', '操作失败', '您已经投过票，请勿反复提交','', '10');
		}*/

        if (!empty($optionid)) {
			
            foreach ($optionid as $key => $val) {
                foreach ($optionid[$key] as $k => $v) {
                   $r = $m_vote_option->where('id =' . $v)->setInc('poll', 1);
					if($flag == "energy"){
						$option = $m_vote_option->where('id =' . $v)->find();
						 switch ($option['listid']) {
							case 61:
								$parameter = $option['option'];
								$data['firchoose'] = $option['option'];
								break;
							case 62:
								$data['secchoose'] = $option['option'];
								break;
							case 63:
								$data['thichoose'] = $option['option'];
								break;
							case 64:
								$data['fourthchoose'] = $option['option'];	
								break;
							case 65:
								$data['fifchoose'] = $option['option'];
								break;
							case 66:
								$data['sixchoose'] = $option['option'];
								break;
							case 71:
								$data['sevchoose'] = $option['option'];
								break;
							case 72:
								$data['eightchoose'] = $option['option'];
								break;
							case 73:
								$data['ninchoose'] = $option['option'];
								break;
							case 74:
								$data['tenchoose'] = $option['option'];
								break;
							case 75:
								$data['elechoose'] = $option['option'];
								break;
							case 76:
								$data['twchoose'] = $option['option'];
								break;
							
							
						}
					}
                    if ($r == false) {
						 echo_json('0', '操作失败', '投票失败！','', '2');
                    }
                }
            }
        } else {
			 echo_json('0', '操作失败', '您未选择任何选项！','', '2');
        }
		if($flag == "energy"){
			$parameter = $_POST['village'];
			$parameter_1 = '"'.$_POST['building'].'"';
			$result = D('Survey_address')->field('COMMUNITYNAME')->where("COMMUNITYCODE = $parameter")->group('COMMUNITYCODE')->select();
			$result_1 = D('Survey_address')->field('BUILDINGNAME')->where("BUILDINGCODE = $parameter_1")->group('BUILDINGCODE')->select();
			$data['address'] = $result[0]['COMMUNITYNAME'].'小区'.$result_1[0]['BUILDINGNAME'].$_POST['uite'].'单元'.$_POST['floor'].'层'.$_POST['number'].'号';
			$data['content'] = $_POST['content'];
			
			$data['username'] = $_POST['username'];
			$data['phone'] = $_POST['telephone'];
			$data['createtime'] = time();
			$re = D('Survey_energy1')->add($data);
		}
		
		echo_json('1', '操作成功', '投票成功！', U('Vote/index','id=21'), '2');
    }

	 public function save8() {
		
		$flag = $_POST['flag'];
		$voteid = intval($_REQUEST['id']);
		$vote = R('Vote/valid', array($voteid));
		if(empty($vote)){
			echo_json('0', '操作失败', '此次调查已经结束，感谢您的关注！','', '2');
		}

		
		/*if($_SESSION['isvoted'] ==1){
			echo_json('0', '操作失败', '您已经投过票，请勿反复提交','', '2');
		}*/
		
        
        $m_vote_option = M('vote_option');
		$survey = M('Vote_dust');
        $optionid = $_REQUEST['optionid'];
        if (!empty($optionid)) {
            foreach ($optionid as $key => $val) {
                foreach ($optionid[$key] as $k => $v) {
                    $r = $m_vote_option->where('id =' . $v)->setInc('poll', 1);
					if($flag == "dust"){
						$option = $m_vote_option->where('id =' . $v)->find();
						
						 switch ($option['listid']) {
							case 77:
								$data['firchoose'] = $option['option'];
								break;
							case 78:
								$data['secchoose'] = $option['option'];
								break;
							case 79:
								$data['thichoose'] = $option['option'];
								break;
							case 80:
								$data['fourthchoose'] = $option['option'];
								break;
							case 81:
								$data['fifchoose'] = $option['option'];
								break;
							case 82:
								$choose_82 .= $option['option'].",";
								$data['sixchoose'] = $choose_82;
								continue;
							case 83:
								$choose_83 .= $option['option'].",";
								$data['sevchoose'] = $choose_83;
								continue;
								
							case 84:
								$choose_84 .= $option['option'].",";
								$data['eightchoose'] = $choose_84;
								continue;
							case 85:
								$choose_85 .= $option['option'].",";
								$data['ninchoose'] = $choose_85;
								continue;
								
							case 86:
								$choose_86 .= $option['option'].",";
								$data['tenchoose'] = $choose_86;
								continue;
								
						}
					}
                    if ($r == false) {
						 echo_json('0', '操作失败', '投票失败！','', '2');
                    }
                }
            }
        } else {
           // $this->error('您未选择任何选项！');
			 echo_json('0', '操作失败', '您未选择任何选项！','', '2');
        }
		if($flag == "dust"){
			$data['housename'] = $_POST['village_name'];
			$data['content'] = $_POST['content'];
			$data['createtime'] = time();
			$re = D('Vote_dust')->add($data);
		}
		$_SESSION['isvoted']= 1;
		echo_json('1', '操作成功', '投票成功！', U('Vote/index','id=21'), '2');
//        $this->success('投票成功！', '__URL__/result/voteid/' . $voteid);
    }


//2018年生态城公用事业满意度调查问卷保存
	public function save9() {
		$flag = $_POST['flag'];
		$voteid = $_POST['id'];
		
		$m_vote_option = M('vote_option');
		$survey = M('Vote_cause');
        $optionid = $_REQUEST['optionid'];
		
		$vote = R('Vote/valid', array($voteid));
		if(empty($vote)){
			echo_json('0', '操作失败', '此次调查已经结束，感谢您的关注！','', '2');
		}

		$map['rDistrict'] = $_POST['village'].'小区';
		$map['customerName'] = $_POST['customerName'];
		$map['telephone'] = $_POST['telephone'];
		$map['roomNumber'] = $_POST['roomNumber'];
        $count = D('Vote_cause')->where($map)->count();
		
		if($count == 1){
			echo_json('0', '操作失败', '您已经投过票，请勿反复提交','', '10');
		}
		
		$first = $_POST['first'];
		
        if (!empty($optionid)) {
            foreach ($optionid as $key => $val) {
                foreach ($optionid[$key] as $k => $v) {
                   $r = $m_vote_option->where('id =' . $v)->setInc('poll', 1);
					if($flag == "cause"){
						$option = $m_vote_option->where('id =' . $v)->find();
						 switch ($option['listid']) {
														case 91:
								$parameter = $option['option'];
								$data['twChoose'] = $option['option'];//自来水供应
								break;
							case 92:
								$data['ngChoose'] = $option['option'];//天然气供应
								break;
							case 93:
								$data['hsChoose'] = $option['option'];//供热供应
								break;
							case 94:
								$data['rwChoose'] = $option['option'];//再生水	
								break;
							case 95:
								$data['wqChoose'] = $option['option'];//水体水质
								break;
							case 96:
								$data['mfChoose'] = $option['option'];//道路、桥梁、排水、路灯等市政设施
								break;
							case 97:
								$data['lgChoose'] = $option['option'];//景观绿化
								break;
							case 98:
								$data['rcChoose'] = $option['option'];//道路保洁与环境卫生
								break;
							case 99:
								$data['agentChoose'] = $option['option'];//智能物回（积分兑换、大件回收等）
								break;
							case 100:
								$data['busgChoose'] = $option['option'];//公交车辆的安全保障
								break;
							case 101:
								$data['bdrChoose'] = $option['option'];//公交车辆的驾驶员的仪容仪表和服务态度
								break;
							case 102:
								$data['cleanupChoose'] = $option['option'];//车身及车内部的清洁卫生
								break;
							
						}
					}
                    if ($r == false) {
						 echo_json('0', '操作失败', '投票失败！','', '2');
                    }
                }
            }
        } else {
			 echo_json('0', '操作失败', '您未选择任何选项！','', '2');
        }
		if($flag == "cause"){
			//$parameter = $_POST['village'];
			//$parameter_1 = '"'.$_POST['building'].'"';
			//$result = D('Survey_address')->field('COMMUNITYNAME')->where("COMMUNITYCODE = $parameter")->group('COMMUNITYCODE')->select();
			//$result_1 = D('Survey_address')->field('BUILDINGNAME')->where("BUILDINGCODE = $parameter_1")->group('BUILDINGCODE')->select();
			
			$data['rDistrict'] = $_POST['village'].'小区';
			
			$data['opinionChoose'] = $_POST['content'];
			
			$data['customerName'] = $_POST['customerName'];
			$data['telephone'] = $_POST['telephone'];
			$data['roomNumber'] = $_POST['roomNumber'];
			$data['createtime'] = time();
			$data['year'] = date('Y');
			$re = M('Vote_cause')->add($data);
		}
		echo_json('1', '操作成功', '投票成功！', U('Vote/index','id=21'), '100');
    }
//	*******************************************************************************************



	
	
	//判断验证码是否显示
	public function vercode($code) {
		$map['housecode'] = $code;
		$ret = D('Survey_code')->where($map)->select();
		$flag = 1;
		if(empty($ret[0]['housecode'])){
			$flag = 0;
		}
		echo $this->ajaxReturn($flag,'JSON');
		
	}

    public function result() {

        $m_vote = D('Vote');
        $m_vote_list = M('vote_list');
        $m_vote_option = M('vote_option');

        $voteid = intval($_REQUEST['voteid']);
        $vote = $m_vote->where("id = $voteid")->find();
        $votelist = $m_vote_list->where("voteid = $voteid")->select();
        foreach ($votelist as $key => $val) {
            $listid = $votelist[$key]['listid'];
            $option[$listid] = $m_vote_option->where("listid = $listid")->select();
            $sum[$listid] = $m_vote_option->where("listid = $listid")->sum('poll');
            foreach ($option[$listid] as $k => $v) {
                $width = 200;  //外层DIV宽度
                $option[$listid][$k]['percent'] = round($option[$listid][$k]['poll'] / $sum[$listid] * 100, 2); //所占比例
                $ratio = ($option[$listid][$k]['poll'] / $sum[$listid]) * $width;
                $option[$listid][$k]['ratio'] = $ratio;
            }
        }

        $data = array(
            'vote' => $vote,
            'votelist' => $votelist,
            'option' => $option,
        );
        $this->assign($data);
         //左侧导航
//        $this->pid = D('Cate')->where('id=' . $vote['cat_id'])->getField('pid'); //控制左侧子菜单
        $this->nav = $this->nav($this->cate, $vote['cat_id'], $voteid,'Vote'); //当前位置
        $p = get_parents($this->cate, $vote['cat_id']);
		if(empty($p)){
			$this->redirect("Public/404");
		}
        $this->topid = $p[0]['id']; //顶部导航选中效果
        $this->display();
    }

}

?>
