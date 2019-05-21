<?php

class UserAction extends HomeAction {
    public function _initialize() {
        parent::_initialize();
        $this->assign('topid', '20'); //顶部导航选中效果。此处选择用户中心栏目id即可
    }

    public function _empty(){
        $this->redirect("Public/404");
    }

    // 我的5890
    public function index() {
        $this->check();
        //获取房间信息
        $houseCode = R('User/fjbh', array($_SESSION['uid']));
        $info = D('user')->where('id=' . $_SESSION['uid'])->find();
        if(empty($info)){
            $this->redirect("Public/404");
        }

        $year = date('Y');
        $strtime = date('Y')."-6-1 00:00:00";
        if(strtotime(date('Y-m-d H:i:s'))<=strtotime($strtime)){
            $year = date('Y')-1;
        }
        $sj = $year.'-'.($year+1);

        /* 查找已绑定的房间(身份为房主或者租客均可) */
        $this->houselist = R('User/fjbd', array($_SESSION['uid']));
        if (!empty($this->houselist) && !isset($_SESSION['selected_houseCode'])) {
            // the first selected house' code
            $_SESSION['selected_houseCode'] = $this->houselist[0]['houseCode'];
        }

        $this->now = date('Y', time());
        $this->assign($info);
        $this->assign('current_sel', $_SESSION['selected_houseCode']);
        $this->assign('title', '我的5890');
        $this->sj = $sj;

        $this->id = $this->_get('id');
        $this->nav = $this->nav($this->cate, $this->id, ''); //位置导航
		$this->assign('areaid',$this->id);
        $this->display();
    }

    public function jfyw() {
        $this->check();
		$this->hcode=$_POST['hcode'];
        $this->display();
    }

    public function ywbl() {
        $this->check();
        $this->zss=$_SESSION['jump2'];
        $this->hb=$_SESSION['jump3'];
        $this->display();
    }

    public function lssh() {
        $this->check();
        $this->display();
    }

    public function fjxx() {
        $this->check();

        $houseCode = R('User/fjbh', array($_SESSION['uid']));
        $info = D('user')->where('id=' . $_SESSION['uid'])->find();
        if(empty($info)){
            $this->redirect("Public/404");
        }
        $year = date('Y');
        $strtime = date('Y')."-6-1 00:00:00";
        if(strtotime(date('Y-m-d H:i:s'))<=strtotime($strtime)){
            $year = date('Y')-1;
        }
        $sj = $year.'-'.($year+1);
        /* 查找已绑定的房间(身份为房主或者租客均可) */
        $houselist = R('User/fjbd', array($_SESSION['uid']));
        $this->houselist = $houselist;
        $this->now = date('Y', time());
        $this->assign($info);
        $this->sj = $sj;
        $diyi = $_GET['diyi'];
        $this->diyi = $diyi;
        if($_GET['diyi']){$this->chushi = $houselist[$diyi]['houseCode'];}
        else{$this->chushi = $houselist[0]['houseCode'];}

        $this->display();
    }

    public function ny() {
        $this->check();
		if (!isset($_SESSION['selected_houseCode'])) {
            $houselist = R('User/fjbd', array($_SESSION['uid']));//用户绑定房间
            if (empty($houselist)) {
                $this->redirect('User/bind');
            } else {
                $_SESSION['selected_houseCode'] = $houselist[0]['houseCode'];
            }
        }
		$stopstatus = R('Api/stopstatus', array($_SESSION['selected_houseCode']));
		$res = $stopstatus->{'r_body'}[0];
		if($res->{'STOPSTATUS'} == '停供'){
			$tingong=1;
		}else{
			$tingong=0;
		}
		$this->tingong = $tingong;
        //print_r($_SESSION['selected_houseCode_name']);
        $this->display();
    }
// 请确认您已认真阅读并同意申请人应履行的责任
    /**
     * 能源公司->恢复供热申请Info
     */
    public function reheatInfo() {
        // check user login status
        $this->check();

        if (!isset($_SESSION['selected_houseCode'])) {
            $houselist = R('User/fjbd', array($_SESSION['uid']));//用户绑定房间
            if (empty($houselist)) {
                $this->redirect('User/bind');
            } else {
                $_SESSION['selected_houseCode'] = $houselist[0]['houseCode'];
            }
        }

        $this->display();
    }

    /**
     * 能源公司->恢复供热申请
     */
    public function reheat() {
        // check user login status
        $this->check();

        if (!isset($_SESSION['selected_houseCode'])) {
            $houselist = R('User/fjbd', array($_SESSION['uid']));//用户绑定房间
            if (empty($houselist)) {
                $this->redirect('User/bind');
            } else {
                $_SESSION['selected_houseCode'] = $houselist[0]['houseCode'];
            }
        }

        $calllist = R('Api/callMyBusinessListInterface', array($_SESSION['selected_houseCode']));
        for($k = 0; $k < count($calllist->{'r_body'}); $k++){
            if($calllist->{'r_body'}[$k]->{"BUSINESSTYPE"} == '恢复供热申请' && $calllist->{'r_body'}[$k]->{"EXAMINESTATUS"} == '审核中'){
                 $this->housecode=$_SESSION['selected_houseCode'];
                 $this->date='';
                 $this->msg='同类型审核正在处理中';
                 $this->display('bgsjhOk');die();
            }
        }

        $info = D('user')->where('id=' . $_SESSION['uid'])->find();
        if(empty($info)){
            $this->redirect("Public/404");
        }

        $houseInfo = R('Api/getHouse', array($_SESSION['selected_houseCode']));

		$url =  C('CALL_URL') . '/GetRecoveryYearServlet';
		$post_data ="";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		$output = curl_exec($ch);
		$date=json_decode($output,true);
		$chargeMonth=$date['r_body'][0]["YEAR"];
        $oldphone = $houseInfo['MOBILEPHONE'];
        $phone = R('Api/maskPhoneNumber', array($oldphone));

        $this->assign('phone', $phone);
        $this->assign('oldphone', $oldphone);
        $this->housecode=$_SESSION['selected_houseCode'];
        $this->owername=$_SESSION['selected_houseCode_name'];
        //$this->chargeyear=$chargeMonth;

        $this->assign('houseInfo', $houseInfo);
        $this->assign('chargeMonth', $chargeMonth);
        $this->assign('userName', $info['nickname']);

        $this->display();
    }

    public function reheatOk() {
        $this->check();

        $housecode=$_POST['housecode'];
        $owername=$_POST['owername'];
        $chargeyear=$_POST['chargeyear'];
		//var_dump($chargeyear);exit;
        if($_FILES['sfz']['name'][0] && $_FILES['sfz']['name'][1]){
            import('@.ORG.UploadFile');
            $upload = new UploadFile(); // 实例化上传类
            $upload->maxSize = 2 * 1024 * 1024; //设置上传图片的大小2mb
            $upload->allowExts = array('jpg'); //设置上传图片的后缀
            // $upload->uploadReplace = true;     //同名则替换
            $upload->saveRule = 'uniqid'; //设置上传头像命名规则(临时图片),修改了UploadFile上传类
            //完整的头像路径
            $path = './Uploads/shenhe/';
            $upload->savePath = $path;
            if (!$upload->upload()) { // 上传错误提示错误信息
                 $this->housecode=$_POST['housecode'];
                 $this->date='';
                 $this->msg='上传图片时失败';
                 $this->display('bgsjhOk');
            } else { // 上传成功 获取上传文件信息
                $info = $upload->getUploadFileInfo();
				$imgnew1=$info[0]['savepath'].$info[0]['savename'];
				$img1name=$info[0]['savename'];
				$imgnew2=$info[1]['savepath'].$info[1]['savename'];
				$img2name=$info[1]['savename'];
				
				$base641 = file_get_contents($imgnew1);
                $base64img1 =base64_encode($base641);
				$base642 = file_get_contents($imgnew2);
                $base64img2 =base64_encode($base642);
				
						$imgarr = "[{'fileName': '".$img1name."','fileBase64':'". $base64img1."','fileType':'身份证'},{'fileName':'".$img2name."','fileBase64':'".$base64img2."','fileType':'身份证'}]";
                            //如果都成功，调用接口存储数据
                            $houseInfo = R('Api/SubmitRecoveryProcessServlet', array('恢复供热申请',$housecode,$owername,$chargeyear,'网站',$imgarr));
                            if($houseInfo->{'r_code'} == '0000'){
                                $this->housecode=$_POST['housecode'];
                                $this->date=date('Y').'年'.date('m').'月'.date('d').'日';
                                $this->display('bgsjhOk');
                            }elseif($houseInfo->{'r_code'} == '8008'){
                                $this->housecode=$_POST['housecode'];
                                $this->date='';
                                $this->msg='您有未审核完成的申请，待审核完成后可以再次提交。';
                                $this->display('bgsjhOk');
                            }else{
                                $this->housecode=$_POST['housecode'];
                                $this->date='';
                                $this->msg='上传图片时失败!';
                                $this->display('bgsjhOk');
                            }
                        

            }
        }else{
            $this->housecode=$_POST['housecode'];
             $this->date='';
             $this->msg='缺少上传图片';
             $this->display('bgsjhOk');
        }

    }

    /**
     * 能源公司->绑定能源卡Info
     */
    public function nykInfo() {
        // check user login status
        $this->check();

        if (!isset($_SESSION['selected_houseCode'])) {
            $houselist = R('User/fjbd', array($_SESSION['uid']));//用户绑定房间
            if (empty($houselist)) {
                $this->redirect('User/bind');
            } else {
                $_SESSION['selected_houseCode'] = $houselist[0]['houseCode'];
            }
        }

        $this->display();
    }

    /**
     * 能源公司->绑定能源卡
     */
    public function nyk() {
        // check user login status
        $this->check();

        if (IS_GET) {
            $this->error('非法的请求!');
        }

        if (!isset($_POST['accept']) || $_POST['accept'] != 1) {
            $this->error('请确认您已认真阅读并同意绑定/变更能源卡提示!');
        }

        if (!isset($_SESSION['selected_houseCode'])) {
            $houselist = R('User/fjbd', array($_SESSION['uid']));//用户绑定房间
            if (empty($houselist)) {
                $this->redirect('User/bind');
            } else {
                $_SESSION['selected_houseCode'] = $houselist[0]['houseCode'];
            }
        }

        $info = D('user')->where('id=' . $_SESSION['uid'])->find();
        if(empty($info)){
            $this->redirect("Public/404");
        }

        $calllist = R('Api/callMyBusinessListInterface', array($_SESSION['selected_houseCode']));
        for($k = 0; $k < count($calllist->{'r_body'}); $k++){
            if($calllist->{'r_body'}[$k]->{"BUSINESSTYPE"} == '变更能源卡' && $calllist->{'r_body'}[$k]->{"EXAMINESTATUS"} == '审核中'){
                 $this->housecode=$_SESSION['selected_houseCode'];
                 $this->date='';
                 $this->msg='同类型审核正在处理中';
                 $this->display('bgsjhOk');die();
            }
        }

        //小区编号是：5002或5004的是建行，其他的都是中行
        $newcode = substr($_SESSION['selected_houseCode'],0,4);

        $houseInfo = R('Api/getHouse', array($_SESSION['selected_houseCode']));

        $phone = $houseInfo['MOBILEPHONE'];
        $this->oldphone= $phone;
        $phone = R('Api/maskPhoneNumber', array($phone));
        $this->housecode=$_SESSION['selected_houseCode'];
        $this->owername=$_SESSION['selected_houseCode_name'];

        $this->assign('phone', $phone);
        $this->assign('newcode', $newcode);
        $this->assign('houseInfo', $houseInfo);
        $this->assign('userName', $info['nickname']);

        $this->display();
    }

    /**
     * 能源公司->阶梯价基数核增Info
     */
    public function jtjhzInfo() {
        // check user login status
        $this->check();

        if (!isset($_SESSION['selected_houseCode'])) {
            $houselist = R('User/fjbd', array($_SESSION['uid']));//用户绑定房间
            if (empty($houselist)) {
                $this->redirect('User/bind');
            } else {
                $_SESSION['selected_houseCode'] = $houselist[0]['houseCode'];
            }
        }

        $this->display();
    }

    /**
     * 能源公司->手机号变更提示Infos
     */
    public function bgsjhInfo() {
        // check user login status
        $this->check();

        if (!isset($_SESSION['selected_houseCode'])) {
            $houselist = R('User/fjbd', array($_SESSION['uid']));//用户绑定房间
            if (empty($houselist)) {
                $this->redirect('User/bind');
            } else {
                $_SESSION['selected_houseCode'] = $houselist[0]['houseCode'];
            }
        }

        $this->display();
    }

    /**
     * 能源公司->手机号变更
     */
    public function bgsjh() {
        // check user login status
        $this->check();
        $this->housecode=$_SESSION['selected_houseCode'];
        $this->owername=$_SESSION['selected_houseCode_name'];
        $this->display();
    }

    /**
     * 能源公司->手机号变更OK
     */
    public function bgsjhOk() {
        // check user login status
        $this->check();

        $houselist = R('Api/chargePhone', array($_SESSION['selected_houseCode'],$_POST['phone'],$_POST['owername'],'网站'));//用户绑定房间
        $this->housecode=$_SESSION['selected_houseCode'];
        if($houselist->{'r_code'} == '0000'){
            $this->phone=$_POST['phone'];
        }else{
            $this->phone='';
        }
        $this->type=1;
        $this->display();
    }

    public function nykcg() {
        $this->check();

        $housecode=$_POST['housecode'];
        $owername=$_POST['owername'];
        $bank=$_POST['bank'];
        $bankNo=$_POST['yinhang1'].$_POST['yinhang2'].$_POST['yinhang3'].$_POST['yinhang4'];
        $youxiaoqi=$_POST['yue'].'/'.$_POST['nian'];


        if($_FILES['sfz']){
            import('@.ORG.UploadFile');
            $upload = new UploadFile(); // 实例化上传类
            $upload->maxSize = 2 * 1024 * 1024; //设置上传图片的大小2mb
            $upload->allowExts = array('jpg'); //设置上传图片的后缀
            // $upload->uploadReplace = true;     //同名则替换
            $upload->saveRule = 'uniqid'; //设置上传头像命名规则(临时图片),修改了UploadFile上传类
            //完整的头像路径
            $path = './Uploads/shenhe/';
            $upload->savePath = $path;
            if (!$upload->upload()) { // 上传错误提示错误信息
                 $this->housecode=$_POST['housecode'];
                 $this->date='';
                 $this->msg='上传图片时失败';
                 //$this->msg=$upload->getErrorMsg();
                 $this->display();
            } else { // 上传成功 获取上传文件信息
                $info = $upload->getUploadFileInfo();
                $imgnew=$info[0]['savepath'].$info[0]['savename'];
				$base64 = file_get_contents($imgnew);
                $base64img =base64_encode($base64);
                            $houseInfo = R('Api/SubmitBankProcessServlet', array('变更能源卡',$housecode,$owername,$bank,$bankNo,$youxiaoqi,'网站',$base64img,$info[0]['savename']));
                            if($houseInfo->{'r_code'} == '0000'){
                                $this->housecode=$_POST['housecode'];
                                $this->date=date('Y').'年'.date('m').'月'.date('d').'日';
                                $this->display();
                            }elseif($houseInfo->{'r_code'} == '8008'){
                                $this->housecode=$_POST['housecode'];
                                $this->date='';
                                $this->msg='您有未审核完成的申请，待审核完成后可以再次提交。';
                                $this->display('bgsjhOk');
                            }else{
                                $this->housecode=$_POST['housecode'];
                                $this->date='';
                                $this->msg='上传图片时失败!';
                                $this->display();
                            }
                        
                    
                
            }
        }else{
            $this->housecode=$_POST['housecode'];
             $this->date='';
             $this->msg='缺少上传图片';
             $this->display();
        }
    }
    /**
     * 能源公司->阶梯价基数核增
     */
    public function jtjhz() {
        // check user login status
        $this->check();

        if (IS_GET) {
            $this->error('非法的请求!');
        }

        if (!isset($_POST['accept']) || $_POST['accept'] != 1) {
            $this->error('请确认您已认真阅读并同意阶梯价基数核增提示!');
        }

        if (!isset($_SESSION['selected_houseCode'])) {
            $houselist = R('User/fjbd', array($_SESSION['uid']));//用户绑定房间
            if (empty($houselist)) {
                $this->redirect('User/bind');
            } else {
                $_SESSION['selected_houseCode'] = $houselist[0]['houseCode'];
            }
        }

        $info = D('user')->where('id=' . $_SESSION['uid'])->find();
        if(empty($info)){
            $this->redirect("Public/404");
        }

        /*$calllist = R('Api/callMyBusinessListInterface', array($_SESSION['selected_houseCode']));
        for($k = 0; $k < count($calllist->{'r_body'}); $k++){
            if($calllist->{'r_body'}[$k]->{"BUSINESSTYPE"} == '阶梯基增' && $calllist->{'r_body'}[$k]->{"EXAMINESTATUS"} == '审核中'){
                 $this->housecode=$_POST['housecode'];
                 $this->date='';
                 $this->msg='同类型审核正在处理中';
                 $this->display('bgsjhOk');die();
            }
        }*/

        $houseInfo = R('Api/getHouse', array($_SESSION['selected_houseCode']));

        $phone = $houseInfo['MOBILEPHONE'];
        $this->oldphone= $phone;
        $phone = R('Api/maskPhoneNumber', array($phone));
        $this->housecode=$_SESSION['selected_houseCode'];
        $this->owername=$_SESSION['selected_houseCode_name'];

        $this->assign('phone', $phone);
        $this->assign('houseInfo', $houseInfo);

        $this->display();
    }
    /**
     * 能源公司->燃气开通
     */
    public function rqktInfo() {

        $this->check();

        if (!isset($_SESSION['selected_houseCode'])) {
            $houselist = R('User/fjbd', array($_SESSION['uid']));//用户绑定房间
            if (empty($houselist)) {
                $this->redirect('User/bind');
            } else {
                $_SESSION['selected_houseCode'] = $houselist[0]['houseCode'];
            }
        }

        $this->display();
    }
	    public function rqkt() {

        $this->check();

        if (IS_GET) {
            $this->error('非法的请求!');
        }

        if (!isset($_POST['accept']) || $_POST['accept'] != 1) {
            $this->error('请确认您已认真阅读并同意绑定/变更能源卡提示!');
        }

        if (!isset($_SESSION['selected_houseCode'])) {
            $houselist = R('User/fjbd', array($_SESSION['uid']));//用户绑定房间
            if (empty($houselist)) {
                $this->redirect('User/bind');
            } else {
                $_SESSION['selected_houseCode'] = $houselist[0]['houseCode'];
            }
        }

        $info = D('user')->where('id=' . $_SESSION['uid'])->find();
        if(empty($info)){
            $this->redirect("Public/404");
        }


        $houseInfo = R('Api/getHouse', array($_SESSION['selected_houseCode']));

        $phone = $houseInfo['MOBILEPHONE'];
        $this->oldphone= $phone;
        $phone = R('Api/maskPhoneNumber', array($phone));
        $this->housecode=$_SESSION['selected_houseCode'];
        $this->owername=$_SESSION['selected_houseCode_name'];
		$time=date("Y-m-d");

		$this->assign('time', $time);
        $this->assign('phone', $phone);
        $this->assign('houseInfo', $houseInfo);
        $this->assign('userName', $info['nickname']);

        $this->display();
    }
    public function rqktcg() {
        $this->check();

        $housecode=$_POST['housecode'];
        $owername=$_POST['owername'];
        $nphone=$_POST['nphone'];
		$time=$_POST['time'];
		$houseInfo = R('Api/SubmitGasOpeningApplyServlet', array('燃气开通',$housecode,$owername,$time,$nphone,'网站'));
		if($houseInfo->{'r_code'} == '0000'){
                                $this->housecode=$_POST['housecode'];
                                $this->date=date('Y').'年'.date('m').'月'.date('d').'日';
                                $this->display('bgsjhOk');
                            }elseif($houseInfo->{'r_code'} == '8008'){
                                $this->housecode=$_POST['housecode'];
                                $this->date='';
                                $this->msg='您有未审核完成的申请，待审核完成后可以再次提交。';
                                $this->display('bgsjhOk');
                            }else{
                                $this->housecode=$_POST['housecode'];
                                $this->date='';
                                $this->msg='申请失败,请稍后尝试!';
                                $this->display('bgsjhOk');
                            }
    }
    /**
     * 能源公司->燃气开通结束
     */
    public function jtjhzcg() {
        $this->check();
		//var_dump($_POST);exit;
        $housecode=$_POST['housecode'];
        $owername=$_POST['owername'];
        if($_POST['renshu'] == '10人'){
            $renshu=10;
        }else{
            $renshu=substr($_POST['renshu'], 0,1);
        }

        import('@.ORG.UploadFile');
        $upload = new UploadFile(); // 实例化上传类
        $upload->maxSize = 2 * 1024 * 1024; //设置上传图片的大小2mb
        $upload->allowExts = array('jpg'); //设置上传图片的后缀
        // $upload->uploadReplace = true;     //同名则替换

        $upload->saveRule = 'uniqid'; //设置上传头像命名规则(临时图片),修改了UploadFile上传类
        //完整的头像路径
        $path = './Uploads/shenhe/';
        $upload->savePath = $path;
        if (!$upload->upload()) { // 上传错误提示错误信息
             $this->housecode=$_POST['housecode'];
             $this->date='';
             //$this->msg='上传图片时失败';
             $this->msg=$this->error($upload->getErrorMsg());
             $this->display();
        } else { // 上传成功 获取上传文件信息
            $info = $upload->getUploadFileInfo();
				$imgnew1=$info[0]['savepath'].$info[0]['savename'];
				$result0=$info[0]['savename'];
				$imgnew2=$info[1]['savepath'].$info[1]['savename'];
				$result1=$info[1]['savename'];
				$imgnew3=$info[2]['savepath'].$info[2]['savename'];
				$result2=$info[2]['savename'];
				$imgnew4=$info[3]['savepath'].$info[3]['savename'];
				$result3=$info[3]['savename'];
				
				$base641 = file_get_contents($imgnew1);
                $base64img1 =base64_encode($base641);
				$base642 = file_get_contents($imgnew2);
                $base64img2 =base64_encode($base642);
				$base643 = file_get_contents($imgnew3);
                $base64img3 =base64_encode($base643);
				$base644 = file_get_contents($imgnew4);
                $base64img4 =base64_encode($base644);
				$enjoy1= "'fileBase64': '".$base64img1."','fileName': '".$result0."','fileType': '身份证','memberName': '','index':''";
				$enjoy2= "'fileBase64': '".$base64img2."','fileName': '".$result1."','fileType': '身份证','memberName': '','index':''";
				$enjoy3= "'fileBase64': '".$base64img3."','fileName': '".$result2."','fileType': '房产证','memberName': '','index':''";
				$enjoy4= "'fileBase64': '".$base64img4."','fileName': '".$result3."','fileType': '户口薄主页','memberName': '','index':''";


                    $lastnum = 4;//从3开始计数，获取上传叔祖的信息
                    if($result0 && $result1 && $result2 && $result3){
                        if($_POST['hnhw1'] == '1'){
							$ret1=$info[$lastnum]['savename'];
							$imgret1=$info[$lastnum]['savepath'].$info[$lastnum]['savename'];
							$base64ret1 = file_get_contents($imgret1);
							$base64imgret1 =base64_encode($base64ret1);

							$hnhw1= "'fileBase64': '".$base64imgret1."','fileName': '".$ret1."','fileType': '户口薄','memberName': '".$_POST['xingming1']."','index': 1";

                            $lastnum = $lastnum+1;
                        }else{
							$ret101=$info[$lastnum]['savename'];
							$imgret1=$info[$lastnum]['savepath'].$info[$lastnum]['savename'];
							$base64ret1 = file_get_contents($imgret1);
							$base64imgret1 =base64_encode($base64ret1);

							$hnhw1= "'fileBase64': '".$base64imgret1."','fileName': '".$ret1."','fileType': '居住证','memberName': '".$_POST['xingming1']."','index': 2";

							

                            $lastnum = $lastnum+1;
                        }
                        if(($_POST['hnhw1'] == '1' && $ret1) || ($ret1 && $ret2)){
                            if($_POST['hnhw2'] == '1'){
								$ret2=$info[$lastnum]['savename'];
								$imgret2=$info[$lastnum]['savepath'].$info[$lastnum]['savename'];
								$base64ret2 = file_get_contents($imgret2);
								$base64imgret2 =base64_encode($base64ret2);

								$hnhw2= "'fileBase64': '".$base64imgret2."','fileName': '".$ret2."','fileType': '户口薄','memberName': '".$_POST['xingming2']."','index': 3";

                                $lastnum = $lastnum+1;
                            }else{
                                $ret2=$info[$lastnum]['savename'];
								$imgret2=$info[$lastnum]['savepath'].$info[$lastnum]['savename'];
								$base64ret2 = file_get_contents($imgret2);
								$base64imgret2 =base64_encode($base64ret2);

								$hnhw2= "'fileBase64': '".$base64imgret2."','fileName': '".$ret2."','fileType': '居住证','memberName': '".$_POST['xingming2']."','index': 4";


								$lastnum = $lastnum+1;
                            }
                            if(($_POST['hnhw2'] == '1' && $ret1) || ($ret1 && $ret2)){
                                if($_POST['hnhw3'] == '1'){
                                    $ret3=$info[$lastnum]['savename'];
									$imgret3=$info[$lastnum]['savepath'].$info[$lastnum]['savename'];
									$base64ret3 = file_get_contents($imgret3);
									$base64imgret3 =base64_encode($base64ret3);

									$hnhw3= "'fileBase64': '".$base64imgret3."','fileName': '".$ret3."','fileType': '户口薄','memberName': '".$_POST['xingming3']."','index': 5";

									$lastnum = $lastnum+1;
                                }else{
                                    $ret3=$info[$lastnum]['savename'];
									$imgret3=$info[$lastnum]['savepath'].$info[$lastnum]['savename'];
									$base64ret3 = file_get_contents($imgret3);
									$base64imgret3 =base64_encode($base64ret3);

									$hnhw3= "'fileBase64': '".$base64imgret3."','fileName': '".$ret3."','fileType': '居住证','memberName': '".$_POST['xingming3']."','index': 6";

									$lastnum = $lastnum+1;
                                }
                                if(($_POST['hnhw3'] == '1' && $ret1) || ($ret1 && $ret2)){
                                    if($_POST['hnhw4'] == '1'){
                                        $ret4=$info[$lastnum]['savename'];
										$imgret4=$info[$lastnum]['savepath'].$info[$lastnum]['savename'];
										$base64ret4 = file_get_contents($imgret4);
										$base64imgret4 =base64_encode($base64ret4);

										$hnhw4= "'fileBase64': '".$base64imgret4."','fileName': '".$ret4."','fileType': '户口薄','memberName': '".$_POST['xingming4']."','index': 7";

										$lastnum = $lastnum+1;
                                    }else{
                                        $ret4=$info[$lastnum]['savename'];
										$imgret4=$info[$lastnum]['savepath'].$info[$lastnum]['savename'];
										$base64ret4 = file_get_contents($imgret4);
										$base64imgret4 =base64_encode($base64ret4);

										$hnhw4= "'fileBase64': '".$base64imgret4."','fileName': '".$ret4."','fileType': '居住证','memberName': '".$_POST['xingming4']."','index': 8";

										$lastnum = $lastnum+1;
                                    }
                                    if(($_POST['hnhw4'] == '1' && $ret1) || ($ret1 && $ret2)){
                                        if($_POST['hnhw5'] == '1'){
                                            $ret5=$info[$lastnum]['savename'];
											$imgret5=$info[$lastnum]['savepath'].$info[$lastnum]['savename'];
											$base64ret5 = file_get_contents($imgret5);
											$base64imgret5 =base64_encode($base64ret5);

											$hnhw5= "'fileBase64': '".$base64imgret5."','fileName': '".$ret5."','fileType': '户口薄','memberName': '".$_POST['xingming5']."','index': 9";

											$lastnum = $lastnum+1;
                                        }else{
                                            $ret5=$info[$lastnum]['savename'];
											$imgret5=$info[$lastnum]['savepath'].$info[$lastnum]['savename'];
											$base64ret5 = file_get_contents($imgret5);
											$base64imgret5 =base64_encode($base64ret5);

											$hnhw5= "'fileBase64': '".$base64imgret5."','fileName': '".$ret5."','fileType': '居住证','memberName': '".$_POST['xingming5']."','index': 10";

											$lastnum = $lastnum+1;
                                        }
                                        if($renshu>=6){
                                            if(($_POST['hnhw5'] == '1' && $ret1) || ($ret1 && $ret2)){
                                                if($_POST['hnhw6'] == '1'){
                                                    $ret6=$info[$lastnum]['savename'];
													$imgret6=$info[$lastnum]['savepath'].$info[$lastnum]['savename'];
													$base64ret6 = file_get_contents($imgret6);
													$base64imgret6 =base64_encode($base64ret6);

													$hnhw6= "'fileBase64': '".$base64imgret6."','fileName': '".$ret6."','fileType': '户口薄','memberName': '".$_POST['xingming6']."','index': 11";

													$lastnum = $lastnum+1;
                                                }else{
                                                    $ret6=$info[$lastnum]['savename'];
													$imgret6=$info[$lastnum]['savepath'].$info[$lastnum]['savename'];
													$base64ret6 = file_get_contents($imgret6);
													$base64imgret6 =base64_encode($base64ret6);

													$hnhw6= "'fileBase64': '".$base64imgret6."','fileName': '".$ret6."','fileType': '居住证','memberName': '".$_POST['xingming6']."','index': 12";


													$lastnum = $lastnum+1;
                                                }
                                                if($renshu>=7){
                                                    if(($_POST['hnhw6'] == '1' && $ret1) || ($ret1 && $ret2)){
                                                        if($_POST['hnhw7'] == '1'){
                                                            $ret7=$info[$lastnum]['savename'];
															$imgret7=$info[$lastnum]['savepath'].$info[$lastnum]['savename'];
															$base64ret7 = file_get_contents($imgret7);
															$base64imgret7 =base64_encode($base64ret7);

															$hnhw7= "'fileBase64': '".$base64imgret7."','fileName': '".$ret7."','fileType': '户口薄','memberName': '".$_POST['xingming7']."','index': 13";

															$lastnum = $lastnum+1;
                                                        }else{
                                                            $ret7=$info[$lastnum]['savename'];
															$imgret7=$info[$lastnum]['savepath'].$info[$lastnum]['savename'];
															$base64ret7 = file_get_contents($imgret7);
															$base64imgret7 =base64_encode($base64ret7);

															$hnhw7= "'fileBase64': '".$base64imgret7."','fileName': '".$ret7."','fileType': '居住证','memberName': '".$_POST['xingming7']."','index': 14";

															$lastnum = $lastnum+1;
                                                        }
                                                        if($renshu>=8){
                                                            if(($_POST['hnhw7'] == '1' && $ret1) || ($ret1 && $ret2)){
                                                                if($_POST['hnhw8'] == '1'){
                                                                    $ret8=$info[$lastnum]['savename'];
																	$imgret8=$info[$lastnum]['savepath'].$info[$lastnum]['savename'];
																	$base64ret8 = file_get_contents($imgret8);
																	$base64imgret8 =base64_encode($base64ret8);

																	$hnhw8= "'fileBase64': '".$base64imgret8."','fileName': '".$ret8."','fileType': '户口薄','memberName': '".$_POST['xingming8']."','index': 15";

																	$lastnum = $lastnum+1;
                                                                }else{
                                                                    $ret8=$info[$lastnum]['savename'];
																	$imgret8=$info[$lastnum]['savepath'].$info[$lastnum]['savename'];
																	$base64ret8 = file_get_contents($imgret8);
																	$base64imgret8 =base64_encode($base64ret8);

																	$hnhw8= "'fileBase64': '".$base64imgret8."','fileName': '".$ret8."','fileType': '居住证','memberName': '".$_POST['xingming8']."','index': 16";

																	$lastnum = $lastnum+1;
                                                                }
                                                                if($renshu>=9){
                                                                    if(($_POST['hnhw8'] == '1' && $ret1) || ($ret1 && $ret2)){
                                                                        if($_POST['hnhw9'] == '1'){
                                                                            $ret9=$info[$lastnum]['savename'];
																			$imgret9=$info[$lastnum]['savepath'].$info[$lastnum]['savename'];
																			$base64ret9 = file_get_contents($imgret9);
																			$base64imgret9 =base64_encode($base64ret9);

																			$hnhw9= "'fileBase64': '".$base64imgret9."','fileName': '".$ret9."','fileType': '户口薄','memberName': '".$_POST['xingming9']."','index': 17";

																			$lastnum = $lastnum+1;
                                                                        }else{
                                                                            $ret9=$info[$lastnum]['savename'];
																			$imgret9=$info[$lastnum]['savepath'].$info[$lastnum]['savename'];
																			$base64ret9 = file_get_contents($imgret9);
																			$base64imgret9 =base64_encode($base64ret9);

																			$hnhw9= "'fileBase64': '".$base64imgret9."','fileName': '".$ret9."','fileType': '居住证','memberName': '".$_POST['xingming9']."','index': 18";


																			$lastnum = $lastnum+1;
                                                                        }
                                                                        if($renshu>=10){
                                                                            if(($_POST['hnhw9'] == '1' && $ret1) || ($ret1 && $ret2)){
                                                                                if($_POST['hnhw10'] == '1'){
                                                                                    $ret10=$info[$lastnum]['savename'];
																					$imgret10=$info[$lastnum]['savepath'].$info[$lastnum]['savename'];
																					$base64ret10 = file_get_contents($imgret10);
																					$base64imgret10 =base64_encode($base64ret10);

																					$hnhw10= "'fileBase64': '".$base64imgret10."','fileName': '".$ret10."','fileType': '户口薄','memberName': '".$_POST['xingming10']."','index': 19";

																					$lastnum = $lastnum+1;
                                                                                }else{
                                                                                    $ret10=$info[$lastnum]['savename'];
																					$imgret10=$info[$lastnum]['savepath'].$info[$lastnum]['savename'];
																					$base64ret10 = file_get_contents($imgret10);
																					$base64imgret10 =base64_encode($base64ret10);

																					$hnhw10= "'fileBase64': '".$base64imgret10."','fileName': '".$ret10."','fileType': '居住证','memberName': '".$_POST['xingming10']."','index':20";

																					$lastnum = $lastnum+1;
                                                                                }
                                                                                //现在开始拼接接口需要的格式
                                                                                $hukou ="[{".$enjoy1."},{".$enjoy2."},{".$enjoy3."},{".$enjoy4."},{".$hnhw1."},{".$hnhw2."},{".$hnhw3."},{".$hnhw4."},{".$hnhw5."},{".$hnhw6."},{".$hnhw7."},{".$hnhw8."},{".$hnhw9."},{".$hnhw10."}]";
                                                                            }else{
                                                                                $this->housecode=$_POST['housecode'];
                                                                                $this->date='';
                                                                                $this->msg='上传数据时失败!01';
                                                                                $this->display();
                                                                            }
                                                                        }else{
                                                                            //现在开始拼接接口需要的格式
                                                                            $hukou ="[{".$enjoy1."},{".$enjoy2."},{".$enjoy3."},{".$enjoy4."},{".$hnhw1."},{".$hnhw2."},{".$hnhw3."},{".$hnhw4."},{".$hnhw5."},{".$hnhw6."},{".$hnhw7."},{".$hnhw8."},{".$hnhw9."}]";
                                                                        }
                                                                    }else{
                                                                        $this->housecode=$_POST['housecode'];
                                                                        $this->date='';
                                                                        $this->msg='上传数据时失败!02';
                                                                        $this->display();
                                                                    }
                                                                }else{
                                                                    //现在开始拼接接口需要的格式
                                                                    $hukou ="[{".$enjoy1."},{".$enjoy2."},{".$enjoy3."},{".$enjoy4."},{".$hnhw1."},{".$hnhw2."},{".$hnhw3."},{".$hnhw4."},{".$hnhw5."},{".$hnhw6."},{".$hnhw7."},{".$hnhw8."}]";
                                                                }
                                                            }else{
                                                                $this->housecode=$_POST['housecode'];
                                                                $this->date='';
                                                                $this->msg='上传数据时失败!03';
                                                                $this->display();
                                                            }
                                                        }else{
                                                            //现在开始拼接接口需要的格式
                                                            $hukou ="[{".$enjoy1."},{".$enjoy2."},{".$enjoy3."},{".$enjoy4."},{".$hnhw1."},{".$hnhw2."},{".$hnhw3."},{".$hnhw4."},{".$hnhw5."},{".$hnhw6."},{".$hnhw7."}]";
                                                        }
                                                    }else{
                                                        $this->housecode=$_POST['housecode'];
                                                        $this->date='';
                                                        $this->msg='上传数据时失败!04';
                                                        $this->display();
                                                    }
                                                }else{
                                                    //现在开始拼接接口需要的格式
                                                    $hukou ="[{".$enjoy1."},{".$enjoy2."},{".$enjoy3."},{".$enjoy4."},{".$hnhw1."},{".$hnhw2."},{".$hnhw3."},{".$hnhw4."},{".$hnhw5."},{".$hnhw6."}]";
                                                }
                                            }else{
                                                $this->housecode=$_POST['housecode'];
                                                $this->date='';
                                                $this->msg='上传数据时失败!05';
                                                $this->display();
                                            }
                                        }else{
                                            //因为最少5个，所以现在开始拼接接口需要的格式
                                            $hukou ="[{".$enjoy1."},{".$enjoy2."},{".$enjoy3."},{".$enjoy4."},{".$hnhw1."},{".$hnhw2."},{".$hnhw3."},{".$hnhw4."},{".$hnhw5."}]";
                                        }
                                    }else{
                                        $this->housecode=$_POST['housecode'];
                                        $this->date='';
                                        $this->msg='上传数据时失败!06';
                                        $this->display();
                                    }
                                }else{
                                    $this->housecode=$_POST['housecode'];
                                    $this->date='';
                                    $this->msg='上传数据时失败!07';
                                    $this->display();
                                }
                            }else{
                                $this->housecode=$_POST['housecode'];
                                $this->date='';
                                $this->msg='上传数据时失败!08';
                                $this->display();
                            }
                        }else{
                            $this->housecode=$_POST['housecode'];
                            $this->date='';
                            $this->msg='上传数据时失败!09';
                            $this->display();
                        }
                    }else{
                        $this->housecode=$_POST['housecode'];
                        $this->date='';
                        $this->msg='上传数据时失败!10';
                        $this->display();
                    }
					//var_dump($info);exit;
                    $houseInfo = R('Api/SubmitLadderProcessServlet', array('阶梯基增',$housecode,$owername,$renshu,'网站',$hukou));
                    if($houseInfo->{'r_code'} == '0000'){
                        $this->housecode=$_POST['housecode'];
                        $this->date=date('Y').'年'.date('m').'月'.date('d').'日';
                        $this->display();
                    }elseif($houseInfo->{'r_code'} == '8008'){
                                $this->housecode=$_POST['housecode'];
                                $this->date='';
                                $this->msg='您有未审核完成的申请，待审核完成后可以再次提交。';
                                $this->display('bgsjhOk');
                            }else{
                        $this->housecode=$_POST['housecode'];
                        $this->date='';
                        $this->msg='上传图片时失败!11';
                        $this->display();
                    }
                
            

        }
    }

	/*********水务公司开始*********/
	public function sw() {
        $this->check();
		$this->id = intval($this->_param('id'));
        $this->nav = $this->nav($this->cate, $this->id, ''); // 当前位置
        $this->display();
    }
	// 请确认您已认真阅读并同意申请人应履行的责任
    /**
     * 再生水
     */
    public function swZsTip() {
        // check user login status
        $this->check();

        if (!isset($_SESSION['selected_houseCode'])) {
            $houselist = R('User/fjbd', array($_SESSION['uid']));//用户绑定房间
            if (empty($houselist)) {
                $this->redirect('User/bind');
            } else {
                $_SESSION['selected_houseCode'] = $houselist[0]['houseCode'];
            }
        }

        $this->display();
    }
    public function swZsUp() {

        // check user login status
        $this->check();

        if (!isset($_SESSION['selected_houseCode'])) {
            $houselist = R('User/fjbd', array($_SESSION['uid']));//用户绑定房间
            if (empty($houselist)) {
                $this->redirect('User/bind');
            } else {
                $_SESSION['selected_houseCode'] = $houselist[0]['houseCode'];
            }
        }

        $calllist = R('Api/callMyBusinessListSwInterface', array($_SESSION['selected_houseCode']));
        for($k = 0; $k < count($calllist->{'r_body'}); $k++){
            if($calllist->{'r_body'}[$k]->{"BUSINESSTYPE"} == '再生水停闭' && $calllist->{'r_body'}[$k]->{"EXAMINESTATUS"} == '审核中'){
                 $this->housecode=$_SESSION['selected_houseCode'];
                 $this->date='';
                 $this->msg='同类型审核正在处理中';
                 $this->display('swbgsjhOk');die();
            }
        }

        $info = D('user')->where('id=' . $_SESSION['uid'])->find();
        if(empty($info)){
            $this->redirect("Public/404");
        }

        $houseInfo = R('Api/getHouse', array($_SESSION['selected_houseCode']));

        $oldphone = $houseInfo['MOBILEPHONE'];
        $phone = R('Api/maskPhoneNumber', array($oldphone));

        $this->assign('phone', $phone);
        $this->assign('oldphone', $oldphone);
        $this->housecode=$_SESSION['selected_houseCode'];
        $this->owername=$_SESSION['selected_houseCode_name'];
        $this->chargeyear=$chargeMonth['YEAR'];

        $this->assign('houseInfo', $houseInfo);
        $this->assign('chargeMonth', date('Y-m-d'));
        $this->assign('userName', $info['nickname']);

        $this->display();

    }

    public function swZsUpOk() {
        $this->check();

        $housecode=$_POST['housecode'];
        $owername=$_POST['owername'];
        $chargeMonth=$_POST['chargeMonth'];
		
        if($_FILES['sfz']['name'][0] && $_FILES['sfz']['name'][1]){
            import('@.ORG.UploadFile');
            $upload = new UploadFile(); // 实例化上传类
            $upload->maxSize = 2 * 1024 * 1024; //设置上传图片的大小2mb
            $upload->allowExts = array('jpg'); //设置上传图片的后缀
            // $upload->uploadReplace = true;     //同名则替换
            $upload->saveRule = 'uniqid'; //设置上传头像命名规则(临时图片),修改了UploadFile上传类

            //完整的头像路径
            $path = './Uploads/shenhe/';
            $upload->savePath = $path;
			//var_dump($upload->upload());exit;
            if (!$upload->upload()) { // 上传错误提示错误信息
                 $this->housecode=$housecode;
                 $this->date='';
                 $this->msg='上传图片时失败';
                 $this->display('swbgsjhOk');
            } else { // 上传成功 获取上传文件信息
                $info = $upload->getUploadFileInfo();
				
                $imgnew1=$info[0]['savepath'].$info[0]['savename'];
				$img1name=$info[0]['savename'];
				$imgnew2=$info[1]['savepath'].$info[1]['savename'];
				$img2name=$info[1]['savename'];
				
				$base641 = file_get_contents($imgnew1);
                $base64img1 =base64_encode($base641);
				$base642 = file_get_contents($imgnew2);
                $base64img2 =base64_encode($base642);
				
                        if($_POST['bank'] == '是'){
							$bankinfo = $_POST['kaihuhang'].','.$_POST['zhanghuming'].','.$_POST['yinhangka'].','.$_POST['yinhanghang'];
                            $imgnew3=$info[2]['savepath'].$info[2]['savename'];
							$img3name=$info[2]['savename'];
                            $base643 = file_get_contents($imgnew3);
                            $base64img3 =base64_encode($base643);
							$imgarr = "[{'fileName':'".$img1name."','fileBase64':'".$base64img1."','fileType':'身份证'},{'fileName':'".$img2name."','fileBase64':'".$base64img2."','fileType':'身份证'},{'fileName':'".$img3name."','fileBase64':'".$base64img3."','fileType':'银行卡'}]";
                        }else{
						$imgarr = "[{'fileName': '".$img1name."','fileBase64':'". $base64img1."','fileType':'身份证'},{'fileName':'".$img2name."','fileBase64':'".$base64img2."','fileType':'身份证'}]";
						}
                       //var_dump($imgarr);exit;
                            //调用接口存储数据
							
							
                            $houseInfo = R('Api/SubmitZssCloseSwServlet', array('再生水停闭',$housecode,$owername,$chargeMonth,$_POST['bank'],$bankinfo,'网站',$imgarr));
                            if($houseInfo->{'r_code'} == '0000'){
                                $this->housecode=$_POST['housecode'];
                                $this->date=date('Y').'年'.date('m').'月'.date('d').'日';
                                $this->display('swbgsjhOk');
                            }elseif($houseInfo->{'r_code'} == '8008'){
                                $this->housecode=$_POST['housecode'];
                                $this->date='';
                                $this->msg='您有未审核完成的申请，待审核完成后可以再次提交。';
                                $this->display('swbgsjhOk');
                            }else{
                                $this->housecode=$housecode;
                                $this->date='';
                                $this->msg='上传图片时失败!';
                                $this->display('swbgsjhOk');
                            }
                        
            }
        }else{
            $this->housecode=$_POST['housecode'];
             $this->date='';
             $this->msg='缺少上传图片';
             $this->display('swbgsjhOk');
        }
    }
//再生水预约开通

public function swyyktcg()
{



  //$this->check();
   $housecode=$_POST['housecode'];
   $owername=$_POST['owername'];
	$xingqi=$_POST['xq'];
	$shijian=$_POST['sj'];

	 if($_FILES['sfz']){
            import('@.ORG.UploadFile');
            $upload = new UploadFile(); // 实例化上传类
            $upload->maxSize = 2 * 1024 * 1024; //设置上传图片的大小2mb
            $upload->allowExts = array('jpg'); //设置上传图片的后缀
            // $upload->uploadReplace = true;     //同名则替换
            $upload->saveRule = 'uniqid'; //设置上传头像命名规则(临时图片),修改了UploadFile上传类
            //完整的头像路径
            $path = './Uploads/shenhe/';
            $upload->savePath = $path;
            if (!$upload->upload()) { // 上传错误提示错误信息
                 $this->housecode=$_POST['housecode'];
                 $this->date='';
                 $this->msg='上传图片时失败';
                 //$this->msg=$upload->getErrorMsg();
                 $this->display();
            }else { // 上传成功 获取上传文件信息
                $info = $upload->getUploadFileInfo();
				$imgnew=$info[0]['savepath'].$info[0]['savename'];
				$base64 = file_get_contents($imgnew);
                $base64img =base64_encode($base64);
                         $houseInfo = R('Api/SubmitZssOpenSwServlet', array('再生水开通',$housecode,$owername,$xingqi,$shijian,'网站',$base64img,$info[0]['savename']));
                            if($houseInfo->{'r_code'} == '0000'){
                                $this->housecode=$_POST['housecode'];
                                $this->date=date('Y').'年'.date('m').'月'.date('d').'日';
                                $this->display();
                            }elseif($houseInfo->{'r_code'} == '8008'){
                                $this->housecode=$_POST['housecode'];
                                $this->date='';
                                $this->msg='您有未审核完成的申请，待审核完成后可以再次提交。';
                                $this->display();
                            }else{
                                $this->housecode=$_POST['housecode'];
                                $this->date='';
                                $this->msg='上传图片时失败!';
                                $this->display();
                            }
					}
	 }else{
            $this->housecode=$_POST['housecode'];
             $this->date='';
             $this->msg='缺少上传图片';
             $this->display();
        }
}

/*   //将同样的图片传给ftp服务器
                $ftparray = C('FTPSWARRAY');
                $conn = ftp_connect($ftparray['host'],$ftparray['port']);
                if($conn){
                    $login = ftp_login($conn,$ftparray['username'],$ftparray['pwd']);
                    if($login){
                        $result0 = ftp_put($conn, $info[0]['savename'], $info[0]['savepath'].$info[0]['savename'], FTP_BINARY);

                        ftp_close($conn);
                          if($result0){


                            //如果都成功，调用接口存储数据

                         $houseInfo = R('Api/SubmitZssOpenSwServlet', array('再生水开通',$housecode,$owername,$xingqi,$shijian,'网站',$info[0]['savename']));


                            if($houseInfo->{'r_code'} == '0000'){
                                $this->housecode=$_POST['housecode'];
                                $this->date=date('Y').'年'.date('m').'月'.date('d').'日';
                                $this->display();
                            }else{
                                $this->housecode=$_POST['housecode'];
                                $this->date='';
                                $this->msg='上传图片时失败!';
                                $this->display();
                            }
                        }else{
                            $this->housecode=$_POST['housecode'];

							$this->houseInfo;

                            $this->date='';
                            $this->msg='查询数据时失败!';
                            $this->display();
                        }
                    }else{
                        $this->housecode=$_POST['housecode'];
                        $this->date='';
                        $this->msg='查询数据时失败!';
                        $this->display();
                    }
                }else{
                    $this->housecode=$_POST['housecode'];
                    $this->date='';
                    $this->msg='查询数据时失败!';
                    $this->display();
                }

            }
        }else{
            $this->housecode=$_POST['housecode'];
             $this->date='';
             $this->msg='缺少上传图片';
             $this->display();
        }


} */



    public function swyykt() {
        // check user login status
        $this->check();

        if (!isset($_SESSION['selected_houseCode'])) {
            $houselist = R('User/fjbd', array($_SESSION['uid']));//用户绑定房间
            if (empty($houselist)) {
                $this->redirect('User/bind');
            } else {
                $_SESSION['selected_houseCode'] = $houselist[0]['houseCode'];
            }
        }

        $this->display();
    }

    public function swyyktinfo() {
        // check user login status
        $this->check();

        if (IS_GET) {
            $this->error('非法的请求!');
        }

        if (!isset($_POST['accept']) || $_POST['accept'] != 1) {
            $this->error('请确认您已认真阅读并同意绑定/变更能源卡提示!');
        }

        if (!isset($_SESSION['selected_houseCode'])) {
            $houselist = R('User/fjbd', array($_SESSION['uid']));//用户绑定房间
            if (empty($houselist)) {
                $this->redirect('User/bind');
            } else {
                $_SESSION['selected_houseCode'] = $houselist[0]['houseCode'];
            }
        }

        $info = D('user')->where('id=' . $_SESSION['uid'])->find();
        if(empty($info)){
            $this->redirect("Public/404");
        }
		

        $calllist = R('Api/callMyBusinessListSwInterface', array($_SESSION['selected_houseCode']));
        for($k = 0; $k < count($calllist->{'r_body'}); $k++){
            if($calllist->{'r_body'}[$k]->{"BUSINESSTYPE"} == '变更能源卡' && $calllist->{'r_body'}[$k]->{"EXAMINESTATUS"} == '审核中'){
                 $this->housecode=$_SESSION['selected_houseCode'];
                 $this->date='';
                 $this->msg='同类型审核正在处理中';
                 $this->display('swbgcg');die();
            }
        }
		$url0 = 'http://10.105.15.2/TjstcWebImpl/GetWeekDayServlet';
		$post_data0 ="";
		$ch0 = curl_init();
		curl_setopt($ch0, CURLOPT_URL, $url0);
		curl_setopt($ch0, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch0, CURLOPT_POST, 1);
		curl_setopt($ch0, CURLOPT_POSTFIELDS, $post_data0);
		$output0 = curl_exec($ch0);
		$infoarr=json_decode($output0,true);
		//var_dump($infoarr);exit;
		
			$beginDateNextSat=$infoarr['r_body'][0]['beginDateNextSat'];
			$beginDateNextMon=$infoarr['r_body'][0]['beginDateNextMon'];
			$endDateNextSat=$infoarr['r_body'][0]['endDateNextSat'];
			$beginDateNextWed=$infoarr['r_body'][0]['beginDateNextWed'];
			$endDateNextWed=$infoarr['r_body'][0]['endDateNextWed'];
			$endDateNextMon=$infoarr['r_body'][0]['endDateNextMon'];
		$this->assign('beginDateNextSat', $beginDateNextSat);
		$this->assign('beginDateNextMon', $beginDateNextMon);
		$this->assign('endDateNextSat', $endDateNextSat);
		$this->assign('beginDateNextWed', $beginDateNextWed);
		$this->assign('endDateNextWed', $endDateNextWed);
		$this->assign('endDateNextMon', $endDateNextMon);

		

        //小区编号是：5002或5004的是建行，其他的都是中行
        $newcode = substr($_SESSION['selected_houseCode'],0,4);

        $houseInfo = R('Api/getHouse', array($_SESSION['selected_houseCode']));

        $phone = $houseInfo['MOBILEPHONE'] ;
		$banknumber=$houseInfo['BANKNUMBER'] ;
        $this->oldphone= $phone;
        $phone = R('Api/maskPhoneNumber', array($phone));
        $this->housecode=$_SESSION['selected_houseCode'];
        $this->owername=$_SESSION['selected_houseCode_name'];

        $this->assign('phone', $phone);
        $this->assign('newcode', $newcode);
        $this->assign('houseInfo', $houseInfo);
		$this->assign('banknumber', $banknumber);
        $this->assign('userName', $info['nickname']);

        $this->display();

    }

    public function swbg() {
        // check user login status
        $this->check();

        if (!isset($_SESSION['selected_houseCode'])) {
            $houselist = R('User/fjbd', array($_SESSION['uid']));//用户绑定房间
            if (empty($houselist)) {
                $this->redirect('User/bind');
            } else {
                $_SESSION['selected_houseCode'] = $houselist[0]['houseCode'];
            }
        }

        if($_SESSION['jump2'] != 1){
            $this->housecode=$_SESSION['selected_houseCode'];
            $this->date='';
            $this->msg='您当前未开通再生水业务';
            $this->display('swbgcg');die();
        }

        $this->display();
    }
    public function swbginfo() {
        // check user login status
        $this->check();

        if (IS_GET) {
            $this->error('非法的请求!');
        }

        if (!isset($_POST['accept']) || $_POST['accept'] != 1) {
            $this->error('请确认您已认真阅读并同意绑定/变更能源卡提示!');
        }

        if (!isset($_SESSION['selected_houseCode'])) {
            $houselist = R('User/fjbd', array($_SESSION['uid']));//用户绑定房间
            if (empty($houselist)) {
                $this->redirect('User/bind');
            } else {
                $_SESSION['selected_houseCode'] = $houselist[0]['houseCode'];
            }
        }

        $info = D('user')->where('id=' . $_SESSION['uid'])->find();
        if(empty($info)){
            $this->redirect("Public/404");
        }

        $calllist = R('Api/callMyBusinessListSwInterface', array($_SESSION['selected_houseCode']));
        for($k = 0; $k < count($calllist->{'r_body'}); $k++){
            if($calllist->{'r_body'}[$k]->{"BUSINESSTYPE"} == '变更能源卡' && $calllist->{'r_body'}[$k]->{"EXAMINESTATUS"} == '审核中'){
                 $this->housecode=$_SESSION['selected_houseCode'];
                 $this->date='';
                 $this->msg='同类型审核正在处理中';
                 $this->display('swbgcg');die();
            }
        }

        //小区编号是：5002或5004的是建行，其他的都是中行
        $newcode = substr($_SESSION['selected_houseCode'],0,4);

        $houseInfo = R('Api/getHouse', array($_SESSION['selected_houseCode']));

        $phone = $houseInfo['MOBILEPHONE'] ;
        $this->oldphone= $phone;
        $phone = R('Api/maskPhoneNumber', array($phone));
        $this->housecode=$_SESSION['selected_houseCode'];
        $this->owername=$_SESSION['selected_houseCode_name'];

        $this->assign('phone', $phone);
        $this->assign('newcode', $newcode);
        $this->assign('houseInfo', $houseInfo);
        $this->assign('userName', $info['nickname']);

        $this->display();

    }

public function swbgcg() {
        $this->check();

        $housecode=$_POST['housecode'];
        $owername=$_POST['owername'];
        $bank=$_POST['bank'];
        $bankNo=$_POST['yinhang1'].$_POST['yinhang2'].$_POST['yinhang3'].$_POST['yinhang4'];
        $youxiaoqi=$_POST['yue'].'/'.$_POST['nian'];


        if($_FILES['sfz']){
            import('@.ORG.UploadFile');
            $upload = new UploadFile(); // 实例化上传类
            $upload->maxSize = 2 * 1024 * 1024; //设置上传图片的大小2mb
            $upload->allowExts = array('jpg'); //设置上传图片的后缀
            // $upload->uploadReplace = true;     //同名则替换
            $upload->saveRule = 'uniqid'; //设置上传头像命名规则(临时图片),修改了UploadFile上传类


            //完整的头像路径
            $path = './Uploads/shenhe/';
            $upload->savePath = $path;
            if (!$upload->upload()) { // 上传错误提示错误信息
                 $this->housecode=$_POST['housecode'];
                 $this->date='';
                 $this->msg='上传图片时失败';
                 //$this->msg=$upload->getErrorMsg();
                 $this->display();
            } else { // 上传成功 获取上传文件信息
                $info = $upload->getUploadFileInfo();
                $imgnew=$info[0]['savepath'].$info[0]['savename'];
				$base64 = file_get_contents($imgnew);
                $base64img =base64_encode($base64);
                            $houseInfo = R('Api/SubmitBankProcessSwServlet', array('变更能源卡',$housecode,$owername,$bank,$bankNo,$youxiaoqi,'网站',$base64img,$info[0]['savename']));
                            if($houseInfo->{'r_code'} == '0000'){
                                $this->housecode=$_POST['housecode'];
                                $this->date=date('Y').'年'.date('m').'月'.date('d').'日';
                                $this->display();
                            }elseif($houseInfo->{'r_code'} == '8008'){
                                $this->housecode=$_POST['housecode'];
                                $this->date='';
                                $this->msg='您有未审核完成的申请，待审核完成后可以再次提交。';
                                $this->display('bgsjhOk');
                            }else{
                                //print_r($houseInfo.'123');
                                $this->housecode=$_POST['housecode'];
                                $this->date='';
                                $this->msg='上传图片时失败!';
                                $this->display();
                            }
                        
                    
                

            }
        }else{
            $this->housecode=$_POST['housecode'];
             $this->date='';
             $this->msg='缺少上传图片';
             $this->display();
        }
    }

    /**
     * 水务公司->手机号变更提示Infos
     */
    public function swbgsjhInfo() {
        // check user login status
        $this->check();

        if (!isset($_SESSION['selected_houseCode'])) {
            $houselist = R('User/fjbd', array($_SESSION['uid']));//用户绑定房间
            if (empty($houselist)) {
                $this->redirect('User/bind');
            } else {
                $_SESSION['selected_houseCode'] = $houselist[0]['houseCode'];
            }
        }

        if($_SESSION['jump2'] != 1){
            $this->housecode=$_SESSION['selected_houseCode'];
            $this->date='';
            $this->msg='您当前未开通再生水业务';
            $this->display('swbgcg');die();
        }

        $this->display();
    }

    /**
     * 水务公司->手机号变更
     */
    public function swbgsjh() {
        // check user login status
        $this->check();
        $this->housecode=$_SESSION['selected_houseCode'];
        $this->owername=$_SESSION['selected_houseCode_name'];
        $this->display();
    }

    /**
     * 水务公司->手机号变更OK
     */
    public function swbgsjhOk() {
        // check user login status
        $this->check();

        $houselist = R('Api/chargeSwPhone', array($_SESSION['selected_houseCode'],$_POST['phone'],$_POST['selected_houseCode_name'],'网站'));//用户绑定房间
        $this->housecode=$_SESSION['selected_houseCode'];
        if($houselist->{'r_code'} == '0000'){
            $this->phone=$_POST['phone'];
        }else{
            $this->phone='';
        }
        $this->type=1;
        $this->display();
    }

	/*********水务公司结束*********/

	/*********环保公司开始*********/
	public function hb() {
        $this->check();
		$this->id = intval($this->_param('id'));
        $this->nav = $this->nav($this->cate, $this->id, ''); // 当前位置
        $this->display();
    }
	// 请确认您已认真阅读并同意申请人应履行的责任
    /**
     * 大件垃圾回收
     */
    public function djljhsTip() {
        // check user login status
        $this->check();

        if (!isset($_SESSION['selected_houseCode'])) {
            $houselist = R('User/fjbd', array($_SESSION['uid']));//用户绑定房间
            if (empty($houselist)) {
                $this->redirect('User/bind');
            } else {
                $_SESSION['selected_houseCode'] = $houselist[0]['houseCode'];
            }
        }

        $this->display();
    }
    public function hbUp() {
        // check user login status
        $this->check();

        if (!isset($_SESSION['selected_houseCode'])) {
            $houselist = R('User/fjbd', array($_SESSION['uid']));//用户绑定房间
            if (empty($houselist)) {
                $this->redirect('User/bind');
            } else {
                $_SESSION['selected_houseCode'] = $houselist[0]['houseCode'];
            }
        }
        //获取token
        $hbToken = R('Api/getJfToken', array('stcapiusr','134679','192.168.2.127'));
        if($hbToken['Code'] != 0){
            R('User/djljhsTip', array($_SESSION['uid']));
        }

        $jfToken = $hbToken['Data'];

        $url='http://103.233.5.21:8076/AppService.svc/stcapi/getclass1/'.$jfToken;
        $yijifenlei = file_get_contents($url);
        $yijifenlei = json_decode($yijifenlei,true);
        if($yijifenlei['Code'] == 0){
            foreach ($yijifenlei['Data'] as $k => $v) {
                $tempfl['CateId'] = $v['CateId'];
                $tempfl['CateName'] = $v['CateName'];
                $ayjfl[] = $tempfl;
            }
        }else{
            $ayjfl = '';
        }
        $this->assign('yjfl',$ayjfl);

		$url0 = 'http://10.105.15.2/TjstcWebImpl/GetVerifysubscribeServlet';
		$post_data0 ="";
		$ch0 = curl_init();
		curl_setopt($ch0, CURLOPT_URL, $url0);
		curl_setopt($ch0, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch0, CURLOPT_POST, 1);
		curl_setopt($ch0, CURLOPT_POSTFIELDS, $post_data0);
		$output0 = curl_exec($ch0);
		$infoarr=json_decode($output0,true);
		//var_dump($infoarr);exit;
		for($k = 0; $k < count($infoarr['r_body']); $k++){
			$info[$k]['Left']=$infoarr['r_body'][$k]['Left'];
			$info[$k]['Time']=$infoarr['r_body'][$k]['Time'];
		}

		$this->timeinfo = $info;
        //查询电话
        $hbDH = R('Api/getJf', array($_SESSION['cardnum'],$jfToken,'192.168.2.127'));
		//查询业主姓名
		$housecode=$_SESSION['selected_houseCode'];
		$url = "http://10.105.15.2/TjstcWebImpl/GetHouseInfoServlet";
        $post_data ="vHOUSECODE=$housecode";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $output = curl_exec($ch);
        $date=json_decode($output,true);
        $OWNERNAME=$date["r_body"][0]["OWNERNAME"];
        curl_close($ch);

        $this->assign('token',$jfToken);
        $this->assign('cardnum',$_SESSION['cardnum']);
		$this->assign('housecode',$_SESSION['selected_houseCode']);
		$this->assign('ownername',$OWNERNAME);
        $this->assign('phone',$hbDH['Data']['CustPhone']);
        //print_r($yijifenlei);
        $this->display();
    }

    public function ajax_getErji() {

        $url='http://103.233.5.21:8076/AppService.svc/stcapi/getclass2/'.$_POST['token'].'/'.$_POST['yiji'];
        $erjifenlei = file_get_contents($url);
        $erjifenlei = json_decode($erjifenlei,true);
        if($erjifenlei['Code'] == 0){
            foreach ($erjifenlei['Data'] as $k => $v) {
                $tempfl['CateId'] = $v['CategoryId'];
                $tempfl['CateName'] = $v['CateName'];
                $ayjfl[] = $tempfl;
            }
        }else{
            $ayjfl = '';
        }
        echo $this->ajaxReturn($ayjfl,'JSON');
        exit();
    }

    public function hbcg() {
        $this->check();
        //如果没绑定过房间跳转
		//var_dump($_POST);exit;
        $houseCode = R('User/fjbh', array($_SESSION['uid']));
        if (empty($houseCode)) {
            $this->redirect('User/bind');
        }
        $info = D('user')->where('id=' . $_SESSION['uid'])->find();
        if(empty($info)){
            $this->redirect("Public/404");
        }
        $beizhu = '';
        if($_POST['bzcheck0'] == 1){
            $beizhu .= ',物品较重，请多名回收人员携带工具上门';
        }
        if($_POST['bzcheck1'] == 1){
            $beizhu .= ',大型家具已拆解';
        }
        if($_POST['bzcheck2'] == 1){
            $beizhu .= ',散装物品已分类打包/打回';
        }
        if(!empty($_POST['beizhu'])){
            $beizhu .= ','.$_POST['beizhu'];
        }
        if(!empty($beizhu)){
            $beizhu = substr($beizhu, 1);
        }
		$imgarr='';
		//图片上传验证开始
		if($_FILES['sfz']['name'][0] || $_FILES['sfz']['name'][1]){
            import('@.ORG.UploadFile');
            $upload = new UploadFile(); // 实例化上传类
            $upload->maxSize = 2 * 1024 * 1024; //设置上传图片的大小2mb
            $upload->allowExts = array('jpg'); //设置上传图片的后缀
            // $upload->uploadReplace = true;     //同名则替换
            $upload->saveRule = 'uniqid'; //设置上传头像命名规则(临时图片),修改了UploadFile上传类
            //完整的头像路径
            $path = './Uploads/shenhe/';
            $upload->savePath = $path;
            if (!$upload->upload()) { // 上传错误提示错误信息
                 echo "<script>alert('图片上传失败，请稍后重试');</script>";
				echo "<script language='javascript'>";
				echo 'window.top.location="http://www.66885890.com/index.php?s=/user/hb"';
				echo "</script>";	
            } else { // 上传成功 获取上传文件信息
                $info = $upload->getUploadFileInfo();
				if($_FILES['sfz']['name'][0]){
					$imgnew1=$info[0]['savepath'].$info[0]['savename'];
					$img1name=$info[0]['savename'];
					$base641 = file_get_contents($imgnew1);
					$base64img1 =base64_encode($base641);
				}
				if($_FILES['sfz']['name'][1]){
					$imgnew2=$info[1]['savepath'].$info[1]['savename'];
					$img2name=$info[1]['savename'];
					$base642 = file_get_contents($imgnew2);
					$base64img2 =base64_encode($base642);
				}
				

				$imgarr = "[{'fileBase64':'". $base64img1."'},{'fileBase64':'".$base64img2."'}]";
            }
		}

        $hbYY = R('Api/revNow', array($_POST['phone'],$_POST['cardnum'],$_POST['yijifenlei'],$_POST['erjifenlei'],$_POST['time'],$beizhu,$_POST['housecode'],$_POST['ownername'],'网站',$imgarr));
		//var_dump($hbYY);exit;
        if($hbYY['r_code'] == '8003'){
            $ret = 0;
        }elseif($hbYY['r_code'] == '8007'){
            $ret = 1;
        }
		else{
            $ret = 2;
        }
        $this->assign('ret',$ret);
        $this->display();
    }
	/*********环保公司结束*********/

    public function index1() {
        $this->check();
        //如果没绑定过房间跳转
        $houseCode = R('User/fjbh', array($_SESSION['uid']));
        if (empty($houseCode)) {
            $this->redirect('User/bind');
        }
        $info = D('user')->where('id=' . $_SESSION['uid'])->find();
        if(empty($info)){
            $this->redirect("Public/404");
        }
        /* 查找已绑定的房间(身份为房主或者租客均可) */
        $this->houselist = R('User/fjbd', array($_SESSION['uid']));
        $this->now = date('Y', time());
        $this->assign($info);
        $this->assign('title', '我的5890');
        $this->display();
    }



    public function edit() {
        $this->check();
        $info = D('User')->where('id=' . $_SESSION['uid'])->find();
        if(empty($info)){
            $this->redirect("Public/404");
        }
        $this->assign($info);
        $this->assign('title', '个人设置');

		$this->id = $this->_get('id');
        $this->nav = $this->nav($this->cate, $this->id, ''); //位置导航
        $this->display();
    }

    //用户绑定房间列表
    public function room() {
        $this->check();
        //如果没绑定过房间跳转
        $houseCode = R('User/fjbh', array($_SESSION['uid']));
        if (empty($houseCode)) {
            $this->redirect('User/bind');
        }
        $info = D('user')->where('id=' . $_SESSION['uid'])->find();
        if(empty($info)){
            $this->redirect("Public/404");
        }
        /* 查找已绑定的房间 */
        $this->houselist = R('User/alreadyBind', array($_SESSION['uid']));
        $this->now = date('Y', time());
        $this->assign($info);
        $this->assign('title', '我的5890');
        $this->display();
    }

    //房间个人信息显示
    public function uproom() {
        $this->check();
        $housecode = $this->_get('id');
        //判断用户是否第一次登录
        $info = D('Owner')->where('housecode='."'$housecode'")->find();

        if(empty($info)){
            //获取房间信息
            $list = R('Api/getOracleHouse',array($housecode));
            //dump($list);
            //将信息存入mysql
            $data['housecode'] = $list["HOUSECODE"];
            //$data['oldlinkman'] = $list[6];
            //$data['linkman'] = $list[6];
            $data['oldlinkman'] = $list["OWNERNAME"];
            $data['linkman'] = $list["OWNERNAME"];
            $data['oldlinktel'] = $list["MOBILEPHONE"];
            $data['linktel'] = $list["MOBILEPHONE"];
            $data['oldworkunit'] = $list["WORKUNIT"];
            $data['workunit'] = $list["WORKUNIT"];
            $data['oldmailingaddress'] = $list["ENTERDATE"];
            $data['mailingaddress'] = $list["ADDRESS"];
            $data['status'] = 0;

            $Owner = D('Owner');
            $r = $Owner->add($data);
        }
        //获取房间个人信息
        $info = D('Owner')->where('housecode='."'$housecode'")->find();
        if($info['status'] != 1){
            $info['linkman'] = $info['oldlinkman'];
            $info['linktel'] = $info['oldlinktel'];
            $info['workunit'] = $info['oldworkunit'];
            $info['mailingaddress'] = $info['oldmailingaddress'];
        }
        $this->assign($info);
        $this->display();
    }

    //房间个人信息修改
    public function editinfo() {
        $this->check();

        $urluindex = U('User/room');
        $data = D('Owner')->create();

        $num = 0;
        $data['upstatus'] = 0;

        $housecode = $_POST['housecode'];
        //$linkman = trim($_POST['linkman']);
        //$oldlinkman = trim($_POST['oldlinkman']);
        $linktel = trim($_POST['linktel']);
        $oldlinktel = trim($_POST['oldlinktel']);
        $workunit = trim($_POST['workunit']);
        $oldworkunit = trim($_POST['oldworkunit']);
        $mailingaddress = trim($_POST['mailingaddress']);
        $oldmailingaddress = trim($_POST['oldmailingaddress']);

//      if($oldlinkman != $linkman){
//          $data['linkman'] = $linkman;
//          $num++;
//      }
        if($oldlinktel != $linktel){
            $data['linktel'] = $linktel;
            $num++;
        }
        if($oldworkunit != $workunit){
            $data['workunit'] = $workunit;
            $num++;
        }
        if($oldmailingaddress != $mailingaddress){
            $data['mailingaddress'] = $mailingaddress;
            $num++;
        }

        if($num != 0){
            $data['upstatus'] = 1;
            $re = D('Owner')->where('housecode='."'$housecode'")->setField($data);
            if ($re !== false) {
                echo_json('1', '操作', '恭喜您修改成功,修改的内容待通过管理员审核后，即可查看到！', $urluindex, '10');
            } else {
                echo_json('0', '操作', '修改失败，请重新操作！', '', '10');
            }
        }
        else{
            echo_json('1', '操作', '操作成功！', $urluindex, '10');
        }
    }

    //房间个人信息预览
    function view() {
        $id = intval($this->_param('id'));
        $status = D('Owner')->where('id=' . $id)->find();
        if(empty($status)){
            $this->redirect("Public/404");
        }
        $this->assign($status);
        $this->display();
    }

    //Start update by wangshipeng 2014.10.23
    //用户绑定房间列表
    public function apply() {
        $this->check();
        //如果没绑定过房间跳转
        $houseCode = R('User/fjbh', array($_SESSION['uid']));
        if (empty($houseCode)) {
            $this->redirect('User/bind');
        }
        $info = D('user')->where('id=' . $_SESSION['uid'])->find();
        if(empty($info)){
            $this->redirect("Public/404");
        }
        /* 查找已绑定的房间(身份必须为房主) */
        $this->houselist = R('User/alreadyBind', array($_SESSION['uid']));
        $this->now = date('Y', time());
        $this->assign($info);
        $this->assign('title', '我的5890');
        $this->display();
    }

    //用户申请绑定记录列表
    public function applist() {
        $this->check();
        /* 房间编号 */
        $housecode = $this->_get('id');
        /* 查找房间地址 */
        $this->infor = R('User/fjdz', array($_SESSION['uid'],$housecode));
        $map['houseCode'] = $housecode;
        $map['ifBind'] = 4;
        /* 查找申请绑定记录 */
        $applist = D('User_room')->where($map)->select();
        $this->now = date('Y', time());
        $this->assign('uid', $_SESSION['uid']);
        $this->assign('applist', $applist);
        $this->assign('title', '我的5890');
        $this->display();
    }

    //申请绑定记录处理
    public function upstatus() {
        $this->check();
        $data['uid'] = $_POST['id'];
        $data['houseCode'] = $_POST['houseCode'];
        //$data['ifBind'] = 0;
        $rec['ifBind'] = $_POST['ifBind'];
        $re = D('User_room')->where($data)->setField($rec);
        //将处理结果发送消息给用户
        $toid = $_POST['id'];//接收消息用户id
        //发送请求消息
        $map['title'] = "您有一条新的绑定房间消息,请查收！";
        $infor = R('User/fjdz', array($_SESSION['uid'],$_POST['houseCode']));
        if($_POST['ifBind'] == 2){
            $content = "恭喜您，您对地址为：".$infor['houseName']."的房间发出绑定的请求得到通过！";
        }
        else if($_POST['ifBind'] == 3){
            $content = "很遗憾的通知您，您对地址为：".$infor['houseName']."的房间发出绑定的请求没有得到通过！";
        }
        else{
            $content = "";
        }
        $map['content'] = $content;
        $map['create_time'] = time();
        $map['issys'] = 0;
        $map['housename'] = $_SESSION['uid'].",".$data['houseCode'].",".$infor['houseName'];
        $id = D('Message')->add($map);
        //用户消息关联
        $record['fromid'] = $_SESSION['uid'];
        $record['toid'] = $toid;
        $record['mid'] = $id;
        $record['status'] = 0;
        D('User_message')->add($record);

        if ($re !== false) {
            echo_json('1', '操作成功', '操作成功！', '', '10');
        } else {
            echo_json('0', '操作失败', '操作失败！', '', '10');
        }
    }

    //租客列表
    public function tenlist(){
        $this->check();
        /* 房间编号 */
        $housecode = $this->_get('id');
        $map['houseCode'] = $housecode;
        $map['ifBind'] = 2;
        /* 查找房间地址 */
        $this->infor = R('User/fjdz', array($_SESSION['uid'],$housecode));
        /* 查询租客列表 */
        $this->userlist = D('User_room')->where($map)->select();
        $this->now = date('Y', time());
        $this->assign('uid', $_SESSION['uid']);
        $this->assign('title', '我的5890');
        $this->display();
    }

    //户主解绑租客
    public function unbound() {
        $this->check();
        $data['uid'] = $_POST['id'];
        $data['houseCode'] = $_POST['houseCode'];
        $rec['ifBind'] = 0;
        $re = D('User_room')->where($data)->setField($rec);
        //将解绑发送消息给租客
        $toid = $_POST['id'];//接收消息租客id
        //发送请求消息
        $map['title'] = "您有一条新的绑定房间消息,请查收！";
        $infor = R('User/fjdz', array($_SESSION['uid'],$_POST['houseCode']));
        $content = "很遗憾的通知您，您地址为：".$infor['houseName']."的房间已经被房主解除绑定！";
        $map['content'] = $content;
        $map['create_time'] = time();
        $map['issys'] = 0;
        $map['housename'] = $_SESSION['uid'].",".$data['houseCode'].",".$infor['houseName'];
        $id = D('Message')->add($map);
        //用户消息关联
        $record['fromid'] = $_SESSION['uid'];
        $record['toid'] = $toid;
        $record['mid'] = $id;
        $record['status'] = 0;
        D('User_message')->add($record);

        if ($re !== false) {
            echo_json('1', '操作成功', '操作成功！', '', '10');
        } else {
            echo_json('0', '操作失败', '操作失败！', '', '10');
        }
    }
    //End update by wangshipeng 2014.10.23

    public function verify() {
        $type = isset($_GET['type']) ? $_GET['type'] : 'gif';
        import("ORG.Util.Image");
        Image::buildImageVerify(4, 1, $type);
    }

    public function pass() {
        $this->check();
        $info = D('User')->where('id=' . $_SESSION['uid'])->find();
        if(empty($info)){
            $this->redirect("Public/404");
        }
        $this->assign($info);
        $this->assign('title', '密码');
		$this->id = $this->_get('id');
        $this->nav = $this->nav($this->cate, $this->id, ''); //位置导航
        $this->display();
    }

    //检查验证码
    public function check_pass() {

        //$request = trim($_POST['pass']);
        $encrypt = "46faa8ab560de8e86ee20ce678eeb8";//加密码
        $request = md5(md5($encrypt).md5(trim($_POST['pass'])));

        $pass = D('User')->where('id=' . $_SESSION['uid'])->getField('password');
        if ($pass != $request) {
            echo "false";
        } else {
            echo "true";
        }
        exit();
    }

    public function editok() {
        $this->check();

        $data = D('User')->create();
        $data['username'] = trim($_POST['name']);
        $loginname = $_SESSION ['uname'];
        if($loginname != trim($_POST['name'])){
            $urluindex = U('Public/logout');
        }
        else{
            $urluindex = U('User/index');
        }

        $re = D('User')->save($data);
        if ($re !== false) {
            echo_json('1', '操作成功', '修改成功！', $urluindex, '10');
        } else {
            echo_json('0', '操作失败', '修改失败！', '', '10');
        }
    }

    public function editpass() {
        $this->check();

        $encrypt = "46faa8ab560de8e86ee20ce678eeb8";//加密码

        $data = D('User')->create();
        $oldpass = D('User')->where('id=' . $_SESSION['uid'])->getField('password');
        $urluindex = U('User/index');
        if (!empty($_POST['pass'])) {
            $password = md5(md5($encrypt).md5(trim($_POST['pass'])));

            if ($oldpass !== $password ) {
                echo_json('0', '验证失败', '原密码错误！', '', '5');
            } else {
                if ($_POST['newpass'] !== $_POST['newpassok']) {
                    echo_json('0', '操作失败', '两次密码输入不一致！', '', '10');
                } else if (strlen($_POST['newpass']) < 6) {
                    echo_json('0', '操作失败', '新密码不能小于6位！', '', '10');
                } else {
                    $newpass = md5(md5($encrypt).md5(trim($_POST['newpass'])));
                    $data['password'] = $newpass ;
                    $url = U('Public/logout');
                }
            }
        }
        $re = D('User')->save($data);
        $s = R('Api/passUpdate', array($_SESSION['uid'],$newpass));
        if ($re !== false) {
            echo_json('1', '操作成功', '修改成功！', $urluindex, '10');
        } else {
            echo_json('0', '操作失败', '修改失败！', '', '10');
        }
    }

//
    public function message() {
        $this->check();
        if ($_GET['type'] !== 'fa') {
            if (!empty($_GET['status'])) {
                $map['status'] = $_GET['status'];
            } else {
                $map['status'] = array('in', '0,1');
            }
            $map['toid'] = $_SESSION['uid'];
        } else {
            $map['fromid'] = $_SESSION['uid'];
        }

        import("ORG.Util.Page");
        $count = D('UserMessage')->where($map)->count();
        $page = new Page($count, C('PAGE_SIZE'));
        $page->setConfig('first', '首页');
        $page->setConfig('last', '末页');
        $page->setConfig('theme', ' %upPage%  %first%  %prePage%  %linkPage%  %nextPage% %end%%downPage%<span>%totalRow%条</span> <span>%nowPage%/%totalPage% 页</span>');
        $message = D('UserMessage')->where($map)->limit($page->firstRow . ',' . $page->listRows)->order('id  DESC')->relation(true)->select();
        $show = $page->show();
        $this->assign('page', $show);
        $this->assign('message', $message);
        $this->assign('title', '我的消息');
        $this->display();
    }

    public function sms() {
        $this->check();
        if (!empty($_GET['id'])) {
            $map['toid'] = $_SESSION['uid'];
            $map['mid'] = $_GET['id'];
            $re = D('UserMessage')->where($map)->setField('status', '1');
            $c = D('Message')->where('id=' . $_GET['id'])->find();
            $this->assign($c);
            $this->display();
        } else {

            $this->redirect("Public/404");

        }
    }

    public function addsms() {
        $this->check();
        $this->assign('title', '发件箱');
        $this->display();
    }

    public function insertsms() {
        $this->check();
        $data = D('Message')->create();
        $data['create_time'] = time();
        $re = D('Message')->add($data);
        $row['fromid'] = $_SESSION['uid'];
        $row['toid'] = 0;
        $row['status'] = 0;
        $row['mid'] = $re;

        $re2 = D('UserMessage')->add($row);
        if ($re == true && $re2 == true) {
            echo_json('1', '操作成功', '发送成功！', U('User/message', 'type=fa'), '10');
        } else {
            echo_json('0', '操作失败', '发送失败！', '', '10');
        }
    }

    //内容页中加标签
    public function addtag() {
        $this->check();
        $data['tag'] = D('User')->where('id=' . $_SESSION['uid'])->getField('tag');
        $utags = explode(',', $data['tag']);
        $data['id'] = $_SESSION['uid'];
        if (!empty($_GET['id'])) {
            $d = explode('|', $_GET['id']);
            $id = $d[0];
            $k = $d[1];
            $t = D('Article')->where('id=' . $id)->getField('tag');
            $tags = explode(',', $t);
            //判断这个用户是否已经添加这个标签
            if (in_array($tags[$k], $utags)) {
                $addmessage  = "'".$tags[$k]."'已添加！";
                echo_json('0', '操作失败',$addmessage, '', '10');
            } else {
                $data['tag'] .= $tags[$k] . ',';
                $re = D('User')->save($data);

                if ($re !== FALSE) {
                    $_SESSION['tags'] = $data['tag'];
                    echo_json('1', '操作成功', '订阅成功！', '', '10');
                }
            }
        } elseif (!empty($_POST['tag'])) {

            if(!trim($_POST['tag'])){
                echo_json('0', '操作失败', '订阅标签不可为空！', '', '10');
            }
            if (in_array(strip_tags($_POST['tag']), $utags)) {
                echo_json('0', '操作失败', '您已添加过此标签！', '', '10');
            }
            $data['tag'] .= strip_tags($_POST['tag']) . ',';
            $re = D('User')->save($data);
            if ($re !== FALSE) {
                $_SESSION['tags'] = $data['tag'];
                echo_json('1', '操作成功', '订阅成功！', U('User/addtag'), '10');
            }
        } else {
            $tag = explode(',', $data['tag']);
            $this->assign('title', '我的订阅');
            $this->assign('tag', $tag);
            $this->display();
        }
    }

//用户标签管理
    public function tag() {
        $this->check();
        $this->display();
    }

    //用户删除标签
    public function deltag() {
        $this->check();
        $tag = D('User')->where('id=' . $_SESSION['uid'])->getField('tag');
        $tags = explode(',', $tag);
        $data['id'] = $_SESSION['uid'];

        foreach ($tags as $k => $v) {
            if ($k !== intval($_GET['k'])) {
                $ta[$k] = $v;
            }
        }
        $data['tag'] = implode(',', $ta);
        $re = D('User')->save($data);
        if (!$re == FALSE) {
            $_SESSION['tags'] = $data['tag'];
            echo_json('1', '操作成功', '删除标签成功！', U('User/addtag'), '10');
        }
    }

    //ajax注册绑定房间，
    function ajax_getBindHouList() {

//       参数为  1用户姓名、2身份证号、3银行卡号
        $list = R('Api/getBindHouseList', array(trim($_POST['ownerName']," "), trim($_POST['paperCode']," "), trim($_POST['bankNumber']," ")));
        echo $this->ajaxReturn($list,'JSON');
        exit();
    }

    //ajax输出房间基本信息，
    function ajax_getHouse() {
        $this->check();
//      参数为1房间编号
        $list = R('Api/getHouse', array($_POST['houseCode']));
        $_SESSION['selected_houseCode_name'] = $list['OWNERNAME'];

        echo json_encode($list);
    }

    //ajax输出房间状态
    function ajax_getHouseStatus() {
        //$this->check();
        $data = array();
        $url = 'http://10.105.15.2/TjstcWebImpl/GetHouseItemStateServlet';
        $post_data['vHOUSECODE'] = $_POST['houseCode'];
        $res = json_decode($this->request_post($url,$post_data));
        $list = $res->{'r_body'}[0];
        if($list->{'WATERSTATUS'} == '正常'){
            $_SESSION['jump1']=1;
        }else{
            $_SESSION['jump1']=0;
        }
        if($list->{'ZSSSTATUS'} == '开通'){
            $_SESSION['jump2']=1;
        }else{
            $_SESSION['jump2']=0;
        }
        echo json_encode($list);
    }

    function ajax_setCurrentHouse() {
        // check user login status
        $this->check();
        $_SESSION['selected_houseCode'] = $_POST['houseCode'];
    }

    //ajax输出房间详细信息，
    function ajax_getHouseInfo() {
        $this->check();
        $data = array();
        $url = 'http://10.105.15.2/TjstcWebImpl/GetHouseInfoServlet';
        $post_data['vHOUSECODE'] = $_POST['houseCode'];
        $res = json_decode($this->request_post($url,$post_data));
        $list = $res->{'r_body'}[0];
        echo json_encode($list);
    }

    function ajax_jilushijian() {
        $nowtime=time();
        $list['shijian'] = $nowtime;
        echo json_encode($list);
    }


    //ajax输出居住人数、环保积分
    function ajax_getInfoNumber() {
        $this->check();

        $map1['id'] = $_SESSION ['uid'];
        $res = D('User')->where($map1)->select();

        $map['uid'] = $_SESSION ['uid'];
        $map['houseCode'] = $_POST['houseCode'];
        $res1 = D('UserRoom')->where($map)->select();
		$jfCode=$res1[0]["cardnum"];
		$hbToken = R('Api/getJfToken', array('stcapiusr','134679','192.168.2.127'));
		$jfToken = $hbToken['Data'];
		$hbJF = R('Api/getJf', array($jfCode,$jfToken,'192.168.2.127'));
		$data[0]['point'] =$ret['Bonus'];

        //$data[0]['point'] = $res[0]["point"];
        $data[0]['cardnum'] = $res1[0]["cardnum"];
        if($data[0]['cardnum'] == null){$data[0]['cardnum'] = '--';}
        $data[0]['houseHoldSize'] = $res1[0]["houseHoldSize"];

        echo $this->ajaxReturn($data,'JSON');
        exit();
    }

    //获取房间环保积分状态
    function ajax_getHuanBaoStatus(){
        $data['uid'] = $_SESSION['uid'];
        $data['houseCode'] = $_POST['houseCode'];
        $data['ifBind'] = 1;
        $re = D('UserRoom')->where($data)->find();
        if(!empty($re['cardnum'])){
            $data = 1;
            $_SESSION['jump3']=1;
            $_SESSION['cardnum']=$re['cardnum'];
        }
        else{
            $data = 0;
            $_SESSION['jump3']=0;
        }
        echo $this->ajaxReturn($data,'JSON');
        exit();
    }

    //判断手机号码是否存在
    function ajax_getPhoneStatus(){

        $data['phone'] = $_POST['phone'];

        $re = D('User')->where($data)->find();
        if(!empty($re['id'])){
            $data = 0;
        }
        else{
            $data = 1;
        }
        echo $this->ajaxReturn($data,'JSON');
        exit();
    }

    //ajax输出房间表读数
    function ajax_getNumber() {
        $this->check();
//      参数为1房间编号
        $list = R('Api/getNumber', array($_POST['houseCode']));

        echo json_encode($list);
    }

    //ajax输出房间表读数
    function ajax_getHotNumber() {
        $this->check();
//      参数为1房间编号
        $list = R('Api/getHotNumber', array($_POST['houseCode']));

        echo json_encode($list);
    }

    //ajax输出房间表读数
    function ajax_getHotNumber1() {
        $this->check();
//      参数为1房间编号
        $list = R('Api/getHotNumber1', array($_POST['houseCode']));

        echo json_encode($list);
    }

    //ajax输出房间表读数
    function ajax_getMeterNumber() {
        $this->check();
//      参数为房间编号
        $data['housecode'] = $_POST['houseCode'];
        $list = D('Energy_meter')->where($data)->select();

        echo json_encode($list);
    }

    //ajax输出房间表读数
    function ajax_getHotNowNumber() {
        $this->check();
//      参数为1房间编号
        $list = R('Api/getHotNowNumber', array($_POST['houseCode']));

        echo json_encode($list);
    }


    function ajax_getHouseApply1() {
        $this->check();
//      参数为1房间编号
        $list = R('Api/getHouse1', array($_POST['houseCode']));

        echo json_encode($list);
    }

    //ajax输出房间基本信息（供热优惠申请）
    function ajax_getHouseApply() {
        $this->check();
//      参数为1房间编号
        $list = R('Api/getOracleHouse', array($_POST['houseCode']));

        echo json_encode($list);
    }

    //ajax输出房间2015-2016供热应缴费用
    function ajax_getHouseFee() {
        $this->check();
//      参数为1房间编号
        $list = R('Api/getOracleFee', array($_POST['houseCode']));

        echo json_encode($list);
    }

    //ajax输出房间2016-2017供热应缴费用(“本季应交金额”，“优惠金额”，“结余金额”，“实际应缴金额”)
    function ajax_getHouseJf() {
        $this->check();
//      参数为1房间编号
        $list = R('Api/getJffy', array($_POST['houseCode']));

        echo json_encode($list);
    }


    //ajax输出欠费信息，
    function ajax_getOweCharge() {
        $this->check();
//        参数为1房间编号
        $list = R('Api/getOweCharge', array($_POST['houseCode']));
        echo json_encode($list);
    }

    //ajax输出采暖欠费信息，
    function ajax_getCnCharge() {
        $this->check();
//        参数为1房间编号
        $list = R('Api/getCnmx', array($_POST['houseCode']));
        echo json_encode($list);
    }

    //ajax输出采暖计量信息，
    function ajax_getHotJL() {
        $this->check();
//        参数为1房间编号
        $list = R('Api/getCnjl', array($_POST['houseCode']));
        echo json_encode($list);
    }

    //ajax输出采暖欠费信息，
    function ajax_getCnCharge1() {
        $this->check();
//        参数为1房间编号
        $list = R('Api/getCnmx3', array($_POST['houseCode']));

        echo json_encode($list);
    }

    //ajax 曲线图
    function ajax_getHisChar() {
        $this->check();
        $list = R('Api/getHisChar', array($_POST['houseCode'], $_POST['itemCode'], $_POST['chargeMonth'], $_POST['buss']));

        //start update by wangshipeng 2014.06.01

        echo json_encode($list);
    }

    //查询绑定的房间编号
    function fjbh($uid) {
        $map['uid'] = $uid;
        $map['ifBind'] = array('between','1,2');
        $Code = D('UserRoom')->where($map)->Field('houseCode')->select();
        //$Code = D('UserRoom')->where('uid=' . $uid)->Field('houseCode')->select();
        foreach ($Code as $v) {
            $houseCode[] = $v['houseCode']; //得到现有绑定房间编号
        }
        return $houseCode;
    }

    //查找已绑定的房间(房主)
    function alreadyBind($uid) {
        $map['uid'] = $uid;
        $map['ifBind'] = 1;
        $houselist = D('UserRoom')->where($map)->select();
        $data = array();
        foreach($houselist as $key => $v){
            $value = R('Api/getAddress', array($v['houseCode']));
            $data[$key]['address'] = $value[0]['ADDRESS'];
            $data[$key]['houseCode'] = $v['houseCode'];
            $data[$key]['houseName'] = $v['houseName'];
        }
        return $data;
    }

    //查询房间信息
    function fjdz($uid,$housecode) {
        $map['uid'] = $uid;
        $map['houseCode'] = $housecode;
        $map['ifBind'] = 1;
        $housename = D('UserRoom')->where($map)->find();
        $address = R('Api/getAddress', array($housecode));
        $housename['address'] = $address[0]['ADDRESS'];
        $housename['houseName'] = $address[0]['ADDRESS'];
        return $housename;
    }

    //查询绑定的房间
    function fjbd($uid) {
        $map['uid'] = $uid;
        $map['ifBind'] = array('between','1,2');

        $houselist = D('UserRoom')->where($map)->field('id,houseCode,houseName,address')->select();

        $data = array();
        foreach($houselist as $key => $v){

            $value = R('Api/getAddress', array($v['houseCode']));
            $data[$key]['address'] = $value[0]['ADDRESS'];
            $data[$key]['communityname'] = $value[0]['COMMUNITYNAME'];
            $data[$key]['id'] = $v['id'];
            $data[$key]['houseCode'] = $v['houseCode'];
            $data[$key]['houseName'] = $v['houseName'];
        }
        return $data;
    }

    //查询指定的房间
    function zdfj($uid,$housecode) {
        $map['uid'] = $uid;
        $map['houseCode'] = $housecode;
        $map['ifBind'] = array('between','1,2');

        $houselist = D('UserRoom')->where($map)->field('id,houseCode,houseName,address')->select();

        $data = array();
        foreach($houselist as $key => $v){

            $value = R('Api/getAddress', array($v['houseCode']));
            $data[$key]['address'] = $value[0]['ADDRESS'];
            $data[$key]['communityname'] = $value[0]['COMMUNITYNAME'];
            $data[$key]['id'] = $v['id'];
            $data[$key]['houseCode'] = $v['houseCode'];
            $data[$key]['houseName'] = $v['houseName'];
        }
        return $data;
    }


    public function index2() {
        $this->check();

        $conn = oci_connect("tjstc","tjstc", "10.102.12.62:1521/hxorcl", "ZHS16GBK");
        if (! $conn) {
            $e = oci_error ();
            print htmlentities ( $e ['message'] );
            echo 'no';
            exit ();
        }else{
            echo 'ok';
        }
        $sql = "select communityname||buildingname||cellcode||'单元'||floor||'层'||doorplatecode||'室' as address , housecode  from view_house_qi where houseCode ='5008-005-01-17-04'" ;
        $stmt = oci_parse ( $conn, $sql );
        oci_execute ( $stmt );
        dump( oci_fetch_array ( $stmt ));
        while ( ($row = oci_fetch_array ( $stmt )) ) {
            echo ($row ['ADDRESS']);
        }

        $this->now = date('Y', time());

        $this->assign('title', '我的5890');
        $this->display();
    }

    function fjbd2($uid) {
        $map['uid'] = $uid;
        $map['ifBind'] = array('between','1,2');
        dump( "第1组开始时间".date('y-m-d H:i:s.ms',time()));
        $houselist = D('UserRoom')->where($map)->field('id,houseCode,houseName,address')->select();
        dump( "第1组开始时间".date('y-m-d H:i:s.ms',time()));
        $data = array();
        foreach($houselist as $key => $v){
            dump( "第2组开始时间".date('y-m-d H:i:s.ms',time()));
            $conn = oci_connect("tjstc","tjstc", "10.102.12.62:1521/hxorcl", "ZHS16GBK");
            if (! $conn) {
                $e = oci_error ();
                dump("no");
                print htmlentities ( $e ['message'] );
                exit ();
            }else{
                dump("ok");
            }
            $sql = "select communityname||buildingname||cellcode||'单元'||floor||'层'||doorplatecode||'室' as address , housecode  from view_house_qi where houseCode ='".$v['houseCode']."'" ;
            dump( oci_parse ( $conn, $sql ));
            $stmt = oci_parse ( $conn, $sql );
            oci_execute ( $stmt );
            dump( oci_execute ( $stmt ));
            while ( ($row = oci_fetch_array ( $stmt, OCI_ASSOC )) ) {
                $data[$key]['address'] = $row['ADDRESS'];
                dump($row[0]);
            }
            dump( "第2组开始时间".date('y-m-d H:i:s.ms',time()));
            //$value = R('Api/getAddress', array($v['houseCode']));
            $data[$key]['id'] = $v['id'];
            $data[$key]['houseCode'] = $v['houseCode'];
            $data[$key]['houseName'] = $v['houseName'];
        }
        return $data;
    }

    function oracle_con() {
        //resource oci_connect ( string $username , string $password [, string $db [, string $charset [, int $session_mode ]]] )
//oracle://tjstc:tjstc@10.102.12.62:1521/hxorcl
        $conn = oci_connect("tjstc","tjstc", "10.102.12.62:1521/hxorcl", "ZHS16GBK");
        if (! $conn) {
            $e = oci_error ();
            print htmlentities ( $e ['message'] );
            exit ();
        }
        return $conn;
    }

    //判断用户是否对具体房间进行绑定
    public function houseBind($uid,$housecode) {
        $map['uid'] = $uid;
        $map['houseCode'] = $housecode;
        $housebind = D('UserRoom')->where($map)->select();
        return $housebind;
    }

    //绑定成功
    function bindingok() {
        $this->check();
        $this->display();
    }

    //解绑失败
    function unbindno() {
        $this->check();
        $this->display();
    }

    //解绑成功
    function unbindok() {
        $this->check();

        /*解绑房间*/
        $where['houseCode'] = $_POST['housecode'];
        $where['uid'] = $_SESSION['uid'];

        $re = D('UserRoom')->where($where)->setField('ifBind','0');
        $this->housecode = $_POST['housecode'];
        if ($re !== false) {
            $this->display();
        } else {
            $this->display('User/unbindno');
        }
    }

    //绑定页面
    function bind() {
        $this->check();
        $villagelist = array();
        $info = D('user')->where('id=' . $_SESSION['uid'])->find();
        if(empty($info)){
            $this->redirect("Public/404");
        }
        //获取小区
        $url = 'http://10.105.15.2/TjstcWebImpl/GetCommunityNameServlet';
        $post_data['vCOMMUNITYCODE'] = '';
        $vill = json_decode($this->request_post($url,$post_data));
        //订单编号处理
        for($k = 0; $k < count($vill->{'r_body'}); $k++){
            $villagelist[$k]['COMMUNITYCODE'] = $vill->{'r_body'}[$k]->{"COMMUNITYCODE"};
            $villagelist[$k]['COMMUNITYNAME'] = $vill->{'r_body'}[$k]->{"COMMUNITYNAME"};
        }
        //获取绑定房间
        $map['uid'] = $_SESSION['uid'];
        $map['ifBind'] = array('between','1,2');
        $houselist = D('UserRoom')->where($map)->select();
        /*if(count($houselist>3)){
            $this->error("同一账号下最多能绑定三个房间！", '/index.php?s=/user/index.html');
        }*/
        $this->houselist = $houselist;
        $this->villagelist = $villagelist;
        $this->assign('title', '绑定房间');
        $this->assign($info);
        $this->display();
    }
    //强制绑定申请页面
    function qxbind() {
        $this->check();
        $villagelist = array();
        $info = D('user')->where('id=' . $_SESSION['uid'])->find();
        if(empty($info)){
            $this->redirect("Public/404");
        }
        //获取小区
        $url = 'http://10.105.15.2/TjstcWebImpl/GetCommunityNameServlet';
        $post_data['vCOMMUNITYCODE'] = '';
        $vill = json_decode($this->request_post($url,$post_data));
        //订单编号处理
        for($k = 0; $k < count($vill->{'r_body'}); $k++){
            $villagelist[$k]['COMMUNITYCODE'] = $vill->{'r_body'}[$k]->{"COMMUNITYCODE"};
            $villagelist[$k]['COMMUNITYNAME'] = $vill->{'r_body'}[$k]->{"COMMUNITYNAME"};
        }
        //获取绑定房间
        $map['uid'] = $_SESSION['uid'];
        $map['ifBind'] = array('between','1,2');
        $houselist = D('UserRoom')->where($map)->select();
        /*if(count($houselist>3)){
            $this->error("同一账号下最多能绑定三个房间！", '/index.php?s=/user/index.html');
        }*/
        $this->houselist = $houselist;
        $this->villagelist = $villagelist;
        $this->assign('title', '绑定房间');
        $this->assign($info);
        $this->display();
    }

    //获取楼号
    function getBuilding() {
        $data = array();
        $url = 'http://10.105.15.2/TjstcWebImpl/GetBuildingNameServlet';
        $post_data['vCOMMUNITYCODE'] = $_POST['parameter'];
        $res = json_decode($this->request_post($url,$post_data));
        //订单编号处理
        for($k = 0; $k < count($res->{'r_body'}); $k++){
            $data[$k]['BUILDINGCODE'] = $res->{'r_body'}[$k]->{"BUILDINGCODE"};
            $data[$k]['BUILDINGNAME'] = $res->{'r_body'}[$k]->{"BUILDINGNAME"};
        }
        echo $this->ajaxReturn($data,'JSON');
        exit();
    }

    //获取单元
    function getUite() {
        $data = array();
        $url = 'http://10.105.15.2/TjstcWebImpl/GetCellNameServlet';
        $post_data['vBUILDINGCODE'] = $_POST['parameter'];
        $res = json_decode($this->request_post($url,$post_data));
        //订单编号处理
        for($k = 0; $k < count($res->{'r_body'}); $k++){
            $data[$k]['CELLCODE'] = $res->{'r_body'}[$k]->{"CELLCODE"};
        }
        echo $this->ajaxReturn($data,'JSON');
        exit();
    }

    //获取门牌号
    function getFloor() {
        $data = array();
        $url = 'http://10.105.15.2/TjstcWebImpl/GetHouseNumberServlet';
        $post_data['vCELLCODE'] = $_POST['parameter'];
        $post_data['vBUILDINGCODE'] = $_POST['parameter1'];
        $res = json_decode($this->request_post($url,$post_data));
        //订单编号处理
        for($k = 0; $k < count($res->{'r_body'}); $k++){
            $data[$k]['FLOORDOORPLATECODENUM'] = $res->{'r_body'}[$k]->{"FLOORDOORPLATECODENUM"};
            $data[$k]['FLOORDOORPLATECODE'] = $res->{'r_body'}[$k]->{"FLOORDOORPLATECODE"};
        }
        echo $this->ajaxReturn($data,'JSON');
        exit();
    }
    //检验手机验证码
    function check_houseinfo(){
        $data = array();
        $url = 'http://10.105.15.2/TjstcWebImpl/CheckHouseInfoServlet';
        $post_data['vHOUSECODE'] = $_POST['vHOUSECODE'];
        $post_data['vOWNERNAME'] = $_POST['vOWNERNAME'];
        $post_data['vPAPERCODE'] = $_POST['vPAPERCODE'];
        //$post_data['vCARDENDDATE'] = substr($_POST['vCARDENDDATE'],2,2).'/'.substr($_POST['vCARDENDDATE'],6,2);
        //$post_data['vBANKNUMBER'] = $_POST['vBANKNUMBER'];
        $res = json_decode($this->curl_post($url,$post_data));
        $data['CODE'] = $res->{'r_code'};
        echo $this->ajaxReturn($data,'JSON');
        exit();
    }

	//检验手机是否属于该用户
    function check_phoneRight(){
        $data = array();
        $url = 'http://10.105.15.2/TjstcWebImpl/CheckHouseMobilephoneServlet';
        $post_data['vHOUSECODE'] = $_POST['vHOUSECODE'];
		$post_data['vMOBILEPHONE'] = $_POST['vMOBILEPHONE'];
        $post_data['vOWNERNAME'] = $_POST['vOWNERNAME'];
        //$post_data['vCARDENDDATE'] = substr($_POST['vCARDENDDATE'],2,2).'/'.substr($_POST['vCARDENDDATE'],6,2);
        //$post_data['vBANKNUMBER'] = $_POST['vBANKNUMBER'];
        $res = json_decode($this->curl_post($url,$post_data));
        $data['CODE'] = $res->{'r_code'};
        echo $this->ajaxReturn($data,'JSON');
        exit();
    }

    //检验是否重复绑定
    /*function ajax_editInfo(){
        $map['uid'] = $_SESSION['uid'];
        $map['houseCode'] = $_POST['houseCode'];
        $data['houseHoldSize'] = $_POST['houseHoldSize'];
        $re = D('UserRoom')->where($map)->save($data);
        $map1['id'] = $_SESSION['uid'];
        $data1['point'] = $_POST['point'];
        $re1 = D('User')->where($map1)->save($data1);
        if($re && $re1){
            $data = 1;
        }
        else{
            $data = 0;
        }
        echo $this->ajaxReturn($data,'JSON');
        exit();
    }*/
    function ajax_editInfo(){
        $map['uid'] = $_SESSION['uid'];
        $map['houseCode'] = $_POST['houseCode'];
        $data['houseHoldSize'] = $_POST['houseHoldSize'];
        $data['cardnum'] = $_POST['huanbaoka']=='--'?'':$_POST['huanbaoka'];
        $re = D('UserRoom')->where($map)->save($data);
        if($re){
            $data = 1;
        }
        else{
            $data = 0;
        }
        echo $this->ajaxReturn($data,'JSON');
        exit();
    }

    //检验手机验证码
    function check_phone_verify_code(){
        $phoneActiveCode = $_POST['phoneActiveCode'];

        if (time() > session('token_getpass_time')) {
            $data = 2;
        }else if (session('phoneActiveCode') != $_POST['phoneActiveCode']){
            $data = 1;
        }/*else{
            unset($_SESSION['phoneActiveCode']);unset($_SESSION['token_getpass_time']);
        }*/
        echo $this->ajaxReturn($data,'JSON');
        exit();
    }
    //检验房间是否已绑定
    function check_ifbind(){
        $data['houseCode'] = $_POST['vHOUSECODE'];
        $data['ifBind'] = 1;
        $re = D('UserRoom')->where($data)->find();
        if(!empty($re)){
            $data = 0;
        }
        else{
            $data = 1;
        }
        echo $this->ajaxReturn($data,'JSON');
        exit();
    }
	//申请解绑房间处理
    function qxbindok() {
        $this->check();
        $housecode=$_POST['housecode'];
        $owername=$_POST['owername'];
		$papercode=$_POST['papercode'];
		$uid=$_SESSION['uid'];
		//var_dump($_FILES);exit;
		header("Content-type:text/html;charset=utf-8");
        if($_FILES['sfz']['name'][0] && $_FILES['sfz']['name'][1]){
            import('@.ORG.UploadFile');
            $upload = new UploadFile(); // 实例化上传类
            $upload->maxSize = 2 * 1024 * 1024; //设置上传图片的大小2mb
            $upload->allowExts = array('jpg'); //设置上传图片的后缀
            // $upload->uploadReplace = true;     //同名则替换
            $upload->saveRule = 'uniqid'; //设置上传头像命名规则(临时图片),修改了UploadFile上传类
            //完整的头像路径
            $path = './Uploads/shenhe/';
            $upload->savePath = $path;
            if (!$upload->upload()) { // 上传错误提示错误信息
                 echo "<script>alert('图片上传失败，请稍后重试');</script>";
				echo "<script language='javascript'>";
				echo 'window.top.location="http://www.66885890.com/index.php?s=/user/qxbind"';
				echo "</script>";	
            } else { // 上传成功 获取上传文件信息
                $info = $upload->getUploadFileInfo();
				$imgnew1=$info[0]['savepath'].$info[0]['savename'];
				$img1name=$info[0]['savename'];
				$imgnew2=$info[1]['savepath'].$info[1]['savename'];
				$img2name=$info[1]['savename'];
				
				$base641 = file_get_contents($imgnew1);
                $base64img1 =base64_encode($base641);
				$base642 = file_get_contents($imgnew2);
                $base64img2 =base64_encode($base642);
						if($_FILES['sfz']['name'][2] && $_FILES['sfz']['name'][3]){
							$imgnew3=$info[2]['savepath'].$info[2]['savename'];
							$img3name=$info[2]['savename'];
							$imgnew4=$info[3]['savepath'].$info[3]['savename'];
							$img4name=$info[3]['savename'];
				
							$base643 = file_get_contents($imgnew3);
							$base64img3 =base64_encode($base643);
							$base644 = file_get_contents($imgnew4);
							$base64img4 =base64_encode($base644);
							$imgarr = "[{'fileName': '".$img1name."','fileBase64':'". $base64img1."','fileType':'能源合同'},{'fileName':'".$img2name."','fileBase64':'".$base64img2."','fileType':'能源合同'},{'fileName': '".$img3name."','fileBase64':'". $base64img3."','fileType':'再生水合同'},{'fileName':'".$img4name."','fileBase64':'".$base64img4."','fileType':'再生水合同'}]";
						}else{
						$imgarr = "[{'fileName': '".$img1name."','fileBase64':'". $base64img1."','fileType':'能源合同'},{'fileName':'".$img2name."','fileBase64':'".$base64img2."','fileType':'能源合同'}]";}
						//echo $imgarr;exit;
                            //如果都成功，调用接口存储数据
                            $houseInfo = R('Api/ApplyBindHouseServlet', array($housecode,$owername,$papercode,'网站',$uid,$imgarr));
                            if($houseInfo->{'r_code'} == '0000'){
                                echo "<script>alert('提交成功，请等待工作人员处理');</script>";
								echo "<script language='javascript'>";
								echo 'window.top.location="http://www.66885890.com/index.php?s=/user/index"';
								echo "</script>";
                            }elseif($houseInfo->{'r_code'} == '8009'){
                                echo "<script>alert('您有未审核完成的申请，待审核完成后可以再次提交。');</script>";
								echo "<script language='javascript'>";
								echo 'window.top.location="http://www.66885890.com/index.php?s=/user/index"';
								echo "</script>";	
                            }else{
                                echo "<script>alert('图片上传失败，请稍后重试');</script>";
								echo "<script language='javascript'>";
								echo 'window.top.location="http://www.66885890.com/index.php?s=/user/qxbind"';
								echo "</script>";	
                            }
                        

            }
        }else{
             echo "<script>alert('请上传能源合同或能源合同上传失败，请稍后重试');</script>";
				echo "<script language='javascript'>";
				echo 'window.top.location="http://www.66885890.com/index.php?s=/user/qxbind"';
				echo "</script>";	
        }

    }
    //绑定房间
    function bindok() {
        $this->check();
        $houseCode = $_POST['housecode'];

        if (!empty($houseCode)) {

            $data['uid'] = $_SESSION['uid'];
            $data['houseCode'] = $houseCode;
            $data['houseName'] = $_POST['housename'];
            $data['address'] = $_POST['housename'];
            $data['houseHoldSize'] = $_POST['houseHoldSize'];
            $data['cardnum'] = $_POST['energycard'];
            $data['bankNumber'] = $_POST['banknumber'];
            $data['ownerName'] = $_POST['ownername'];
            $data['paperCode'] = $_POST['papercode'];
            $data['bankEndData'] = $_POST['cardenddata'];
            $data['phone'] = $_POST['phone'];
            $data['bindtime'] = time();
            $data['ifBind'] = 1;
            $r = D('UserRoom')->add($data);
            $s = R('Api/bindInsert', array($r,$_SESSION['uid'],$houseCode));

            session('phoneActiveCode', null);
            session('token_getpass_time', null);
            $this->housecode = $houseCode;

            $this->display();
        } else {
            echo_json('0', '提示', '操作失败！原因：您未选择要绑定的房间！', '', '10');
        }
    }

    function unbind() {
        $this->check();
// Ins+ by lzhang 2014-02-23 ---------------------------------------------------
        $id = $this->_get('phoneActiveCodeUnbind');
        if (!empty($id)) {
            $id = trim($id);
        }
        if (empty($id)) {
            echo_json('0', '提示', '请填写手机验证码！', '', '10');
        }
        if (time() > session('token_getpass_time')) {
            echo_json('0', '提示', '手机验证码过期！', '', '10');
        }
        if (session('phoneActiveCode') != $id) {
            echo_json('0', '提示', '手机验证码错误，请核对！', '', '10');
        }
// Ins- by lzhang 2014-02-23 ---------------------------------------------------
        // Up by wangshipeng 2014-10-22
        //$where['uid'] = $_SESSION['uid'];
        /*查询解绑房间编号*/
        $map['id'] = $this->_get('id');
        $infor = D('UserRoom')->where($map)->Field('houseCode,ifBind')->find();
        /*解绑房间*/
        $where['houseCode'] = $infor['houseCode'];
        $where['id'] = $this->_get('id');
        $ifBind = $infor['ifBind'];
        if($ifBind == 1){
            /*向租客发送解绑消息*/
            //获取租客列表
            /*$rec['ifBind'] = 2;
            $rec['houseCode'] = $infor['houseCode'];
            $renlist = D('UserRoom')->where($rec)->select();
            if(!empty($renlist)){
                foreach($renlist as $v){
                    //租客id
                    $toid = $v['uid'];
                    //发送请求消息
                    $data['title'] = "您有一条新的绑定房间消息,请查收！";
                    $infor = D('User')->where('id=' . $_SESSION['uid'])->Field('nickname')->find();
                    $content = "很遗憾的通知您，您地址为： ".$v['address']." 的房间已经被房主解除绑定！";
                    $data['content'] = $content;
                    $data['create_time'] = time();
                    $data['issys'] = 0;
                    $data['housename'] = $_SESSION['uid'].",".$v['houseCode'].",".$v['address'];
                    $id = D('Message')->add($data);
                    //用户消息关联
                    $record['fromid'] = $_SESSION['uid'];
                    $record['toid'] = $toid;
                    $record['mid'] = $id;
                    $record['status'] = 0;
                    D('User_message')->add($record);
                }
            }*/
            $re = D('UserRoom')->where($where)->setField('ifBind','0');
        }
        else if($ifBind == 2){
            $re = D('UserRoom')->where($map)->setField('ifBind','0');
        }
        else{
            $re = false;
        }
        $s = R('Api/bindDelete', array($this->_get('id')));
// Up by wangshipeng 2014-10-22
        if ($re !== false) {
            session('phoneActiveCode', null);
            session('token_getpass_time', null);
            echo_json('1', '提示', '房间解绑成功！', '', '10');
        } else {
            echo_json('0', '提示', '房间解绑失败！', '', '10');
        }
    }

    public function editavator() {
        $this->check();
        $this->assign('title', '头像编辑');
        $this->display();
    }

    //上传头像
    public function uploadImg() {
        $checklogin=$this->_param('checklogin');

        if(empty($checklogin)){
            exit();
        }
        import('@.ORG.UploadFile');
        $upload = new UploadFile();      // 实例化上传类
        $upload->maxSize = 1 * 1024 * 1024;     //设置上传图片的大小
        $upload->allowExts = array('jpg', 'png', 'gif'); //设置上传图片的后缀
        // $upload->uploadReplace = true;     //同名则替换
        $upload->saveRule = 'uniqid';     //设置上传头像命名规则(临时图片),修改了UploadFile上传类
        //完整的头像路径
        $path = './Uploads/avatar/temp/';
        $upload->savePath = $path;
        if (!$upload->upload()) {      // 上传错误提示错误信息
            $this->ajaxReturn('', $upload->getErrorMsg(), 0, 'json');
        } else {           // 上传成功 获取上传文件信息
            $info = $upload->getUploadFileInfo();


            $temp_size = getimagesize($path .$info[0]['savename']);
            if ($temp_size[0] < 100 || $temp_size[1] < 100) {//判断宽和高是否符合头像要求
                $this->ajaxReturn(0, '图片宽或高不得小于100px！', 0, 'json');
            }

            $this->ajaxReturn($path . $info[0]['savename'], $info, 1, 'json');
        }
    }

    //裁剪并保存用户头像
    public function cropImg() {

        //图片裁剪数据
        $params = $this->_post();      //裁剪参数
        if (!isset($params) && empty($params)) {
            return;
        }

        //头像目录地址
        $path = './Uploads/avatar/';

        $userid = intval($_SESSION['uid']);
        $savename=$_SESSION['uname'].'_'.time();
        $avator = D('User')->where(' id =' . $userid)->getField('avator');
        $save['avator'] = "./Uploads/avatar/" . $savename . ".jpg";
        $r = D('User')->where(' id =' . $userid)->save($save);

        //要保存的图片
        $real_path = $path . $savename. '.jpg';
        //临时图片地址
        $pic_path = $params['savename'];

        import('@.ORG.ThinkImage');
        $Think_img = new ThinkImage(THINKIMAGE_GD);
        //裁剪原图
        $Think_img->open($pic_path)->crop($params['w'], $params['h'], $params['x'], $params['y'])->save($real_path);
        //生成缩略图
        //$Think_img->open($real_path)->thumb(100,100, 1)->save($path.'avatar_100.jpg');
        //$Think_img->open($real_path)->thumb(60,60, 1)->save($path.'avatar_60.jpg');
        //$Think_img->open($real_path)->thumb(30,30, 1)->save($path.'avatar_30.jpg');
        if ($r !== false) {
            if(file_exists ($avator)){
                delDirAndFile($avator);
            }
            $this->redirect('User/index');

        }else{
            $this->error('上传失败！');
        }

    }

    public function delmessage() {
        $this->check();

        $delarr = $_POST['checkmess'];
        $userid = intval($_SESSION['uid']);

        if (!empty($delarr)) {
            foreach ($delarr as $key => $val) {
                $re = D('UserMessage')->where("id = " . $val)->delete();
                if ($re == false) {
                    echo_json('0', '操作失败', '房间删除失败！', '', '10');
                }
            }
        } else {
            echo_json('0', '操作失败', '没有选择消息！', '', '10');
        }
        $url = U('User/message');
        if (!empty($_POST['type'])) {
            $url = U('User/message', 'type=fa');
        }
        echo_json('1', '操作成功', '消息删除成功！', $url, '10');
    }

    // 检查用户是否存在
    public function check_user() {
        $request = trim($_POST['name']);
        $username1 = $_SESSION ['uname'];
        $username = D("User")->field("username")->select();
        if($request == $username1){
            echo "true";
        }
        else{
            if (in_array(array("username" => $request), $username)) {
                echo "false";
            } else {
                echo "true";
            }
        }

        exit();
    }

    // 检查用户email是否存在
    public function check_email() {
        $request = urldecode(trim($_POST ['email']));
        $map['username'] = $_SESSION ['uname'];
        $email1 = D("User")->where($map)->field("email")->select();
        $email = D("User")->field("email")->select();
        if(in_array(array("email" => $request), $email1)){
            echo "false";
        }
        else{
            if (in_array(array("email" => $request), $email)) {
                echo "true";
            } else {
                echo "false";
            }
        }

        exit();
    }

	    // 检查用户phone是否存在
    public function check_phone() {
        $request = urldecode(trim($_POST ['phone']));
        $map['username'] = $_SESSION ['uname'];
        $phone = D("User")->where($map)->field("phone")->select();
        if(in_array(array("phone" => $request), $phone)){
            echo "false";
        }
        else {
                echo "true";
            }

        exit();
    }


}

?>
