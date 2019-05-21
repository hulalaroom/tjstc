<?php

class ApplyAction extends HomeAction {
	public function _empty(){
		$this->redirect("Public/404");
	}

    //绑定房间申请记录lists方法
    public function lists() {
        $this->check();
		$houseid = $this->_get('id');
		$housecode = $this->_get('housecode');
        $userid = $_SESSION['uid'];
        $map['toid'] = array('eq', $userid);
       
        $count = D('user_message')->where($map)->count();
		if($count !=0 && $count!="" && $count!=NULL){
			$messagelist = D('user_message')->where($map)->field('fromid')->order('id DESC')->group('fromid')->select();
			foreach($messagelist as $ml){
				$user['id'] = array('eq', $ml['fromid']);
				$userlist[] = D('user')->where($user)->order('id DESC')->select();
			}
		}
		else{
			$userlist = "";
		}
		$user = D('user'); 
        $content = $user->join('ad_user_room as ur on ad_user.id = ur.uid')->field('ur.houseName')->where('ur.uid=' . $userid . ' and ur.id=' . $houseid . ' and ur.ifBind=1')->select();
		$this->assign('housename', $content[0]['houseName']);
        $this->assign('list', $userlist);
        $this->display();
    }

    //绑定提交
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
            echo_json('0', '登记失败,请重新登记', '登记失败,请重新登记', '', '2');
        }
		
        echo_json('1', '登记成功,审核需要10个工作日左右,请耐心等待', '登记成功,审核需要10个工作日左右,请耐心等待', U('Index/index'), '2');
    }

    //申请绑定用户信息预览
    function view() {
        $id = intval($this->_param('id'));
        $content = D('User')->where('id=' . $id)->find();
		if(empty($content)){
			$this->redirect("Public/404");
		}
        $this->assign($content);
        $this->display();
    }

}

?>
