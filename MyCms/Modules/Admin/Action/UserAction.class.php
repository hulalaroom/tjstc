<?php

class UserAction extends AdminAction {

    public function index() {
        //用户组列表
        $this->group_list('status=1');
        //每页条数
        if (!empty($_GET['limit'])) {
            $limit = $_GET['limit'];
            $index['limit'] = $_GET['limit'];
        } else {
            $limit = C('PAGE_SIZE');
            $index['limit'] = C('PAGE_SIZE');
        }
		
        //绑定房间
		//因表单提交数据为POST而page分页接受为get所以查询数据后在点击分页会出现bug
		//解决方法为用 $_REQUEST 替代$_POST 因$_REQUEST可接受$_GET和$_POST的数据
        if (is_numeric($_REQUEST['roomnum1'])) {
            $map['n.roomnum'] = $_REQUEST['roomnum1'];   
			$index['roomnum1'] = $_REQUEST['roomnum1'];
        }
		 if (is_numeric($_REQUEST['limit'])) { 
			$index['limit'] = $_REQUEST['limit'];
        }
		//房间绑定数量
		if (is_numeric($_REQUEST['roomnumcou'])) {
            $map['n.roomnum'] = $_REQUEST['roomnumcou'];
			$index['roomnumcou'] = $_REQUEST['roomnumcou'];
        }
		
        //注册用户名 & 注册手机号　模糊查询
        if (!empty($_REQUEST['username']) || !empty($_REQUEST['phone'])) {
			if($_REQUEST['username'] != "" && $_REQUEST['phone'] != ""){
				$map['n.username'] = array('like', '%' . $_REQUEST['username'] . '%');//注册用户名
				$map['n.phone'] = array('like', '%' . $_REQUEST['phone'] . '%');//注册手机号
				$map['_logic'] = 'or'; //or条件
			}else if($_REQUEST['phone'] == ""){
				$map['n.username'] = array('like', '%' . $_REQUEST['username'] . '%');//注册用户名
				$index['username'] = $_REQUEST['username'];
			}else if($_REQUEST['username'] == ""){
				$map['n.phone'] = array('like', '%' . $_REQUEST['phone'] . '%');//注册手机号
				$index['phone'] = $_REQUEST['phone'];
			}
			//$index['a.username'] = 'a.username';
			
        }
        
        
            $order = 'n.id  DESC';
        
	        
		//列表
		//首先使用buildSql方法构造子查询SQL
		$Subquery = D('User')->alias('au')
					->join('left join (SELECT a.id,count(1) roomnum FROM ad_user a inner join ad_user_room b on a.id=b.uid and b.ifbind="1"GROUP BY a.id ) nu on au.id=nu.id ')
					//->group('a.id,a.username,a.phone,a.create_time')
					->relation(true)
					->field('au.id,au.username,au.phone,au.fromobj,FROM_UNIXTIME(au.create_time,"%Y-%m-%d %H:%i:%S") as create_time,ifnull(nu.roomnum,0) as roomnum')
					->buildSql();
					
		//分页
        import("ORG.Util.Page");		
        $count = D('User')
					->table($Subquery.' n')
					->where($map)			
					->count();
        $page = new Page($count, $limit);
		//print_r($page);
        //echo D('User')->getLastSql();			

        //echo D('User')->getLastSql();
		//利用子查询进行查询---构造的子查询SQL可用于ThinkPHP的连贯操作方法
		$user_list= D('User')->field('*')
					->table($Subquery.' n')
					->where($map)
					->limit($page->firstRow . ',' . $page->listRows)
					->order($order)
					->select();
		//echo   D('User')->getLastSql();
		//exit;
		
		
        $this->assign('user_list', $user_list);
        //分页显示及默认页数
        $show = $page->show();
		//var_dump($page);
        $this->assign('page',$show);// 赋值分页输出
        $this->assign('p', C('PAGE_SIZE'));
        //输出搜索的条件
        $this->assign($index);
        $this->display();
    }
    public function xx() {

        if (is_numeric($_REQUEST['id'])) {
            $map['n.id'] = $_REQUEST['id'];   
        }
		if (is_numeric($_REQUEST['id'])) {
            $map2['uid'] = $_REQUEST['id'];   
        }

	        
		//列表
		//首先使用buildSql方法构造子查询SQL
		$Subquery = D('User')->alias('au')
					->join('left join (SELECT a.id,count(1) roomnum FROM ad_user a inner join ad_user_room b on a.id=b.uid and b.ifbind="1"GROUP BY a.id ) nu on au.id=nu.id ')
					//->group('a.id,a.username,a.phone,a.create_time')
					->relation(true)
					->field('au.id,au.username,au.phone,au.fromobj,FROM_UNIXTIME(au.create_time,"%Y-%m-%d %H:%i:%S") as create_time,ifnull(nu.roomnum,0) as roomnum')
					->buildSql();
	

        //echo D('User')->getLastSql();
		//利用子查询进行查询---构造的子查询SQL可用于ThinkPHP的连贯操作方法
		$user_list= D('User')->field('*')
					->table($Subquery.' n')
					->where($map)
					->select();
		//echo   D('User')->getLastSql();
		//exit;
		
		//var_dump($user_list);
		$room_list=D('User_room')->field('*')
					->where($map2)
					->select();
        $this->assign('user_list', $user_list);
		$this->assign('room_list', $room_list);

        $this->display();
    }
    public function group_list($w) {
        $glist = D('Group')->where($w)->select();
        $this->assign('glist', $glist);
        return;	
    }
    
    public function edit() {
        $id = intval($this->_param('id'));
        if ($id <= 0) {
            $this->error('参数错误！');
        } else {
            $this->group_list('status=1');
            $list = D('User')->where('id=' . $id)->find();
            $this->assign($list);
            $this->assign('type', 'edit');
            $this->display('edit');
        }
    }

	public function add() {
		$this->group_list('status=1');
		$list = D('User')->where('id=' . $id)->find();
		$this->assign($list);
		$this->assign('type', 'edit');
		$this->display();
    }


	public function pass() {
        $id = intval($this->_param('id'));
        if ($id <= 0) {
            $this->error('参数错误！');
        } else {
            $this->group_list('status=1');
            $list = D('User')->where('id=' . $id)->find();
            $this->assign($list);
            $this->assign('type', 'edit');
            $this->display();
        }
    }

	public function updatePass() {
		//用户id
        $id = $_POST['id'];
		//用户密码更改
		$encrypt = "46faa8ab560de8e86ee20ce678eeb8"; // 加密码
        $password = md5(md5($encrypt) . md5(trim($_POST ['pass'])));
        $data ['password'] = $password;
        $re = D('User')->where("id = ".$id)->setField($data);
		$s = R('Api/passUpdate', array($id,$password));
        if ($re !== false) {
            $this->success('修改成功！', '__URL__/index');
        } else {
            $this->success('修改失败！', '__URL__/index');
        }
    }
	//用户登录明细统计
	 public function view() {
        $id = intval($this->_param('id'));
        if ($id <= 0) {
            $this->error('参数错误！');
        } else {
			$user = D('User')->where('id=' . $id)->find();
            $user_log_list = D('User_login_log')->where('user_id=' . $id)->select();
			$this->assign('user_name', $user['username']);
            $this->assign('user_log_list', $user_log_list);
            $this->display();
        }
    }
	//用户基础信息，费用信息，用量信息
	 public function infor() {
        $id = intval($this->_param('id'));
		$i = 1;
        if ($id <= 0) {
            $this->error('参数错误！');
        } else {
			$user = D('User')->where('id=' . $id)->find();
			//查询用户已绑定房间
            $map['uid'] = $id;
			$map['ifBind'] = array('between','1,2');
            $houselist = D('UserRoom')->where($map)->select();
			if(empty($houselist)){
				 $this->error('用户未绑定房间！');
			}
			foreach ($houselist as $v) {
				$hlist[] = $v['houseCode'];
			}
			$houseCode = json_encode($hlist);
			if(empty($this->_param('housecode'))){
				$housecode = $houselist[0]['houseCode'];
			}
			else{
				$housecode = $this->_param('housecode');
			}
			//查询绑定房间基础信息
		    $this->list = R('Api/getHouse', array($housecode));
			//查询绑定房间账单信息
			$this->costlist = R('Api/getOweCharge', array($housecode));
			//查询历史用量信息
			if(!empty($hlist)){
				$code = "";
				$count = count($hlist);//统计数组长度
				for($i=0;$i<$count;$i++){
					if($i != ($count-1)){
						$code .="'".$hlist[$i]."'".",";
					}
					else{
						$code .="'".$hlist[$i]."'";
					}
				}
			}
			$now = date('Ym');
			$this->powerlist = R('Api/getPowerHistory', array($code));
			$this->assign('housecode', $housecode);
			$this->assign('houselist', $houselist);
			$this->assign('i', $i);
			$this->assign('user_name', $user['username']);
			$this->assign('id', $id);
            $this->display();
        }
    }
	//平台用户详细信息
	 public function information() {
        $id = intval($this->_param('id'));
		$i = 1;
        if ($id <= 0) {
            $this->error('参数错误！');
        } else {
			//查询平台用户详细信息
		    $getinfor = R('Api/getInfor', array($id));
			//dump($getinfor);
			$this->assign('getinfor', $getinfor);
			$this->assign('i', $i);
			$this->assign('id', $id);
            $this->display();
        }
    }
    public function update() {
        D('User')->create();
        $re = D('User')->save();
        if ($re !== false) {
            $this->success('修改成功！', '__URL__/index');
        } else {
            $this->success('修改失败！', '__URL__/index');
        }
    }
	
	//添加用户
	public function insert() {
		if (empty($_POST ['username'])) {
			$this->error('用户名不能为空!');
        } 
		if (empty($_POST ['nickname'])) {
			$this->error('真实姓名不能为空!');
        }
		if (empty($_POST ['password'])) {
			$this->error('用户密码不能为空!');
        }
		if ($_POST ['password'] !== $_POST ['passok']) {
			$this->error('两次密码不同!');
        }
		/*if (empty($_POST ['email'])) {
			$this->error('邮箱不能为空!');
        }*/
        if (empty($_POST ['phone'])) {
			$this->error('手机号码不能为空!');
        }
		$data['username'] = $_POST ['username'];
		$data['nickname'] = $_POST ['nickname'];
		$encrypt = "46faa8ab560de8e86ee20ce678eeb8"; // 加密码
        $password = md5(md5($encrypt) . md5(trim($_POST ['password'])));
		$data['password'] = $password;
		$data['email'] = $_POST ['email'];
		$data['phone'] = $_POST ['phone'];
		$data['address'] = $_POST ['address'];
		$data['avator'] = '';
		$data ['create_ip'] = get_client_ip();
		$data ['last_login_ip'] = '';
		$data ['token'] = md5('my' . $data ['username'] . $data ['password'] . time());
		$data ['tag'] = '';
		$data ['token_time'] = time() + 60 * 60 * 24;
		$data ['mail_ok'] = 0;
		$data ['phone_ok'] = 0;
		$data ['last_login_time'] = 0;
		$data ['phone_login_count'] = 0;
		$data ['token_getpass_time'] =  time() + 60 * 1;
		$data['status'] = 1;
		$data['fromobj'] = 1;
		$data['group_id'] = 1;
		$data['point'] = 0;
		$data['login_count'] = 0;
		$data['create_time'] = strtotime(date('Y-m-d H:i:s',time()));
        $re = D('User')->add($data);
        if ($re !== false) {
            $this->success('添加用户成功！', '__URL__/index');
        } else {
            $this->success('添加用户失败！', '__URL__/index');
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

        if (empty($ids)) {
            $this->error('没有选择记录！');
            exit();
        }
        $map['id'] = array('in', $ids);
		$where['mid'] = array('in', $ids);
        switch ($command) {
            case 'delete':
                $re = D('User')->where($map)->delete();
				$re2=D('UserMessage')->where($where)->delete();
                $type = "删除";
                break;
            case 'status':
                $re = D('User')->where($map)->setField('status', '1');
				 $re2=D('UserMessage')->where($where)->setField('status', '1');
                $type = "启用";
                break;
            case 'unstatus':
                $re = D('User')->where($map)->setField('status', '0');
				 $re2=D('UserMessage')->where($where)->setField('status', '0');
                $type = "停用";
                break;
        }
        if ($re!==false) {
            $this->success('成功' . $type);
        } else {
            $this->error('失败！');
        }
    }
	
	 function score_rule_index(){
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
          //列表
        import("ORG.Util.Page");
        $count = D('ScoreRule')->where($map)->count();
        $page = new Page($count, $limit);
        $sr_list = D('ScoreRule')->where($map)->limit($page->firstRow . ',' . $page->listRows)->order('id  DESC')->select();
//        dump($art_list);
        $this->assign('sr_list', $sr_list);
        //分页显示及默认页数
        $show = $page->show();
        $this->assign('page',$show);// 赋值分页输出
        $this->assign('p', C('PAGE_SIZE'));
        //输出搜索的条件
        $this->assign($index);
        $this->display();
    }
    function score_rule_add(){
        $this->assign('type','add');
        $this->display();
    }
    
    
     public function score_rule_insert() {
        D('ScoreRule')->create();
        $re = D('ScoreRule')->add();
        if ($re !== false) {
            $this->success('录入成功！', '__URL__/score_rule_index');
        } else {
            $this->success('录入失败！', '__URL__/score_rule_index');
        }
    }

    public function score_rule_edit() {
        $id = intval($this->_param('id'));
        if ($id <= 0) {
            $this->error('参数错误！');
        } else {

            $list = D('ScoreRule')->where('id=' . $id)->find();
            $this->assign($list);
            $this->assign('type', 'edit');
            $this->display('score_rule_add');
        }
    }
	
	//用户筛选
	public function check() {
		//查询条件获取
		$username = $_POST['username'];
		$nickname = $_POST['nickname'];
		$email = $_POST['email'];
		$phone = $_POST['phone'];
		$yezhudianhua = $_POST['yezhudianhua'];
		$lianxidianhua = $_POST['lianxidianhua'];
		$shenfenzhenghao = $_POST['shenfenzhenghao'];
		$nengyuankahao = $_POST['nengyuankahao'];
		$fangjiandizhi = $_POST['fangjiandizhi'];
		$daijiaoyinhang = $_POST['daijiaoyinhang'];
		$qishimianji = $_POST['qishimianji'];
		$jieshumianji = $_POST['jieshumianji'];
		$qishishijian = $_POST['qishishijian'];
		$jieshushijian = $_POST['jieshushijian'];
		$sql = "select distinct a.username , a.nickname,a.fromobj,a.phone,a.email,a.point,a.id,b.ifBind  from ad_user  as a ,ad_user_room  as  b where a.id=b.uid  and b.ifBind in ('1','2')";
		//查询来源
		if(empty($_POST['zhuce'])){
			$zhuce = 0;
		}
		else{
			$zhuce = 1;
		}
		if(empty($_POST['bangding'])){
			$bangding = 0;
		}
		else{
			$bangding = 1;
		}
		
		//用户名
		if (!empty($_POST['username'])) {
			 $map['username'] = array('like', '%' . $_POST['username'] . '%');
			 $sql .="  and  a.username  like  '%" . $_POST['username'] . "%' ";
             $index['username'] = $_POST['username'];
        }
		//真实姓名
        if (!empty($_POST['nickname'])) {
            $map['nickname'] = array('like', '%' . $_POST['nickname'] . '%');
			$sql .="  and  a.nickname  like  '%" . $_POST['nickname'] . "%' ";
			$index['nickname'] = $_POST['nickname'];
        }
		//邮箱
		if (!empty($_POST['email'])) {
			 $map['email'] = array('like', '%' . $_POST['email'] . '%');
			 $sql .="  and  a.email  like  '%" . $_POST['email'] . "%' ";
             $index['email'] = $_POST['email'];
        }
		//手机号码
		if (!empty($_POST['phone'])) {
			 $map['phone'] = array('like', '%' . $_POST['phone'] . '%');
			 $sql .="  and  a.phone  like  '%" . $_POST['phone'] . "%' ";
             $index['phone'] = $_POST['phone'];
        }
		//业主名称
		if (!empty($_POST['yezhudianhua'])) {
             $index['yezhudianhua'] = $_POST['yezhudianhua'];
        }
		//联系电话
		if (!empty($_POST['lianxidianhua'])) {
             $index['lianxidianhua'] = $_POST['lianxidianhua'];
        }
		//身份证号
		if (!empty($_POST['shenfenzhenghao'])) {
             $index['shenfenzhenghao'] = $_POST['shenfenzhenghao'];
        }
		//能源卡号
		if (!empty($_POST['nengyuankahao'])) {
             $index['nengyuankahao'] = $_POST['nengyuankahao'];
        }
		//房间地址
		if (!empty($_POST['fangjiandizhi'])) {
             $index['fangjiandizhi'] = $_POST['fangjiandizhi'];
        }
		//代缴银行
		if (!empty($_POST['daijiaoyinhang'])) {
             $index['daijiaoyinhang'] = $_POST['daijiaoyinhang'];
        }
		//起始面积
		if (!empty($_POST['qishimianji'])) {
             $index['qishimianji'] = $_POST['qishimianji'];
        }
		//结束面积
		if (!empty($_POST['jieshumianji'])) {
             $index['jieshumianji'] = $_POST['jieshumianji'];
        }
		//起始入住时间
		if (!empty($_POST['qishishijian'])) {
             $index['qishishijian'] = $_POST['qishishijian'];
        }
		//结束入住时间
		if (!empty($_POST['jieshushijian'])) {
             $index['jieshushijian'] = $_POST['jieshushijian'];
        }

		//判断查询方式
		if($zhuce == 1 && $bangding == 0){
			//查询用户(按注册信息)
			$user_list = D('User')->where($map)->relation(true)->select();
		}
		else if(($zhuce == 0 && $bangding == 1) OR ($zhuce == 1 && $bangding == 1)){
			$house = new Model();
			//查询用户(按绑定信息)
			$houseCodeList = R('Api/checkUser', array('',$username,$nickname,$email,$phone,'',$yezhudianhua, $lianxidianhua, $shenfenzhenghao, $nengyuankahao, $fangjiandizhi, $daijiaoyinhang, $qishimianji, $jieshumianji, $qishishijian, $jieshushijian));
			if(!empty($houseCodeList)){
				$housecode = "";
				$count = count($houseCodeList);//统计数组长度
				for($i=0;$i<$count;$i++){
					if($i != ($count-1)){
						$housecode .="'".$houseCodeList[$i]['HOUSECODE']."'".",";
					}
					else{
						$housecode .="'".$houseCodeList[$i]['HOUSECODE']."'";
					}
				}
			}
			$sql .="  and b.houseCode in (".$housecode.") ";
			$list = $house->query($sql);
			$arr = array();
			for($i=0;$i<count($list);$i++){
				//将所得数组放入数组$arr
				$arr[$i]['id'] = $list[$i]['id'];
				$arr[$i]['ifBind'] = $list[$i]['ifBind'];
				$arr[$i]['phone'] = $list[$i]['phone'];
				$arr[$i]['point'] = $list[$i]['point'];
				$arr[$i]['username'] = $list[$i]['username'];
				$arr[$i]['email'] = $list[$i]['email'];
				$arr[$i]['nickname'] = $list[$i]['nickname'];
				$arr[$i]['fromobj'] = $list[$i]['fromobj'];	
			} 			
			$user_list = $arr;
		}
		else{
			$user_list = "first";
		}
		//dump($user_list);
		//注册信息查询
		$index['zhuce'] = $zhuce;
		//接口信息查询
		$index['bangding'] = $bangding;
		
		$this->assign('user_list', $user_list);

		$this->assign($index);

        $this->display();
    }

	//平台用户筛选
	public function choose() {
		//查询条件获取
		$yezhuxingming = $_POST['yezhuxingming'];
		$lianxidianhua = $_POST['lianxidianhua'];
		$shenfenzhenghao = $_POST['shenfenzhenghao'];
		$nengyuankahao = $_POST['nengyuankahao'];
		$village = $_POST['village'];
		$building = $_POST['building'];
		$yonghuleixing = $_POST['yonghuleixing'];
		$qishimianji = $_POST['qishimianji'];
		$jieshumianji = $_POST['jieshumianji'];
		$qishishijian = $_POST['qishishijian'];
		$jieshushijian = $_POST['jieshushijian'];
		//查询来源
		if(empty($_POST['bangding'])){
			$bangding = 0;
		}
		else{
			$bangding = 1;
		}
		
		//业主名称
		if (!empty($_POST['yezhuxingming'])) {
             $index['yezhuxingming'] = $_POST['yezhuxingming'];
        }
		//联系电话
		if (!empty($_POST['lianxidianhua'])) {
             $index['lianxidianhua'] = $_POST['lianxidianhua'];
        }
		//身份证号
		if (!empty($_POST['shenfenzhenghao'])) {
             $index['shenfenzhenghao'] = $_POST['shenfenzhenghao'];
        }
		//能源卡号
		if (!empty($_POST['nengyuankahao'])) {
             $index['nengyuankahao'] = $_POST['nengyuankahao'];
        }
		//小区
		if (!empty($_POST['village'])) {
             $index['village'] = $_POST['village'];
			 $buildlist = R('Api/getBuilding', array(($_POST['village'])));
        }
		//楼号
		if (!empty($_POST['building'])) {
             $index['building'] = $_POST['building'];
        }
		//用户类型
		if (!empty($_POST['yonghuleixing'])) {
             $index['yonghuleixing'] = $_POST['yonghuleixing'];
        }
		//起始面积
		if (!empty($_POST['qishimianji'])) {
             $index['qishimianji'] = $_POST['qishimianji'];
        }
		//结束面积
		if (!empty($_POST['jieshumianji'])) {
             $index['jieshumianji'] = $_POST['jieshumianji'];
        }
		//起始入住时间
		if (!empty($_POST['qishishijian'])) {
             $index['qishishijian'] = $_POST['qishishijian'];
        }
		//结束入住时间
		if (!empty($_POST['jieshushijian'])) {
             $index['jieshushijian'] = $_POST['jieshushijian'];
        }
		
		//查询用户(按绑定信息)
		if($bangding == 1){
			$list = R('Api/chooseUser', array($yezhuxingming, $lianxidianhua, $shenfenzhenghao, $nengyuankahao, $village, $building, $yonghuleixing, $qishimianji, $jieshumianji, $qishishijian, $jieshushijian));
			
			$count = count($list);//统计数组长度
			$arr = array();
			for($i=0;$i<$count;$i++){  
				//将所得数组放入数组$arr
				//$arr[$i]['id'] = $list[$i]->id;
				$arr[$i]['ownername'] = $list[$i]['OWNERNAME'];
				$arr[$i]['papercode'] = $list[$i]['PAPERCODE'];
				$arr[$i]['dz'] = $list[$i]['DZ'];
				$arr[$i]['banknumber'] = $list[$i]['BANKNUMBER'];
				$arr[$i]['mobilephone'] = $list[$i]['MOBILEPHONE'];
				$arr[$i]['usetype'] = $list[$i]['USETYPE'];
				$arr[$i]['chargearea'] = $list[$i]['CHARGEAREA'];
				$arr[$i]['enterdate'] = $list[$i]['ENTERDATE'];
				$arr[$i]['communityname'] = $list[$i]['COMMUNITYNAME'];
				$arr[$i]['buildingname'] = $list[$i]['BUILDINGNAME'];
			} 
			
			$user_list = $arr;
		}
		else{
			$user_list = "first";
		}
		$villlist = R('Api/getVillage');

		if($_GET['excel'] == 1){
			$this->exportexcel($user_list);
			$user_list = "first";
		} 
		
		//接口信息查询
		$index['bangding'] = $bangding;

		$this->assign('user_list', $user_list);

		$this->assign('villlist', $villlist);

		$this->assign('buildlist', $buildlist);

		$this->assign($index);

        $this->display();
    }

	public function exportexcel($result){
        $ResultsData = $result;  //查询数据得到$ResultsData二维数组  
		$ave = 10000;
		$key = 0;
		$count = intval(count($ResultsData)/($ave))+1;
        vendor("PHPExcel.PHPExcel");  
        // 建立PHPExcel对象  
        $objPHPExcel = new PHPExcel();  
		for($i=0;$i<$count;$i++){
			if($i !=0){
				$key = $key + $ave;
			}
			//创建一个工作空间(sheet)
			$objPHPExcel->createSheet();
			// sheet命名  
			//$objPHPExcel->getActiveSheet()->setTitle('统计表'); 
			//设置页签
			$objPHPExcel->setActiveSheetIndex($i);  
			//设置宽度
			$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);  
			$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);  
			$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(50);  
			$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);  
			$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);  
			$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);  
			$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);  
			$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
			$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
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
			$objPHPExcel->setActiveSheetIndex($i)  
				->setCellValue('A1', '业主名称')  
				->setCellValue('B1', '身份证号')  
				->setCellValue('C1', '房间地址')  
				->setCellValue('D1', '能源卡号')  
				->setCellValue('E1', '联系电话')  
				->setCellValue('F1', '用户类型')  
				->setCellValue('G1', '采暖面积')  
				->setCellValue('H1', '小区名称')
				->setCellValue('I1', '大楼');
	  
			// 填充数据(UTF-8)  
			for($j=0;$j<$ave;$j++){
				if($i==0){
					$flg = $j;
				}
				else{
					$flg = $key + $j;
				} 
				$papercode = $ResultsData[$flg]['papercode']." ";
				$banknumber = $ResultsData[$flg]['banknumber']." ";
				$objPHPExcel->getActiveSheet($i)->setCellValue('A'.($j+2), $ResultsData[$flg]['ownername']);  
				$objPHPExcel->getActiveSheet($i)->setCellValue('B'.($j+2), $papercode);  
				$objPHPExcel->getActiveSheet($i)->setCellValue('C'.($j+2), $ResultsData[$flg]['dz']); 
				$objPHPExcel->getActiveSheet($i)->setCellValue('D'.($j+2), $banknumber);   
				$objPHPExcel->getActiveSheet($i)->setCellValue('E'.($j+2), $ResultsData[$flg]['mobilephone']); //时间戳转换  
				$objPHPExcel->getActiveSheet($i)->setCellValue('F'.($j+2), $ResultsData[$flg]['usetype']);  
				$objPHPExcel->getActiveSheet($i)->setCellValue('G'.($j+2), $ResultsData[$flg]['chargearea']);  
				$objPHPExcel->getActiveSheet($i)->setCellValue('H'.($j+2), $ResultsData[$flg]['communityname']); 
				$objPHPExcel->getActiveSheet($i)->setCellValue('I'.($j+2), $ResultsData[$flg]['buildingname']); 
			}  
	  
		}
  
        // excel头参数  
        header('Content-Type: application/vnd.ms-excel');  
        header('Content-Disposition: attachment;filename="用户信息查询统计表('.date('Y-m-d').').xls"');  //日期为文件名后缀  
        header('Cache-Control: max-age=0');  
  
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  //excel5为xls格式，excel2007为xlsx格式  
        $objWriter->save('php://output'); 
	}


	//数据统计
	public function account() {
       $qishishijian = $_POST['qishishijian'];
	   $jieshushijian = $_POST['jieshushijian'];
	   //查询来源
		if(empty($_POST['bangding'])){
			$bangding = 0;
		}
		else{
			$bangding = 1;
		}
       //查询时间
		if ((!empty($_POST['qishishijian'])) && (!empty($_POST['jieshushijian']))) {
			 $data['visit_time'] = array(between,array(strtotime($qishishijian),strtotime($jieshushijian)));
			 $map['login_time'] = array(between,array(strtotime($qishishijian),strtotime($jieshushijian)));
			 $res['bindtime'] = array(between,array(strtotime($qishishijian),strtotime($jieshushijian)));
			 $fun['s.bindtime'] = array(between,array(strtotime($qishishijian),strtotime($jieshushijian)));
			 $con['create_time'] = array(between,array(strtotime($qishishijian),strtotime($jieshushijian)));
			 $com['create_time'] = array(between,array(strtotime($qishishijian),strtotime($jieshushijian)));
			 $index['qishishijian'] = $qishishijian;
			 $index['jieshushijian'] = $jieshushijian;
        }
		if((!empty($_POST['qishishijian'])) && (empty($_POST['jieshushijian']))){
			$data['visit_time'] = array(between,array(strtotime($qishishijian),time()));
			$map['login_time'] = array(between,array(strtotime($qishishijian),time()));
			$res['bindtime'] = array(between,array(strtotime($qishishijian),time()));
			$fun['s.bindtime'] = array(between,array(strtotime($qishishijian),time()));
			$con['create_time'] = array(between,array(strtotime($qishishijian),time()));
			$com['create_time'] = array(between,array(strtotime($qishishijian),time()));
			$index['qishishijian'] = $qishishijian;
		}
		if((empty($_POST['qishishijian'])) && (!empty($_POST['jieshushijian']))){
			$data['visit_time'] = array(between,array(strtotime("01 January 2012"),strtotime($jieshushijian)));
			$map['login_time'] = array(between,array(strtotime("01 January 2012"),strtotime($jieshushijian)));
			$res['bindtime'] = array(between,array(strtotime("01 January 2012"),strtotime($jieshushijian)));
			$fun['s.bindtime'] = array(between,array(strtotime("01 January 2012"),strtotime($jieshushijian)));
			$con['create_time'] = array(between,array(strtotime("01 January 2012"),strtotime($jieshushijian)));
			$com['create_time'] = array(between,array(strtotime("01 January 2012"),strtotime($jieshushijian)));
			$index['jieshushijian'] = $jieshushijian;
		}
		/*if((empty($_POST['qishishijian'])) && (empty($_POST['jieshushijian']))){
			$data['visit_time'] = array(between,array(strtotime("01 January 2012"),time()));
			$map['login_time'] = array(between,array(strtotime("01 January 2012"),time()));
			$res['bindtime'] = array(between,array(strtotime("01 January 2012"),time()));
			$fun['s.bindtime'] = array(between,array(strtotime("01 January 2012"),time()));
		}*/
		if($bangding == 1){
			//网站每日运行统计数据
			$arr = array();
			$all = array();
			//总访问量
			$visitCount = D('Visit_log')->where($data)->count();
			$visitCountAll = D('Visit_log')->field('id')->count();
			//移动端访问量
			$data['fromobj'] = 1;
			$visitPhoneCount = D('Visit_log')->where($data)->count();
			$visitPhoneCountAll = D('Visit_log')->where('fromobj=1')->count();
			//总登陆用户数量
			$loginCount = D('User_login_log')->where($map)->group('user_id')->count();
			$loginCountAll = D('User_login_log')->group('user_id')->count();
			//网站登陆用户数量
			$map['login_type'] = 1;
			$loginPcCount = D('User_login_log')->where($map)->group('user_id')->count();
			$loginPcCountAll = D('User_login_log')->where('login_type=1')->group('user_id')->count();
			//绑定用户数量
			$res['ifBind'] = 1;
			$bindCount = D('User_room')->where($res)->count();
			$bindCountAll = D('User_room')->where('ifBind=1')->count();
			//总注册用户数量
			$registerCount = D('User')->where($con)->count();
			$registerCounttAll = D('User')->field('id')->count();
			//移动端注册用户数量
			$con['fromobj'] = 1;
			$registerAppCount = D('User')->where($con)->count();
			$registerAppCounttAll = D('User')->where('fromobj=1')->field('id')->count();
			//总正常用户数量
			$com['status'] = 1;
			$registerUsedCount = D('User')->where($com)->count();
			$registerUsedCounttAll = D('User')->where('status=1')->field('id')->count();
			//移动端正常用户
			$com['fromobj'] = 1;
			$registerUsedAppCount = D('User')->where($com)->count();
			$registerUsedAppCounttAll = D('User')->where('status=1 and fromobj=1')->field('id')->count();
			//绑定用户信息
			$fun['s.ifBind']  = 1;
			/*import("ORG.Util.Page");
			$count = D('User')->table('ad_user t, ad_user_room s')->where($fun)->where('t.id = s.uid')->field('t.nickname,t.username,t.phone,s.houseCode,s.wxusername,s.houseName')->count();
			$page = new Page($count, $limit);
			$list = D('User')->table('ad_user t, ad_user_room s')->where($fun)->where('t.id = s.uid')->field('t.nickname,t.username,t.phone,s.houseCode,s.wxusername,s.houseName')->limit($page->firstRow . ',' . $page->listRows)->order('s.id DESC')->select();
			//分页显示及默认页数
			$show = $page->show();*/
			$list = D('User')->table('ad_user t, ad_user_room s')->where($fun)->where('t.id = s.uid')->field('t.nickname,t.username,t.phone,s.houseCode,s.wxusername,s.houseName')->order('s.id DESC')->select();
			//微信绑定数量
			$res['wxusername']  = array('neq','');
			$da['wxusername']  = array('neq','');
			$da['ifBind']  = 1;
			$bindWxCount = count(D('User_room')->where($res)->select());
			$bindWxCountAll = count(D('User_room')->where($da)->select());
			if ((!empty($_POST['qishishijian'])) || (!empty($_POST['jieshushijian']))) {
				$arr['visitCount'] = $visitCount;
				$arr['visitPhoneCount'] = $visitPhoneCount;
				$arr['registerCount'] = $registerCount;
				$arr['registerPcCount'] = $registerCount - $registerAppCount;
				$arr['registerAppCount'] = $registerAppCount;
				$arr['registerUsedCount'] = $registerUsedCount;
				$arr['registerUsedPcCount'] = $registerUsedCount - $registerUsedAppCount;
				$arr['registerUsedAppCount'] = $registerUsedAppCount;
				$arr['registerStopCount'] = $registerCount - $registerUsedCount;
				$arr['registerStopPcCount'] = $registerCount - $registerAppCount - ($registerUsedCount - $registerUsedAppCount);
				$arr['registerStopAppCount'] = $registerAppCount - $registerUsedAppCount;
				
				if(strtotime($qishishijian) <= '1406822400'){
					$arr['loginCount'] = $loginCount+C('login');
					$arr['loginPcCount'] = $loginPcCount+C('login');
				}
				else{
					$arr['loginCount'] = $loginCount;
					$arr['loginPcCount'] = $loginPcCount;
				}
				$arr['loginAppCount'] = $loginCount - $loginPcCount;
				$arr['bindCount'] = $bindCount;
				$arr['bindWxCount'] = $bindWxCount;
				$arr['bindPcCount'] = $bindCount - $bindWxCount;
			}
			//总数
			$all['visitCountAll'] = $visitCountAll;
			$all['visitPhoneCountAll'] = $visitPhoneCountAll;
			$all['loginCountAll'] = $loginCountAll+C('login');
			$all['loginPcCountAll'] = $loginPcCountAll+C('login');
			$all['loginAppCountAll'] = $loginCountAll - $loginPcCountAll;
			$all['bindCountAll'] = $bindCountAll-C('bind');
			$all['bindWxCountAll'] = $bindWxCountAll-C('wx');
			$all['bindPcCountAll'] = ($bindCountAll-C('bind'))-($bindWxCountAll-C('wx'));
			$all['registerCounttAll'] = $registerCounttAll;
			$all['registerPcCounttAll'] = $registerCounttAll - $registerAppCounttAll;
			$all['registerAppCounttAll'] = $registerAppCounttAll;
			$all['registerUsedCounttAll'] = $registerUsedCounttAll;
			$all['registerUsedPcCounttAll'] = $registerUsedCounttAll - $registerUsedAppCounttAll;
			$all['registerUsedAppCounttAll'] = $registerUsedAppCounttAll;
			$all['registerStopCounttAll'] = $registerCounttAll - $registerUsedCounttAll;
			$all['registerStopPcCounttAll'] = $registerCounttAll - $registerAppCounttAll - ($registerUsedCounttAll - $registerUsedAppCounttAll);
			$all['registerStopAppCounttAll'] = $registerAppCounttAll - $registerUsedAppCounttAll;
		}
		if($_GET['excel'] == 1){
			$this->exportaccount($list);
		} 
		//平台信息查询
		$index['bangding'] = 1;
		$this->assign('page', $show); // 赋值分页输出
		$this->assign('p', C('PAGE_SIZE'));
		$this->assign('result', $arr);
		$this->assign('list', $list);
		$this->assign('all', $all);
		$this->assign($index);

        $this->display();
    }

	public function exportaccount($result){
        $ResultsData = $result;  //查询数据得到$ResultsData二维数组 
		$ave = 10000;
		$key = 0;
		$count = intval(count($ResultsData)/($ave))+1;
        vendor("PHPExcel.PHPExcel");  
        // 建立PHPExcel对象  
        $objPHPExcel = new PHPExcel();
		for($i=0;$i<$count;$i++){
			if($i !=0){
				$key = $key + $ave;
			}
			//创建一个工作空间(sheet)
			$objPHPExcel->createSheet();
			// sheet命名  
			$objPHPExcel->getActiveSheet()->setTitle('统计表'); 
			//设置页签
			$objPHPExcel->setActiveSheetIndex($i);  
			//设置宽度
			$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);  
			$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);  
			$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);  
			$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(50);  
			//设置水平居中   
			$objPHPExcel->getActiveSheet()->getStyle('A')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);  
			$objPHPExcel->getActiveSheet()->getStyle('B')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
			$objPHPExcel->getActiveSheet()->getStyle('C')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
			$objPHPExcel->getActiveSheet()->getStyle('D')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
			// 设置首行文字  
			$objPHPExcel->setActiveSheetIndex($i)  
				->setCellValue('A1', '用户真实名')  
				->setCellValue('B1', '联系电话')  
				->setCellValue('C1', '房间编号')  
				->setCellValue('D1', '房间地址'); 
	  
			// 填充数据(UTF-8)  
			for($j=0;$j<$ave;$j++){
				if($i==0){
					$flg = $j;
				}
				else{
					$flg = $key + $j;
				}
				$objPHPExcel->getActiveSheet($i)->setCellValue('A'.($j+2), $ResultsData[$flg]['nickname']);  
				$objPHPExcel->getActiveSheet($i)->setCellValue('B'.($j+2), $ResultsData[$flg]['phone']);  
				$objPHPExcel->getActiveSheet($i)->setCellValue('C'.($j+2), $ResultsData[$flg]['houseCode']); 
				$objPHPExcel->getActiveSheet($i)->setCellValue('D'.($j+2), $ResultsData[$flg]['houseName']);   
			}
        }
  
        // excel头参数  
        header('Content-Type: application/vnd.ms-excel');  
        header('Content-Disposition: attachment;filename="绑定房间用户详细信息查询统计表('.date('Y-m-d').').xls"');  //日期为文件名后缀  
        header('Cache-Control: max-age=0');  
  
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  //excel5为xls格式，excel2007为xlsx格式  
        $objWriter->save('php://output'); 
	}



	//用户缴费
	public function pay() {
       $qishishijian = $_POST['qishishijian'];
	   $jieshushijian = $_POST['jieshushijian'];
	   //查询来源
		if(empty($_POST['bangding'])){
			$bangding = 0;
		}
		else{
			$bangding = 1;
		}
       //查询时间
		if ((!empty($_POST['qishishijian'])) && (!empty($_POST['jieshushijian']))) {
			 $data['visit_time'] = array(between,array(strtotime($qishishijian),strtotime($jieshushijian)));
			 $map['login_time'] = array(between,array(strtotime($qishishijian),strtotime($jieshushijian)));
			 $res['bindtime'] = array(between,array(strtotime($qishishijian),strtotime($jieshushijian)));
			 $fun['s.bindtime'] = array(between,array(strtotime($qishishijian),strtotime($jieshushijian)));
			 $con['create_time'] = array(between,array(strtotime($qishishijian),strtotime($jieshushijian)));
			 $com['create_time'] = array(between,array(strtotime($qishishijian),strtotime($jieshushijian)));
			 $index['qishishijian'] = $qishishijian;
			 $index['jieshushijian'] = $jieshushijian;
        }
		if((!empty($_POST['qishishijian'])) && (empty($_POST['jieshushijian']))){
			$data['visit_time'] = array(between,array(strtotime($qishishijian),time()));
			$map['login_time'] = array(between,array(strtotime($qishishijian),time()));
			$res['bindtime'] = array(between,array(strtotime($qishishijian),time()));
			$fun['s.bindtime'] = array(between,array(strtotime($qishishijian),time()));
			$con['create_time'] = array(between,array(strtotime($qishishijian),time()));
			$com['create_time'] = array(between,array(strtotime($qishishijian),time()));
			$index['qishishijian'] = $qishishijian;
		}
		if((empty($_POST['qishishijian'])) && (!empty($_POST['jieshushijian']))){
			$data['visit_time'] = array(between,array(strtotime("01 January 2012"),strtotime($jieshushijian)));
			$map['login_time'] = array(between,array(strtotime("01 January 2012"),strtotime($jieshushijian)));
			$res['bindtime'] = array(between,array(strtotime("01 January 2012"),strtotime($jieshushijian)));
			$fun['s.bindtime'] = array(between,array(strtotime("01 January 2012"),strtotime($jieshushijian)));
			$con['create_time'] = array(between,array(strtotime("01 January 2012"),strtotime($jieshushijian)));
			$com['create_time'] = array(between,array(strtotime("01 January 2012"),strtotime($jieshushijian)));
			$index['jieshushijian'] = $jieshushijian;
		}
		/*if((empty($_POST['qishishijian'])) && (empty($_POST['jieshushijian']))){
			$data['visit_time'] = array(between,array(strtotime("01 January 2012"),time()));
			$map['login_time'] = array(between,array(strtotime("01 January 2012"),time()));
			$res['bindtime'] = array(between,array(strtotime("01 January 2012"),time()));
			$fun['s.bindtime'] = array(between,array(strtotime("01 January 2012"),time()));
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
        import("ORG.Util.Page");
        $count = D('Pay_info')->count();
		
        $page = new Page($count, $limit);
        $pay_info_list = D('Pay_info')->order('payTime desc')->limit($page->firstRow . ',' . $page->listRows)->select();
		
        //分页显示及默认页数
        $show = $page->show();
		//交易记录
		
		if($_GET['excel'] == 1){
			$list = D('Pay_info')->select();
			$this->exportpay($list);
		}

        for($i=0;$i<count($pay_info_list);$i++){
            $pay_info_list[$i]["payTime"]=substr($pay_info_list[$i]["payTime"],0,4)
                ."-".substr($pay_info_list[$i]["payTime"],4,2)."-".substr($pay_info_list[$i]["payTime"],6,2)
                ." ".substr($pay_info_list[$i]["payTime"],8,2).":".substr($pay_info_list[$i]["payTime"],10,2).":".substr($pay_info_list[$i]["payTime"],12,2);
        }
		//平台信息查询
		$index['bangding'] = 1;
		$this->assign('page', $show); // 赋值分页输出
		$this->assign('p', C('PAGE_SIZE'));
		$this->assign('list', $pay_info_list);
		

        $this->display();
    }


	public function exportpay($result){
        $ResultsData = $result;  //查询数据得到$ResultsData二维数组 
		$ave = 10000;
		$key = 0;
		$count = intval(count($ResultsData)/($ave))+1;
        vendor("PHPExcel.PHPExcel");  
        // 建立PHPExcel对象  
        $objPHPExcel = new PHPExcel();
		for($i=0;$i<$count;$i++){
			if($i !=0){
				$key = $key + $ave;
			}
			//创建一个工作空间(sheet)
			$objPHPExcel->createSheet();
			// sheet命名  
			$objPHPExcel->getActiveSheet()->setTitle('缴费记录统计表'); 
			//设置页签
			$objPHPExcel->setActiveSheetIndex($i);  
			//设置宽度
			$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);  
			$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);  
			$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);  
			$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);  
			$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30); 
			$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20); 
			//设置水平居中   
			$objPHPExcel->getActiveSheet()->getStyle('A')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);  
			$objPHPExcel->getActiveSheet()->getStyle('B')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
			$objPHPExcel->getActiveSheet()->getStyle('C')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
			$objPHPExcel->getActiveSheet()->getStyle('D')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
			$objPHPExcel->getActiveSheet()->getStyle('E')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
			$objPHPExcel->getActiveSheet()->getStyle('F')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			// 设置首行文字  
			$objPHPExcel->setActiveSheetIndex($i)  
				->setCellValue('A1', '订单号')  
				->setCellValue('B1', '银行账号')  
				->setCellValue('C1', '支付金额')  
				->setCellValue('D1', '支付状态')
				->setCellValue('E1', '支付时间')
				->setCellValue('F1', '交易状态');
	  
			// 填充数据(UTF-8)  
			for($j=0;$j<$ave;$j++){
				if($i==0){
					$flg = $j;
				}
				else{
					$flg = $key + $j;
				}
				if(!empty($ResultsData[$flg]['orderNo'])){
					if($ResultsData[$flg]['orderStatus'] == 1){
						$ResultsData[$flg]['orderStatus'] = "支付成功";
					}
					else{
						$ResultsData[$flg]['orderStatus'] = "未支付";
					}
					$sj = $ResultsData[$flg]['payTime'];
					$ResultsData[$flg]['payTime'] = substr($sj,0,4).".".substr($sj,4,2).".".substr($sj,6,2)."  ".date("H:i:s",substr($sj,-6))." ";
				}
				
				$objPHPExcel->getActiveSheet($i)->setCellValue('A'.($j+2), $ResultsData[$flg]['orderNo']." ");  
				$objPHPExcel->getActiveSheet($i)->setCellValue('B'.($j+2), $ResultsData[$flg]['merchantNo']." ");  
				$objPHPExcel->getActiveSheet($i)->setCellValue('C'.($j+2), $ResultsData[$flg]['payAmount']); 
				$objPHPExcel->getActiveSheet($i)->setCellValue('D'.($j+2), $ResultsData[$flg]['orderStatus']);  
				$objPHPExcel->getActiveSheet($i)->setCellValue('E'.($j+2), $ResultsData[$flg]['payTime']);  
				$objPHPExcel->getActiveSheet($i)->setCellValue('F'.($j+2), $ResultsData[$flg]['platformMessage']);  
			}
        }
  
        // excel头参数  
        header('Content-Type: application/vnd.ms-excel');  
        header('Content-Disposition: attachment;filename="用户缴费详细信息统计表('.date('Y-m-d').').xls"');  //日期为文件名后缀  
        header('Cache-Control: max-age=0');  
  
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  //excel5为xls格式，excel2007为xlsx格式  
        $objWriter->save('php://output'); 
	}
		//房间解绑申请
    public function qzjiebang(){
        $url = 'http://10.105.15.2/TjstcWebImpl/GetApplyBindHouseListServlet';
		
		$post_data="";
		$housecode=$_REQUEST['housecode'];
		$aa='';
		$ownername=$_REQUEST['ownername'];
		$bb='';
		$papercode=$_REQUEST['papercode'];
		$cc='';
		if($housecode!=''){
			$aa="and housecode='$housecode'";
			$index['housecode'] = $_REQUEST['housecode'];
		}
		if($ownername!=''){
			$bb="and ownername='$ownername'";
			$index['ownername'] = $_REQUEST['ownername'];
		}
		if($papercode!=''){
			$cc="and papercode='$papercode'";
			$index['papercode'] = $_REQUEST['papercode'];
		}
		$post_data="vCONDITION=".$aa.$bb.$cc;
		//var_dump($post_data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		$output = curl_exec($ch);
		$guestlist1=json_decode($output,true);
		$count = count($guestlist1['r_body']);
		$guestlist=$guestlist1['r_body'];
		//var_dump($guestlist);exit;
		curl_close($ch);
		//var_dump($index);

        //每页条数
        if (!empty($_GET['limit'])) {
            $limit = $_GET['limit'];
            $index['limit'] = $_GET['limit'];
        } else {
            $limit = C('PAGE_SIZE');
            $index['limit'] = C('PAGE_SIZE');
        }
		import("ORG.Util.Page");
        $page = new Page($count,$limit);
		//var_dump($page);
        
		//接口取数据适用分页
		$pages=$_GET['p'];
		if($pages==""){
			$pages=1;
		}
		//print_r($pages);
		$start=($pages-1)*$limit;//偏移量，当前页-1乘以每页显示条数
		$article = array_slice($guestlist,$start,$limit);//原数组，开始下标，要取几条
		//print_r($article);
		
		//分页显示及默认页数
        $show = $page->show();
		//print_r($show);
        $this->assign('page', $show); // 赋值分页输出
        $this->assign('p', C('PAGE_SIZE'));
		
        //输出搜索的条件
        $this->assign($index);
        $this->assign('guestlist', $article);
        $this->display();
    }
		//房间解绑申请详细
    public function jiebangxiangxi(){
        $url = 'http://10.105.15.2/TjstcWebImpl/GetApplyBindHouseListServlet';
		$id=$_GET['id'];
		$post_data="vCONDITION= and id=".$id;
		//var_dump($post_data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		$output = curl_exec($ch);
		$guestlist1=json_decode($output,true);
		$count = count($guestlist1['r_body']);
		$guestlist=$guestlist1['r_body'];

		$nyhtfileurl=$guestlist[0]['nyhtfileurl'];
		$zsshtfileurl=$guestlist[0]['zsshtfileurl'];
		$nyhtfile=explode(',', $nyhtfileurl);
		$zsshtfile=explode(',', $zsshtfileurl);
		$nyhtfile1=$nyhtfile[0];
		$nyhtfile2=$nyhtfile[1];
		$zsshtfile1=$zsshtfile[0];
		$zsshtfile2=$zsshtfile[1];
		//var_dump($guestlist);exit;
		curl_close($ch);
		$this->assign('nyhtfile1', $nyhtfile1);
		$this->assign('nyhtfile2', $nyhtfile2);
		$this->assign('zsshtfile1', $zsshtfile1);
		$this->assign('zsshtfile2', $zsshtfile2);
        $this->assign('guestlist', $guestlist);
        $this->display();
    }
		//房间解绑申请操作
		    public function jiebangcaozuo() {
        $ids = $_GET['rid'];
		$id=$_GET['id'];
		$uid=$_GET['uid'];
		$time=time();
		$map3['id']=$uid;
		$re3=D('User')->where($map3)->field('phone')->select();
		//$aa=D('User')->getLastSql();
		//echo $aa;exit;
		//var_dump($re3);exit;
		//echo $time;exit;
		$phone=$re3[0]["phone"];
		//var_dump($re3);exit;
		//echo $phone;exit;
		$map2['id']=$id;
		$data2['state']='已审核';
		$data2['examinetime']=$time;
		$re2=D('applybindhouse')->where($map2)->save($data2);

        $map['houseCode'] =$ids;
		$data['ifBind']=0;
        $re = D('UserRoom')->where($map)->save($data);
		
		
		//$aa=D('UserRoom')->getLastSql();
		//echo $aa;exit;

        if ($re !== false) {
            $this->success('成功');
			$sjcode='尊敬的客户,您办理的房间解绑申请业务已经审核成功,请及时绑定您的房间,谢谢！';
			$url2 = "http://10.105.15.2:8088/smsshare/smsServlet";
			$data = array(
				//手机号码
				'telNums' => $phone,
				'content' => '',
				'active' => 'webnetNew'
			);
			//echo $sjcode;exit;
			$data = http_build_query($data);
			$ch2 = curl_init();
			$this_header = array(
				"content-type: application/x-www-form-urlencoded; 
				charset=UTF-8"
				);
			curl_setopt($ch2,CURLOPT_HTTPHEADER,$this_header);
			curl_setopt($ch2, CURLOPT_URL, $url2);
			curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch2, CURLOPT_POST, 1);
			curl_setopt($ch2, CURLOPT_POSTFIELDS, $data);
			$output2 = curl_exec($ch2);
			$date2=json_decode($output2,true);
			curl_close($ch2);
        } else {
            $this->error('失败！');
        }
    }
	//获取楼号
	public function getBuilding() {
		$list = R('Api/getBuilding', array(($_POST['parameter'])));
		echo json_encode($list);
	}

    public function score_rule_update() {
        D('ScoreRule')->create();
        $re = D('ScoreRule')->save();
        if ($re !== false) {
            $this->success('修改成功！', '__URL__/score_rule_index');
        } else {
            $this->success('修改失败！', '__URL__/score_rule_index');
        }
    }
    
          //批量操作
    public function score_rule_bat() {
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
                $re = D('ScoreRule')->where($map)->delete();
                $type = "删除";
                break;
            case 'status':
                $re = D('ScoreRule')->where($map)->setField('status', '1');
                $type = "启用";
                break;
            case 'unstatus':
                $re = D('ScoreRule')->where($map)->setField('status', '0');
                $type = "停用";
                break;
        }
        if ($re!==false) {
//            echo D('ScoreRule')->getLastInsID();
            $this->success('成功' . $type);
        } else {
            $this->error('失败！');
        }
    }

	public function userexcel(){
		$Subquery = D('User')->alias('au')
					->join('left join (SELECT a.id,count(1) roomnum FROM ad_user a inner join ad_user_room b on a.id=b.uid and b.ifbind="1"GROUP BY a.id ) nu on au.id=nu.id ')
					//->group('a.id,a.username,a.phone,a.create_time')
					->relation(true)
					->field('au.id,au.username,au.phone,au.fromobj,FROM_UNIXTIME(au.create_time,"%Y-%m-%d %H:%i:%S") as create_time,ifnull(nu.roomnum,0) as roomnum')
					->buildSql();

		$ResultsData= D('User')->field('*')
					->table($Subquery.' n')
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


        //设置水平居中   
        $objPHPExcel->getActiveSheet()->getStyle('A')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);  
        $objPHPExcel->getActiveSheet()->getStyle('B')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
		$objPHPExcel->getActiveSheet()->getStyle('C')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
        $objPHPExcel->getActiveSheet()->getStyle('D')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
		$objPHPExcel->getActiveSheet()->getStyle('E')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 

  
        // 设置首行文字  
        $objPHPExcel->setActiveSheetIndex(0)  
            ->setCellValue('A1', '注册用户名')  
            ->setCellValue('B1', '注册手机号')  
            ->setCellValue('C1', '创建时间')  
            ->setCellValue('D1', '来源')  
            ->setCellValue('E1', '绑定房间数量'); 
  
        // 填充数据(UTF-8)  
        for($i=0;$i<count($ResultsData);$i++){  
			
				$objPHPExcel->getActiveSheet(0)->setCellValue('A'.($i+2), $ResultsData[$i]['username']);  
				$objPHPExcel->getActiveSheet(0)->setCellValue('B'.($i+2), $ResultsData[$i]['phone']); 
				$objPHPExcel->getActiveSheet(0)->setCellValue('C'.($i+2), date('Y-m-d H:i:s',$ResultsData[$i]['create_time']));   
				$objPHPExcel->getActiveSheet(0)->setCellValue('D'.($i+2), $ResultsData[$i]['fromobj']);
				$objPHPExcel->getActiveSheet(0)->setCellValue('E'.($i+2), $ResultsData[$i]['roomnum']);  
			
        }  
  
  
        // sheet命名  
        $objPHPExcel->getActiveSheet()->setTitle('用户信息');  
  
  
        //设置第一个页签
        $objPHPExcel->setActiveSheetIndex(0);  
  
        // excel头参数  
        header('Content-Type: application/vnd.ms-excel');  
        header('Content-Disposition: attachment;filename="用户信息('.date('Y-m-d').').xls"');  //日期为文件名后缀  
        header('Cache-Control: max-age=0');  
  
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  //excel5为xls格式，excel2007为xlsx格式  
        $objWriter->save('php://output'); 
	}
}

?>
