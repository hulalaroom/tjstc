<?php

class ContractAction extends HomeAction {
	public function _empty(){
		$this->redirect("Public/404");
	}

    //合同预约登记lists方法
    public function lists() {
        $this->check();
        $this->id = intval($this->_param('id'));
        $this->nav = $this->nav($this->cate, $this->id, ''); //当前位置
        
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
        //$map['userid'] = array('eq', $userid);
        import("ORG.Util.Page");
        $count = D('Contract')->where($map)->count();
        $page = new Page($count, C('PAGE_SIZE'));
        $page->setConfig('first', '首页');
        $page->setConfig('last', '末页');
        $page->setConfig('theme', ' %upPage%  %first%  %prePage%  %linkPage%  %nextPage% %end%%downPage%<span>%totalRow%条</span> <span>%nowPage%/%totalPage% 页</span>');
        $list = D('Contract')->where($map)->limit($page->firstRow . ',' . $page->listRows)->order('ID DESC')->select();
        $this->assign('list', $list);
        $show = $page->show();
        $this->assign('page', $show);

        $this->display();
    }

    //合同预约登记默认方法
    public function index() {
		$_SESSION['varname'] = "contract";
        $this->check();
		$houselist = D('UserRoom')->where('uid=' . $_SESSION['uid'])->field('houseCode,houseName')->select(); //用户绑定房间
		if(empty($houselist)) {
			$this->error('您没有绑定房间，不能进行合同预约登记', '/index.php?s=/index/index');
		}
        foreach ($houselist as $v) {
            $hlist[] = $v['houseCode'];
			$hlist[] = $v['houseName'];
        }
        $this->id = intval($this->_param('id'));
        $this->pid = D('Cate')->where('id=' . $this->id)->getField('pid'); //左侧导航
        $this->nav = $this->nav($this->cate, $this->id, ''); //当前位置
        $userid = intval($_SESSION['uid']);
        $info = D('User')->where(' id = ' . $userid)->find();
		$this->houselist = $houselist;
        $this->assign('nickname', $info['nickname']);
        $this->assign('phone', $info['phone']);
        $this->display();
    }

    //合同预约登记提交方法
    public function insert() {
        $this->check();
		//$userinfo = D('user')->where('id = ' . $_SESSION['uid'])->find();
		//$data['create_time'] = time();
		//数据获取
		$houseCode = $_POST['houseCode'];
		$ownername = trim($_POST['ownername']);
		$papercode = trim($_POST['papercode']);
		$phone = trim($_POST['phone']);
		$fixedPhone = trim($_POST['fixedPhone']);
		$workunit = trim($_POST['workunit']);
		$unitAddressPhone = trim($_POST['unitAddressPhone']);
		$familyNamePhone = trim($_POST['familyNamePhone']);
		$builtUpArea = trim($_POST['builtUpArea']);
		$poolArea = trim($_POST['poolArea']);
		$floorHeight = trim($_POST['floorHeight']);
		$sleeveBuiltUpArea = trim($_POST['sleeveBuiltUpArea']);
		$ultraHighArea = trim($_POST['ultraHighArea']);
		$heatingArea = trim($_POST['heatingArea']);
		
		$Contract = M('Contract');
		$Contract_back = M('Contract_back');
		
		$data['housecode'] = $houseCode;
		$data['ownername'] = $ownername;
		$data['papercode'] = $papercode;
        $data['phone'] = $phone;
		$data['FixedPhone'] = $fixedPhone;
		$data['workunit'] = $workunit;
		$data['UnitAddressPhone'] = $unitAddressPhone;
		$data['FamilyNamePhone'] = $familyNamePhone;
		$data['BuiltUpArea'] = $builtUpArea;
		$data['PoolArea'] = $poolArea;
		$data['FloorHeight'] = $floorHeight;
		$data['SleeveBuiltUpArea'] = $sleeveBuiltUpArea;
		$data['UltraHighArea'] = $ultraHighArea;
		$data['HeatingArea'] = $heatingArea;
        //$data['userid'] = $_SESSION['uid'];
		$data['create_time'] = strtotime("now");
		
		/*if(empty($b_title)){
			echo_json('0', '操作失败', '标题不可为空', '', '3');
		}
		if(empty($b_content)){
			echo_json('0', '操作失败', '内容不可为空', '', '3');
		}*/
		$r = $Contract->add($data);
		$c = $Contract_back->add($data);
        if ($r == false || $c == false) {
            echo_json('0', '登记失败,请重新登记', '登记失败,请重新登记', '', '10');
        }
		
        echo_json('1', '登记成功,审核需要10个工作日左右,请耐心等待', '登记成功,审核需要10个工作日左右,请耐心等待', U('Index/index'), '10');
    }

    //预览
    function view() {
        $id = intval($this->_param('id'));
        $content = D('Contract')->where('id=' . $id)->find();
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
