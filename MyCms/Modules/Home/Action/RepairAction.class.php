<?php

class RepairAction extends HomeAction {
	public function _empty(){
		$this->redirect("Public/404");
	}

    //大件物品预约登记lists方法
    public function lists() {
        $this->check();
       $this->id = $this->_get('id');
				$pid = $this->_get('pid');
				if (empty($pid)) {
	            $this->pid = D('Cate')->where('id=' . $this->id)->getField('pid'); //控制左侧子菜单
	            $this->nav = $this->nav($this->cate, $this->id, ''); //当前位置
	            $p = get_parents($this->cate, $this->id);
				}
				else{
							$this->pid = $this->_get('pid');
	            $this->nav = $this->nav($this->cate, $this->pid, ''); //当前位置
	            $p = get_parents($this->cate, $this->pid);
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
        $count = D('Repair')->where($map)->count();
        $page = new Page($count, C('PAGE_SIZE'));
        $page->setConfig('first', '首页');
        $page->setConfig('last', '末页');
        $page->setConfig('theme', ' %upPage%  %first%  %prePage%  %linkPage%  %nextPage% %end%%downPage%<span>%totalRow%条</span> <span>%nowPage%/%totalPage% 页</span>');
        $list = D('Repair')->where($map)->limit($page->firstRow . ',' . $page->listRows)->order('ID DESC')->select();
        $this->assign('list', $list);
        $show = $page->show();
        $this->assign('page', $show);
        $this->display();
    }

    //终端预约登记默认方法
    public function index() {
		$_SESSION['varname'] = "Repair";
        $this->check();
		//$this->Repair_time = date('Y-m-d H:i:s',time());
		$houselist = R('User/fjbd', array($_SESSION['uid']));//用户绑定房间
		if(empty($houselist)) {
			$this->error('您没有绑定房间，不能进行终端维修预约登记', '/index.php?s=/user/bind.html');
		}
        foreach ($houselist as $v) {
            $hlist[] = $v['houseCode'];
			$hlist[] = $v['houseName'];
        }
        $this->id = intval($this->_param('id'));
        $this->pid = D('Cate')->where('id=' . $this->id)->getField('pid'); //左侧导航
        $this->nav = $this->nav($this->cate, 138, ''); //当前位置
        $userid = intval($_SESSION['uid']);
        $info = D('User')->where(' id = ' . $userid)->find();
		$this->houselist = $houselist;
        $this->assign('nickname', $info['nickname']);
        $this->assign('phone', $info['phone']);
        $this->display();
    }

    //大件物品预约登记提交方法
    public function insert() {
        $this->check();
		//$userinfo = D('user')->where('id = ' . $_SESSION['uid'])->find();
		//$data['create_time'] = time();
		//数据获取
		$houseCode = $_POST['houseCode'];
		$RepairMan = trim($_POST['RepairMan']);
		$Repair_time = strtotime(date('Y-m-d H:i:s',time()));
		$Address = trim($_POST['Address']);
		$ContactInformation = trim($_POST['ContactInformation']);
		$FaultDescription = trim($_POST['FaultDescription']);
		
		$Repair = M('Repair');
		$Repair_back = M('Repair_back');
		
		$data['housecode'] = $houseCode;
		$data['RepairMan'] = $RepairMan;
		$data['Repair_time'] = $Repair_time;
        $data['RepairTel'] = $ContactInformation;
		$data['Address'] = $Address;
		$data['FaultDescription'] = $FaultDescription;
        $data['userid'] = $_SESSION['uid'];
		//$data['create_time'] = strtotime("now");
		
		/*if(empty($b_title)){
			echo_json('0', '操作失败', '标题不可为空', '', '3');
		}
		if(empty($b_content)){
			echo_json('0', '操作失败', '内容不可为空', '', '3');
		}*/
		$r = $Repair->add($data);
        if ($r == false) {
            echo_json('0', '预约失败,请重新预约', '预约失败,请重新预约', '', '2');
        }
		
        echo_json('1', '预约成功', '预约成功', U('Repair/lists','id=138'), '2');
    }

    //预览(终端维修预约)
    function view() {
        $id = intval($this->_param('id'));
        $content = D('Repair')->where('id=' . $id)->find();
		if(empty($content)){
			$this->redirect("Public/404");
		}
        $this->assign($content);
        $this->display();
    }

    public function verify() {
        $type = isset($_GET['type']) ? $_GET['type'] : 'gif';
        import("ORG.Util.Image");
        Image::buildImageVerify(4, 1, $type);
    }

}

?>
