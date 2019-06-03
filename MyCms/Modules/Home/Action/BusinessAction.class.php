<?php

class BusinessAction extends HomeAction
{
    // 空操作
    public function _empty()
    {
        $this->redirect("Public/404");
    }

    // 投诉建议lists方法
    public function lists()
    {
        $this->check();
		$houselist = R('User/fjbd', array($_SESSION['uid']));//用户绑定房间
		if(empty($houselist)) {
			$this->error('您没有绑定房间，不能进行业务办理!', '/index.php?s=/user/bind.html');
		}
        $this->id = intval($this->_param('id'));
        $this->nav = $this->nav($this->cate, $this->id, ''); // 当前位置
        $p = get_parents($this->cate, $this->id);
        if (empty($p)) {
            $this->redirect("Public/404");
        }
        $this->topid = $p [0] ['id']; // 顶部导航选中效果

        if (is_numeric($this->id)) {
            $c = get_childsid($this->cate, $this->id);
            $cids = $this->id;
            foreach ($c as $v) {
                $cids .= "," . $v;
            }
            //$map['cat_id'] = array('in', $cids);
        }

        $userid = $_SESSION ['uid'];
        $cat_id = intval($_GET ['id']);

        $map['userid'] = array('eq', $userid);
        import("ORG.Util.Page");

		//$houselist = R('User/fjbh', array($_SESSION['uid']));
        //$houseCode = implode(",", $houselist);

		//$houseCode = '1001-001-01-21-01';
		$list = R('Api/callMyBusinessListInterface', array($_SESSION['uid']));
		$list = json_decode(json_encode($list),true);

        $nylist = R('Api/callMyBusinessListSwInterface', array($_SESSION['uid']));
        $nylist = json_decode(json_encode($nylist),true);

		$hblist = R('Api/callMyBusinessListHBServlet', array($_SESSION['uid']));
        $hblist = json_decode(json_encode($hblist),true);

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

		//$count = D('Business')->where($map)->count();

        //$page = new Page($count, C('PAGE_SIZE'));
        //$page->setConfig('first', '首页');
        //$page->setConfig('last', '末页');
        //$page->setConfig('theme', ' %upPage%  %first%  %prePage%  %linkPage%  %nextPage% %end%%downPage%<span>%totalRow%条</span> <span>%nowPage%/%totalPage% 页</span>');

		//$list = D('Business')->where($map)->limit($page->firstRow . ',' . $page->listRows)->order('id  DESC')->select();
		//dump(D('Business')->getLastSql());

		$this->assign('nyywList', $list['r_body']);
        $this->assign('swywList', $nylist['r_body']);
		$this->assign('hbywlist', $hblist['r_body']);

		//$show = $page->show();
        //$this->assign('page', $show);
        $this->display();
    }

    public function listDetail()
    {
        $this->check();
        $houselist = R('User/fjbd', array($_SESSION['uid']));//用户绑定房间
        if(empty($houselist)) {
            $this->error('您没有绑定房间，不能进行业务办理!', '/index.php?s=/user/bind.html');
        }
        /*$this->id = intval($this->_param('id'));
        $this->nav = $this->nav($this->cate, $this->id, ''); // 当前位置
        $p = get_parents($this->cate, $this->id);
        if (empty($p)) {
            $this->redirect("Public/404");
        }
        $this->topid = $p [0] ['id']; // 顶部导航选中效果

        if (is_numeric($this->id)) {
            $c = get_childsid($this->cate, $this->id);
            $cids = $this->id;
            foreach ($c as $v) {
                $cids .= "," . $v;
            }
            //$map['cat_id'] = array('in', $cids);
        }*/

        $Businesscode = $_GET['Businesscode'];
        $Businesstype = $_GET['Businesstype'];
        $list = R('Api/GetMyBusinessDetailServlet', array($Businesscode,$Businesstype));
        if($list->{'r_body'}[0]->{'BANK'}){
            $list->{'r_body'}[0]->{'CARDENDDATE'} = substr($list->{'r_body'}[0]->{'CARDENDDATE'}, 0,2).'月'.substr($list->{'r_body'}[0]->{'CARDENDDATE'}, -2).'年';
        }else{
            $list->{'r_body'}[0]->{'CHARGEMONTH_TO'} = $list->{'r_body'}[0]->{'CHARGEMONTH'}+1;
        }
		//header('content-type:text/html;charset=utf-8');
        //print_r($list);exit;
        $list = json_decode(json_encode($list),true);
        $this->assign('Businesstype', $Businesstype);
        $this->assign('nyywList', $list['r_body']);
        $this->display();
    }

    public function listSwDetail()
    {
        $this->check();
        $houselist = R('User/fjbd', array($_SESSION['uid']));//用户绑定房间
        if(empty($houselist)) {
            $this->error('您没有绑定房间，不能进行业务办理!', '/index.php?s=/user/bind.html');
        }
        /*$this->id = intval($this->_param('id'));
        $this->nav = $this->nav($this->cate, $this->id, ''); // 当前位置
        $p = get_parents($this->cate, $this->id);
        if (empty($p)) {
            $this->redirect("Public/404");
        }
        $this->topid = $p [0] ['id']; // 顶部导航选中效果

        if (is_numeric($this->id)) {
            $c = get_childsid($this->cate, $this->id);
            $cids = $this->id;
            foreach ($c as $v) {
                $cids .= "," . $v;
            }
            //$map['cat_id'] = array('in', $cids);
        }*/

        $Businesscode = $_GET['Businesscode'];
        $Businesstype = $_GET['Businesstype'];
        $list = R('Api/GetMyBusinessDetailSwServlet', array($Businesscode,$Businesstype));//print_r($list);
        if($list->{'r_body'}[0]->{'BANK'}){
            $list->{'r_body'}[0]->{'CARDENDDATE'} = substr($list->{'r_body'}[0]->{'CARDENDDATE'}, 0,2).'月'.substr($list->{'r_body'}[0]->{'CARDENDDATE'}, -2).'年';
        }else{
            $list->{'r_body'}[0]->{'CHARGEMONTH_TO'} = $list->{'r_body'}[0]->{'CHARGEMONTH'}+1;
        }
        //print_r($list);
        $list = json_decode(json_encode($list),true);
        $this->assign('nyywList', $list['r_body']);
		$this->assign('businesstype',$Businesstype);
        $this->display();
    }

    // 维修预约默认方法
    public function bx()
    {
        $this->check();
		$houselist = R('User/fjbd', array($_SESSION['uid']));//用户绑定房间
		if(empty($houselist)) {
			$this->error('您没有绑定房间，不能进行维修预约', '/index.php?s=/user/bind.html');
		}
        $this->id = intval($this->_param('id'));
        $this->pid = D('Cate')->where('id=' . $this->id)->getField('pid'); // 左侧导航
        $this->nav = $this->nav($this->cate, $this->id, ''); // 当前位置
        $p = get_parents($this->cate, $this->id);
        if (empty($p)) {
            $this->redirect("Public/404");
        }
        $this->topid = $p [0] ['id']; // 顶部导航选中效果
        /* 能源类型 */
        //$this->powertype = R('Api/getPowerType');

        // 涉及行业
        $industrylist = array();
        $data = R('Api/getIndustryInfos', array('ItemCodeServlet', ''));
        if (is_array($data) && 0 === $data['error']) {
            $tt = json_decode($data['ret']);
            if ('00' == $tt->r_code) {
                $industrylist = $tt->r_body;
            }
        }
        $this->assign('industrylist', $industrylist);

		// 涉及种类
        $industrytype = array();
        $data = R('Api/getIndustryInfos', array('ItemCodeServletTwo', $industrylist[0]));
        if (is_array($data) && 0 === $data['error']) {
            $tt = json_decode($data['ret']);

            if ('00' == $tt->r_code) {
                foreach ($tt->r_body as $type) {
                    $industrytype[] = array('itemCode' => $type->ItemCode, 'itemName' => $type->ItemName);
                }
            }
        }

        $this->assign('industrytype', $industrytype);

        /*查找已绑定的房间 */
        $this->houselist = R('User/fjbd', array($_SESSION['uid']));


        $userid = intval($_SESSION ['uid']);
        $info = D('User')->where(' id = ' . $userid)->find();
        $this->assign('nickname', $info ['nickname']);
        $this->assign('cat_id', $this->id);
        $this->assign('phone', $info ['phone']);
        $this->display();
    }

    //开通
    public function kt()
    {
        $this->check();
		$houselist = R('User/fjbd', array($_SESSION['uid']));//用户绑定房间
		if(empty($houselist)) {
			$this->error('您没有绑定房间，不能进行开通预约', '/index.php?s=/user/bind.html');
		}
        $this->id = intval($this->_param('id'));
        $this->pid = D('Cate')->where('id=' . $this->id)->getField('pid'); // 左侧导航
        $this->nav = $this->nav($this->cate, $this->id, ''); // 当前位置
        $p = get_parents($this->cate, $this->id);
        if (empty($p)) {
            $this->redirect("Public/404");
        }
        $this->topid = $p [0] ['id']; // 顶部导航选中效果
        /* 能源类型 */
        //$this->powertype = R('Api/getPowerType');
        // 涉及行业
        $industrylist = array();
        $data = R('Api/getIndustryInfos', array('ItemCodeServlet', ''));
        if (is_array($data) && 0 === $data['error']) {
            $tt = json_decode($data['ret']);
            if ('00' == $tt->r_code) {
                $industrylist = $tt->r_body;
            }
        }

        $this->assign('industrylist', $industrylist);

        $industrytype = array();
        $data = R('Api/getIndustryInfos', array('ItemCodeServletTwo', $industrylist[0]));
        if (is_array($data) && 0 === $data['error']) {
            $tt = json_decode($data['ret']);
            if ('00' == $tt->r_code) {
                foreach ($tt->r_body as $type) {

                    $industrytype[] = array('itemCode' => $type->ItemCode, 'itemName' => $type->ItemName);
                }
            }
        }

        $this->assign('industrytype', $industrytype);

        /*查找已绑定的房间 */
        $this->houselist = R('User/fjbd', array($_SESSION['uid']));

        $userid = intval($_SESSION ['uid']);
        $info = D('User')->where(' id = ' . $userid)->find();
        $this->assign('nickname', $info ['nickname']);
        $this->assign('cat_id', $this->id);
        $this->assign('phone', $info ['phone']);
        $this->display();
    }

    //停供
    public function tg()
    {
        $this->check();
        $this->id = intval($this->_param('id'));
        $this->pid = D('Cate')->where('id=' . $this->id)->getField('pid'); // 左侧导航
        $this->nav = $this->nav($this->cate, $this->id, ''); // 当前位置
        $p = get_parents($this->cate, $this->id);
        if (empty($p)) {
            $this->redirect("Public/404");
        }
        $this->topid = $p [0] ['id']; // 顶部导航选中效果
        /* 能源类型 */
        //$this->powertype = R('Api/getPowerType');
        /* 查找已绑定的房间 */
        $this->houselist = D('UserRoom')->where('uid=' . $_SESSION['uid'])->select();

        // 涉及行业
        $industrylist = array();
        $data = R('Api/getIndustryInfos', array('ItemCodeServlet', ''));
        if (is_array($data) && 0 === $data['error']) {
            $tt = json_decode($data['ret']);
            if ('00' == $tt->r_code) {
                $industrylist = $tt->r_body;
            }
        }

        $this->assign('industrylist', $industrylist);

        $industrytype = array();
        $data = R('Api/getIndustryInfos', array('ItemCodeServletTwo', $industrylist[0]));
        if (is_array($data) && 0 === $data['error']) {
            $tt = json_decode($data['ret']);
            if ('00' == $tt->r_code) {
                foreach ($tt->r_body as $type) {
                    $industrytype[] = array('itemCode' => $type->ItemCode, 'itemName' => $type->ItemName);
                }
            }
        }

        $this->assign('industrytype', $industrytype);

        $userid = intval($_SESSION ['uid']);
        $info = D('User')->where(' id = ' . $userid)->find();
        $this->assign('nickname', $info ['nickname']);
        $this->assign('cat_id', $this->id);
        $this->assign('phone', $info ['phone']);
        $this->display();
    }

    public function insert()
    {
        $this->check();
        $data = D('Business')->create();
        $data ['create_time'] = time();
        //$data ['appoint_time'] = strtotime($data ['appoint_time']);
        // $data['updatetime'] = time();
        $userinfo = D('user')->where('id = ' . $_SESSION ['uid'])->find();
        if (empty($_POST ['linkman'])) {
            $data ['linkman'] = $userinfo ['nickname'];
        }
        if (empty($_POST ['phone'])) {
            $data ['phone'] = $userinfo ['phone'];
        }

        $data ['cat_id'] = intval($_POST ['cat_id']);
		if(($_POST ['cat_id'] == 78)){
			$data ['cat_id'] = 15;
		}
		if(($_POST ['cat_id'] == 80) or ($_POST ['cat_id'] == 86) or($_POST ['cat_id'] == 91)){
			$data ['cat_id'] = 17;
		}

        $data ['userid'] = $_SESSION ['uid'];
        $data ['itemCode'] = $_POST ['itemCode'];
        $b_title = trim($data ['title']);
        $b_summary = trim($data ['summary']);

        /*if (empty($b_title)) {
            echo_json('0', '操作失败', '标题不可为空', '', '3');
        }*/
        if (empty($b_summary)) {
            echo_json('0', '操作失败', '内容不可为空', '', '3');
        }

         if (!empty($data['housecode'])) {
             /*绑定验证*/
             $list = R('Api/checkRoom', array($data['housecode'], $data['itemCode'], $_SESSION ['uname'], $_POST['typeCode']));
             if ($list['error'] == '1') {
                 echo_json('0', '操作失败', '您还没有绑定房间,不能进行预约', U('Business/lists', 'id=10'), '3');
             }
             switch ($list[0]) {
                 case '00':
                     $r = D('business')->add($data);
                     if ($r == false) {
                         echo_json('0', '操作失败', '操作失败', '', '2');
                     } else {
                         echo_json('1', '操作成功', '操作成功', U('Business/lists', 'id=10'), '2');
                     }
                     break;
                 case '01':
                     echo_json('0', '操作失败', '非法访问', '', '2');
                     break;
                 case '02':
                     echo_json('0', '操作失败', '没有绑定能源卡号', '', '2');
                     break;
                 case '03':
                     echo_json('0', '操作失败', '没有绑定房间编号', '', '2');
                     break;
                 case '04':
                     echo_json('0', '操作失败', '您的房间有欠费', '', '2');
                     break;
             }
         } else {
            $r = D('business')->add($data);
            if ($r == false) {
                echo_json('0', '操作失败', '操作失败', '', '2');
            }
            echo_json('1', '操作成功', '操作成功', U('Business/lists', 'id=10'), '2');
        }


    }

    // 预览(开通预约和维修愉悦)
    function view()
    {
        $id = intval($this->_param('id'));
        $content = D('Business')->where('id=' . $id)->find();
        if (empty($content)) {
            $this->redirect("Public/404");
        }
        $this->assign($content);
        $this->display();
    }

	 // 评价预览
    function pj()
    {
        $id = intval($this->_param('id'));
        $content = D('Business')->where('id=' . $id)->find();
        if (empty($content)) {
            $this->redirect("Public/404");
        }
        $this->assign($content);
        $this->display();
    }

	// 评价内容页
    function pjview()
    {
    	$this->display();
    }

	//提交评价
	 function save()
    {
		//数据获取
		$blsd = $_POST['blsd'];
		dump($blsd);
		return;
		$LinkMan = trim($_POST['lxr']);
		$OldLinkMan = trim($_POST['oldlianxiren']);
		$LinkTel = trim($_POST['lxdh']);


		echo_json('1', '提交成功', '提交成功', U('Business/lists', 'id=10'), '2');
    }

    //上传头像
    public function upimg()
    {
        header('Content-Type:text/html; charset=utf-8');
        $checklogin = $this->_param('checklogin');
        if (empty($checklogin)) {
            exit();
        }
        import('@.ORG.UploadFile');
        $upload = new UploadFile(); // 实例化上传类
        $upload->maxSize = 2 * 1024 * 1024; //设置上传图片的大小2mb
        $upload->allowExts = array('jpg', 'png', 'gif'); //设置上传图片的后缀
        // $upload->uploadReplace = true;     //同名则替换
        $upload->saveRule = 'uniqid'; //设置上传头像命名规则(临时图片),修改了UploadFile上传类
        //完整的头像路径
        $path = './Uploads/avatar/';
        $upload->savePath = $path;
        if (!$upload->upload()) { // 上传错误提示错误信息
            $this->ajaxReturn('', $upload->getErrorMsg(), 0, 'json');
        } else { // 上传成功 获取上传文件信息
            $info = $upload->getUploadFileInfo();
            echo $path . $info[0]['savename'];
        }
    }

    function special()
    {
        $this->id = intval($this->_param('id'));
        $this->pid = D('Cate')->where('id=' . $this->id)->getField('pid'); //左侧导航
        $this->nav = $this->nav($this->cate, $this->id, ''); //当前位置
        $p = get_parents($this->cate, $this->id);
        if (empty($p)) {
            $this->redirect("Public/404");
        }

        $map['cat_id'] = $this->id;
        $map['status'] = 1;

        //文章列表
        import("ORG.Util.Page");
        $count = D('Article')->where($map)->count();
        $sql = D('Article')->getLastSql();
        $page = new Page($count, C('PAGE_SIZE'));
        $page->setConfig('first', '首页');
        $page->setConfig('last', '末页');
        $page->setConfig('theme', ' %upPage%  %first%  %prePage%  %linkPage%  %nextPage% %end%%downPage%<span>%totalRow%条</span> <span>%nowPage%/%totalPage% 页</span>');
        $art_list = D('Article')->where($map)->limit($page->firstRow . ',' . $page->listRows)->order('id DESC')->select();

        $this->assign('art_list', $art_list);
        $show = $page->show();
        $this->assign('page', $show);

        $this->display();
    }

	function special1()
    {
        $this->id = intval($this->_param('id'));
        $this->pid = D('Cate')->where('id=' . $this->id)->getField('pid'); //左侧导航
        $this->nav = $this->nav($this->cate, $this->id, ''); //当前位置
        $p = get_parents($this->cate, $this->id);
        if (empty($p)) {
            $this->redirect("Public/404");
        }

        $map['cat_id'] = $this->id;
        $map['status'] = 1;

        //文章列表
        import("ORG.Util.Page");
        $count = D('Article')->where($map)->count();
        $sql = D('Article')->getLastSql();
        $page = new Page($count, C('PAGE_SIZE'));
        $page->setConfig('first', '首页');
        $page->setConfig('last', '末页');
        $page->setConfig('theme', ' %upPage%  %first%  %prePage%  %linkPage%  %nextPage% %end%%downPage%<span>%totalRow%条</span> <span>%nowPage%/%totalPage% 页</span>');
        $art_list = D('Article')->where($map)->limit($page->firstRow . ',' . $page->listRows)->order('id DESC')->select();

        $this->assign('art_list', $art_list);
        $show = $page->show();
        $this->assign('page', $show);

        $this->display();
    }

    function getIndustryType() {
        $idty = $_POST['idty'];
        $data = R('Api/getIndustryInfos', array('ItemCodeServletTwo', $idty));
        if (is_array($data) && 0 === $data['error']) {
            $tt = json_decode($data['ret']);
            if ('00' == $tt->r_code) {
                echo $data['ret'];
            }
        } else {
            echo json_encode(array('r_code' => '01'));
        }
        exit();
    }
}

?>
