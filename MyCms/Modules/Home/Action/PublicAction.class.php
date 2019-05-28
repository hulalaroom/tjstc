<?php
class PublicAction extends HomeAction {

    function login() {
        $this->assign('topid', 20);
		//var_dump($_SESSION);exit;
        // if one of fields in $_SESSION, then show the login page
        if (!isset($_SESSION ['checklogin']) || !isset($_SESSION ['uname']) || !isset($_SESSION ['ugroup']) || !isset($_SESSION ['uid'])) {
			//var_dump($_SESSION);
            $this->display();
            return;
        }
		
        $session_config = C('SESSION_OPTIONS');
        if((time() - $_SESSION['last_access_time']) >  $session_config['expire']) {
            $this->display();
            return;
        }

		if(isset($_SESSION['userurl'])){
			$this->success('登录成功',$_SESSION['userurl']);
			unset($_SESSION['userurl']);
		}
		else {
            $this->redirect('User/index');
        }
    }

    function reg() {
        if (! isset($_SESSION ['checklogin']) || ! isset($_SESSION ['uname']) || ! isset($_SESSION ['ugroup']) || ! isset($_SESSION ['uid'])) {
            $this->display();
        } else {
            $this->redirect('User/index');
        }
    }

    function regok() {

        $user = D('User')->field('username,phone')->select();
        foreach ( $user as $key => $val ) {
            $username [] = $val ['username'];
            $phone [] = $val ['phone'];
        }
        if (in_array(trim($_POST ['name']), $username)) {
            echo_json('0', '操作失败', '用户名已存在', '', '60');
        }
		if (in_array(trim($_POST ['phone']), $phone)) {
            echo_json('0', '操作失败', '手机号已存在', '', '60');
        }

		if (session('phoneActiveCode') != $_POST ['phoneActiveCode']) {
			echo_json('0', '操作失败', '手机验证码错误，请核对！', '', '60');
		}
		if (time() > session('token_getpass_time')) {
			echo_json('0', '操作失败', '手机验证码过期！', '', '60');
		}

        $data = D('User')->create();
        $data ['username'] = $_POST ['name'];
		$data ['nickname'] = $_POST ['name'];
        $encrypt = "46faa8ab560de8e86ee20ce678eeb8"; // 加密码
        $password = md5(md5($encrypt) . md5(trim($_POST ['pass'])));
        $data ['password'] = $password;
        $data ['status'] = 1;
        session('disp_mail_flag', 0);
        session('phoneActiveCode', null);
        session('token_getpass_time', null);
        $data ['create_time'] = time();
        $data ['create_ip'] = get_client_ip();
        $data ['token'] = md5('my' . $data ['username'] . $data ['password'] . time()); // 创建用于激活识别码
        $data ['token_time'] = time() + 60 * 60 * 24; // 过期时间为24小时后
        $data ['group_id'] = 1; // 注册完成后，默认为第一个用户组
        $r = D('User')->add($data);
		$s = R('Api/userInsert', array($r,$_POST['name'],$password));

        if ($r === FALSE) {
            echo_json('0', '操作失败', '注册失败', '', '60');
        }
		else {
            $_SESSION ['linshi'] = $data ['username']; // 注册完临时用户名
            $_SESSION ['linshi_uid'] = $r; // 注册完临时用户名
            echo_json('1', '注册成功', '注册成功', U('Public/login'), '3');
        }
    }

    function reg2() {
		$phone =  $_SESSION['phone'];
		if(empty($phone)){
			$this->error('异常操作，请稍后重试');
		}
		$this->display();
    }

    function reg2ok() {

        if (empty($_POST ['name'])) {
            echo_json('0', '操作失败', '用户名不能为空', '', '60');
        }

	    $phone =  $_SESSION['phone'];
        $user = D('User')->field('username,email')->select();
        foreach ( $user as $key => $val ) {
            $username [] = $val ['username'];
            if ('' != $val ['email']) {
                $email [] = $val ['email'];
            }
        }
        if (in_array(trim($_POST['name']), $username)) {
            echo_json('0', '操作失败', '用户名已存在', '', '60');
        }
       if (empty($_POST ['pass'])) {
            echo_json('0', '操作失败', '用户密码不能为空', '', '2');
        }

		if ($_POST ['pass'] !== $_POST ['passok']) {
            echo_json('0', '操作失败', '两次密码不同', '', '2');
        }

        if (empty($_POST ['xieyi'])) {
            echo_json('0', '操作失败', '请阅读并同意服务条款', '', '60');
        }
        $data = D('User')->create();
        $data ['username'] =  trim($_POST ['name']);
		$data ['nickname'] =  trim($_POST ['name']);
        $encrypt = "46faa8ab560de8e86ee20ce678eeb8"; // 加密码
        $password = md5(md5($encrypt) . md5(trim($_POST ['pass'])));
        $data ['password'] = $password;
        $data ['status'] = 1;
		$data ['phone'] = $phone;
        session('disp_mail_flag', 0);
        session('phoneActiveCode', null);
        session('token_getpass_time', null);
        $data ['create_time'] = time();
        $data ['create_ip'] = get_client_ip();
        $data ['token'] = md5('my' . $data ['username'] . $data ['password'] . time()); // 创建用于激活识别码
        $data ['token_time'] = time() + 60 * 60 * 24; // 过期时间为24小时后
        $data ['group_id'] = 1; // 注册完成后，默认为第一个用户组
        $r = D('User')->add($data);
		//$s = R('Api/userInsert', array($r,$_POST['name'],$password));

        if ($r === FALSE) {
            echo_json('0', '操作失败', '注册失败', '', '60');
        }
		else {
			$_SESSION ['linshi'] = $data ['username']; // 注册完临时用户名
            $_SESSION ['linshi_uid'] = $r; // 注册完临时用户名

			$map['username'] = trim($_POST['name']);

			$map["password"] = $password;

			$authInfo = D('User')->where($map)->find();
			if (true == $authInfo) {
				if (isset($_SESSION ['linshi_email'])) {
					unset($_SESSION ['linshi']);
					unset($_SESSION ['linshi_uid']);
					unset($_SESSION ['linshi_email']);
					unset($_SESSION ['linshi_url']);
				}
				$_SESSION ['checklogin'] = md5(time() . $authInfo ['username']);
				$_SESSION ['uid'] = $authInfo ['id'];
				$_SESSION ['uname'] = $authInfo ['username'];
				$_SESSION ['uphone'] = $authInfo ['phone'];
				$_SESSION ['ugroup'] = $authInfo ['group_id'];
				$_SESSION ['unick'] = $authInfo ['nickname'];
				$_SESSION ['last_login_time'] = $authInfo ['last_login_time'];
				$_SESSION ['last_login_ip'] = $authInfo ['last_login_ip'];
				$_SESSION ['tags'] = $authInfo ['tag'];
				/* 积分规则存入session */
				$sr_list = D('ScoreRule')->where('status=1')->select();
				foreach ( $sr_list as $ru ) {
					$_SESSION ['rule'] [] = $ru ['module'] . '-' . $ru ['action'];
					$_SESSION ['score'] [] = $ru ['score'];
				}
				// 保存登录信息
				$data = array ();
				$data ['id'] = $authInfo ['id'];
				$data ['last_login_time'] = time();
				$data ['login_count'] = $authInfo ['login_count'] + 1;
				$data ['last_login_ip'] = get_client_ip();
				D('User')->save($data);
				echo_json('1', '登录成功', '登录成功', U('User/index'), '0');
			}
			else{
				echo_json('1', '操作失败', '登录失败，请重新登录', U('Public/login'), '0');
			}


        }
    }

    function reg3() {
        if (empty($_SESSION ['linshi_uid'])) {
            $this->redirect('Public/reg');
        } else {
            $this->display();
        }
    }

    // 登录检测
    public function checkLogin() {

        $way = $_POST ['way'];
		if($way ==1){
			 if (session('verify') != md5($_POST ['verify'])) {
				echo_json('0', '登录失败', '验证码错误', '', '10');
			}

			$username = trim($_POST['username']);
			$map['username'] = $username;
			$map['phone'] = $username;
			$map['_logic'] = 'or';
			$map2['_complex'] = $map;
			$encrypt = "46faa8ab560de8e86ee20ce678eeb8"; //加密码
			$map2["password"] = md5(md5($encrypt) . md5(trim($_POST['password'])));

			$authInfo = D('User')->where($map2)->find();

			// 使用用户名、密码和状态的方式进行认证
			if (false == $authInfo) {
				echo_json('0', '登录失败', '用户名或密码错误', '', '60');
			} else {
				session_start();
				
				/*if($authInfo['status'] == 0 || empty($authInfo)){
					echo_json('0', '登录失败', '您的账户尚未激活' , '', '60');
				}*/
                $_SESSION['last_access_time'] = time();
				if (isset($_SESSION ['linshi_email'])) {
					unset($_SESSION ['linshi']);
					unset($_SESSION ['linshi_uid']);
					unset($_SESSION ['linshi_email']);
					unset($_SESSION ['linshi_url']);
				}
				if($_POST['record'] == 'on'){
					$_SESSION ['username'] = $username;
				}
				else{
					$_SESSION ['username'] = '';
				}
				$_SESSION ['checklogin'] = md5(time() . $authInfo ['username']);
				$_SESSION ['uid'] = $authInfo ['id'];
				$_SESSION ['uname'] = $authInfo ['username'];
				$_SESSION ['ugroup'] = $authInfo ['group_id'];
				$_SESSION ['unick'] = $authInfo ['nickname'];
				$_SESSION ['uphone'] = $authInfo ['phone'];
				$_SESSION ['last_login_time'] = $authInfo ['last_login_time'];
				$_SESSION ['last_login_ip'] = $authInfo ['last_login_ip'];
				$_SESSION ['tags'] = $authInfo ['tag'];
				/* 积分规则存入session */
				$sr_list = D('ScoreRule')->where('status=1')->select();
				foreach ( $sr_list as $ru ) {
					$_SESSION ['rule'] [] = $ru ['module'] . '-' . $ru ['action'];
					$_SESSION ['score'] [] = $ru ['score'];
				}
				// 保存登录信息
				$data = array ();
				$data ['id'] = $authInfo ['id'];
				$data ['last_login_time'] = time();
				$data ['login_count'] = $authInfo ['login_count'] + 1;
				$data ['last_login_ip'] = get_client_ip();
				$data['login_state']=1;
				$data['sessionid']=session_id();
                   //   $type=array();
					 // $type['login_type']=3;
					 //D('User_login_log')->where('user_id='.$authInfo ['id'])->group('user_id')->save($type);
               
				D('User')->save($data);
				
				if($_SESSION['varname'] == "bs"){
					echo_json('1', '登录成功', '登录成功', U('guestbook/onlineRepair'), '0');
				}else if($_SESSION['varname'] =="zxjf"){
					echo_json('1', '登录成功', '登录成功', U('page/dqzd'), '0');
				}else if($_SESSION['varname'] =="vote"){
					echo_json('1', '登录成功', '登录成功', U('vote/index','id=21'), '0');
				}else if($_SESSION['varname'] =="ts"){
					echo_json('1', '登录成功', '登录成功', U('guestbook/index'), '0');
				}else{
					echo_json('1', '登录成功', '登录成功', U('User/index'), '0');
				}
			}
		}
		if($way == 2){
			 if (empty($_POST ['phoneActiveCode'])) {
				echo_json('0', '操作失败', '手机验证码不能为空', '', '60');
			}
			if (session('phoneActiveCode') != $_POST ['phoneActiveCode']) {
                echo_json('0', '操作失败', '手机验证码错误，请核对！', '', '60');
            }
			if (time() > session('token_getpass_time')) {
                echo_json('0', '操作失败', '手机验证码过期！', '', '60');
            }

            $_SESSION['last_access_time'] = time();

			$phone = trim($_POST['phone']);
			$_SESSION ['phone'] = $phone;
			$map['phone'] = $phone;
			$authInfo = D('User')->where($map)->find();
			if (false == $authInfo) {
				echo_json('1', '登录成功', '请完善用户信息', U('Public/reg2'), '0');
			} else {
				session_start();
				/*if($authInfo['status'] == 0 || empty($authInfo)){
					echo_json('0', '登录失败', '您的账户尚未激活' , '', '60');
				}*/
				if (isset($_SESSION ['linshi_email'])) {
					unset($_SESSION ['linshi']);
					unset($_SESSION ['linshi_uid']);
					unset($_SESSION ['linshi_email']);
					unset($_SESSION ['linshi_url']);
				}

				$_SESSION ['username'] = $authInfo ['username'];

				$_SESSION ['checklogin'] = md5(time() . $authInfo ['username']);
				$_SESSION ['uid'] = $authInfo ['id'];
				$_SESSION ['uname'] = $authInfo ['username'];
				$_SESSION ['ugroup'] = $authInfo ['group_id'];
				$_SESSION ['unick'] = $authInfo ['nickname'];
				$_SESSION ['uphone'] = $authInfo ['phone'];
				$_SESSION ['last_login_time'] = $authInfo ['last_login_time'];
				$_SESSION ['last_login_ip'] = $authInfo ['last_login_ip'];
				$_SESSION ['tags'] = $authInfo ['tag'];
				/* 积分规则存入session */
				$sr_list = D('ScoreRule')->where('status=1')->select();
				foreach ( $sr_list as $ru ) {
					$_SESSION ['rule'] [] = $ru ['module'] . '-' . $ru ['action'];
					$_SESSION ['score'] [] = $ru ['score'];
				}
				// 保存登录信息
				$data = array ();
				$data ['id'] = $authInfo ['id'];
				$data ['last_login_time'] = time();
				$data ['login_count'] = $authInfo ['login_count'] + 1;
				$data ['last_login_ip'] = get_client_ip();
				$data['login_state']=1;
				$data['sessionid']=session_id();
				D('User')->save($data);
				if($_SESSION['varname'] == "bs"){
					echo_json('1', '登录成功', '登录成功', U('guestbook/onlineRepair'), '0');
				}else if($_SESSION['varname'] =="zxjf"){
					echo_json('1', '登录成功', '登录成功', U('page/dqzd'), '0');
				}else if($_SESSION['varname'] =="ts"){
					echo_json('1', '登录成功', '登录成功', U('guestbook/index'), '0');
				}else if($_SESSION['varname'] =="vote"){
					echo_json('1', '登录成功', '登录成功', U('vote/index','id=21'), '0');
				}else{
					echo_json('1', '登录成功', '登录成功', U('User/index'), '0');
				}
			}
		}
    }

    public function verify() {

        $type = isset($_GET ['type']) ? $_GET ['type'] : 'gif';
        import("ORG.Util.Image");
        ob_end_clean();
        Image::buildImageVerify(4, 1, $type,108,47);
    }

    // Ins+ by lzhang 2014-02-23 ---------------------------------------------------
    public function get_phone_verify_code() {

        import('ORG.Util.String');
        // 6位手机验证码
        $randval = String::randString(6, 1);
		$notice = '';
        if (empty($_POST ['phone']) && ! empty($_POST ['username'])) {
            //$phone = D('User')->where("username= '" . $_POST ['username'] . "'")->getField('phone');
			/*查询解绑房间编号*/
			if($_POST ['objid'] == 'getVerifyCodeBtnUnbind'){
				$map['id'] = $_POST ['username'];
				$infor = D('UserRoom')->where($map)->Field('houseCode')->find();
				$housecode = $infor['houseCode'];
			}
			//绑定房间
			if($_POST ['objid'] == 'getVerifyCodeBtn'){
				$housecode = $_POST ['username'];
			}
			$url = C('DB_ORACLE');
			$owner = M('',null,$url);

			$list = $owner->query("select MOBILEPHONE from fc_owner where HOUSECODE = '$housecode' and ISOWNER = 1");
			$phone = $list[0]['MOBILEPHONE'];
			//找回密码
			if($_POST ['objid'] == 'getPassBack'){
				$map['username'] = $_POST['username'];
				$infor = D('User')->where($map)->Field('phone')->find();
				$phone = $infor['phone'];
			}
			// Mod+ by wangshipeng 2015-02-26 ---------------------------------------------------
			if (empty($phone)) {
				$data = array (
					'code' => -2,
					'notice' => "您的房间未在结算平台预留联系电话,请到能源服务窗口进行咨询!"
				);
				echo json_encode($data);
				exit();
			}
			// Mod- by wangshipeng 2015-02-26 ---------------------------------------------------
			$phone_1 = substr($phone,7,4);
			$notice = "已将验证码发送到*******".$phone_1."，请及时查看，有效期为5分钟，若有疑问，请到能源公司或者拨打022-66885890进行咨询！";
            $_POST ['phone'] = $phone;
        }
        // send message to regist phone
        $ret = sendPhoneVerifyCode($_POST ['phone'], $randval);
		//$ret = 0;
        if (0 == $ret ['error'] && ((!empty($phone)) || (!empty($_POST ['phone'])))) {
            session('phoneActiveCode', $randval);
            $data = array (
                'code' => 'success',
				'notice' => $notice
            );
            session('token_getpass_time', time() + 300);
        } else {
            $data = array (
                'code' => - 1,
				'notice' => ''
            );
        }
        echo json_encode($data);
        exit();
    }
    // Ins- by lzhang 2014-02-23 ---------------------------------------------------

	 // start add by wangshipeng 2014-06-06 ---------------------------------------------------
    /*public function phone_notice() {

        if (empty($_POST ['phone']) && ! empty($_POST ['username'])) {
            $phone = D('User')->where("username= '" . $_POST ['username'] . "'")->getField('phone');
			$nickname = D('User')->where("username= '" . $_POST ['username'] . "'")->getField('nickname');
			$userid = D('User')->where("username= '" . $_POST ['username'] . "'")->getField('id');
			$houseNum = D('User')->where("uid= '" . $userid . "'")->getField('houseNum');

			// 解绑或者绑定的发送内容
			if($_POST ['qf'] == 1){
				$diff = “已成功解绑”;
			}
			else{
				$diff = “已绑定成功”;
			}

			$randval = “尊敬的”.“:您的房间-”;

            if (empty($phone)) {
                $data = array (
                    'code' => -2
                );
                echo json_encode($data);
                exit();
            }

            $_POST ['phone'] = $phone;
        }
        // send message to regist phone
        $ret = sendPhoneVerifyCode($_POST ['phone'], $randval);

        echo json_encode($data);
        exit();
    }*/
    // end add by wangshipeng 2014-06-06 ---------------------------------------------------

    // 用户登出
    public function logout() {

	        if (isset($_SESSION ['checklogin']) || isset($_SESSION ['uname']) || isset($_SESSION ['ugroup']) || isset($_SESSION ['uid'])) {
				echo session_id();
			$data = array ();
			$data ['id'] = $_SESSION ['uid'];
			$data['login_state']=0;
			D('User')->save($data);
			//echo D('User')->getlastsql();
            unset($_SESSION ['checklogin']);
            unset($_SESSION ['uname']);
            unset($_SESSION ['ugroup']);
            unset($_SESSION ['uid']);
            unset($_SESSION ['unick']);
            unset($_SESSION ['last_login_time']);
            unset($_SESSION ['last_login_ip']);
            unset($_SESSION ['rule']);
            unset($_SESSION ['score']);
            unset($_SESSION ['verify']);
            unset($_SESSION ['linshi']);
            unset($_SESSION ['linshi_uid']);
            unset($_SESSION ['linshi_email']);
            unset($_SESSION ['linshi_url']);
            unset($_SESSION ['tags']);
			unset($_SESSION ['varname']);
			unset($_SESSION ['enjoy']);
            unset($_SESSION ['last_access_time']);

             session_destroy();
        }
        $this->redirect('index/index');
    }

    // 激活
    public function active() {
        if (! empty($_GET ['verify'])) {

            $map ['token'] = trim($_GET ['verify']);
            //$map ['status'] = 0;
            $token_time = D('User')->where($map)->getField('token_time');
            if ($token_time < time()) {
                $this->error('该链接已过期！');
            } else {
                $info = D('User')->where($map)->setField('status', '1');
                if ($info !== false) {
                    $this->success('激活成功！', '__URL__/login');
                }
            }
        } else {
            $this->error('参数错误！');
        }
    }

    // 找回密码
    public function getpass() {
        $this->display();
    }

    // 找回密码处理程序
    public function getpassok() {

		if (empty($_POST ['phoneActiveCode'])) {
			echo_json('0', '提示', '手机验证码不能为空', '', '60');
		} elseif (session('phoneActiveCode') != $_POST ['phoneActiveCode']) {
			echo_json('0', '提示', '手机验证码错误，请核对！', '', '60');
		}  elseif (time() > session('token_getpass_time')) {
			echo_json('0', '提示', '手机验证码过期！', '', '60');
		} elseif (empty($_POST['verify'])) {
			echo_json('0', '提示', '验证码不能为空', '', '60');
		} elseif (session('verify') != md5($_POST ['verify'])) {
			echo_json('0', '提示', '验证码错误', '', '60');
		}
		$phone = trim($_POST['phone']);
		$map['phone'] = $phone;
		$encrypt = "46faa8ab560de8e86ee20ce678eeb8"; //加密码
		$password = md5(md5($encrypt) . md5(trim($_POST['pass'])));
		$authInfo = D('User')->where($map)->find();
		if($authInfo['password'] == $password){
			echo_json('1', '提示', '设置失败，请勿使用和之前一样的密码', '', '60');
		}
        // 保存登录信息
		$data = array ();
		$data ['id'] = $authInfo['id'];
		$ar = array('password'=>$password);
		D('User') ->where($data)->save($ar);
        echo_json('1', '提示', '设置成功', U('Public/login'), '60');

    }

    public function getpass_ok() {
        if (empty($_SESSION ['linshi_email'])) {
            $this->redirect('Index/index');
        } else {
            $this->display();
        }
    }

    function repass() {
        $verify = $this->_get('verify');
        if (! empty($verify)) {
            $re = D('User')->where("token='" . $verify . "'")->find();
            if ($re !== null && $re !== FALSE) {
                $this->assign('email', $re ['email']);
                $this->assign('username', $re ['username']);
                $this->display();
            } else {
                $this->error('该链接无效或已过期！');
            }
        } else {
            $this->error('该链接无效！');
        }
    }

    function repassok() {
        $verify = $this->_post('verify');
        if (! empty($verify)) {
            $re = D('User')->where("token='" . $verify . "'")->find();
            if ($re ['token_getpass_time'] >= time()) {
                $encrypt = "46faa8ab560de8e86ee20ce678eeb8"; // 加密码
                $password = md5(md5($encrypt) . md5(trim($_POST ['pass'])));
                $data ['password'] = $password;

                $data ['token'] = '';
                $data ['token_getpass_time'] = 0;
                $r = D('User')->where("token='" . $verify . "'")->setField($data);
                if ($r !== false) {
                    echo_json('1', '重置密码成功', '重置密码成功', U('Public/logout'), '60');
                }
            } else {
                echo_json('0', '重置密码失败', '链接超时', '', '60');
            }
        } else {
            $this->redirect('Index/index');
        }
    }

    // 检查用户是否存在
    public function check_user() {
        $request = urldecode(trim($_POST ['name']));
        $username = D("User")->field("username")->select();
        if (in_array(array("username" => $request), $username)) {
            echo "false";
        } else {
            echo "true";
        }
        exit();
    }

    // 检查用户email是否存在
    public function check_email() {
        $request = urldecode(trim($_POST ['email']));
        $email = D("User")->field("email")->select();
        if (in_array(array("email" => $request), $email)) {
            echo "true"; // 存在返回true
        } else {
            echo "false";
        }
        exit();
    }

    // 检查验证码
    public function check_verify() {
        $request = urldecode(trim($_POST ['verify']));
        if (session('verify') != md5($request)) {
            echo "false";
        } else {
            echo "true";
        }
        exit();
    }
}

?>
