<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2016/8/23
 * Time: 15:10
 */

class UnionAction extends HomeAction {

    public function index(){

           Vendor('Unionpay.acp_service');

		   $params = array(

			//以下信息非特殊情况不需要改动
			'version' => com\unionpay\acp\sdk\SDKConfig::getSDKConfig()->version,                 //版本号
			'encoding' => 'utf-8',				  //编码方式
			'txnType' => '01',				      //交易类型
			'txnSubType' => '01',				  //交易子类
			'bizType' => '000201',				  //业务类型
			'frontUrl' =>  com\unionpay\acp\sdk\SDKConfig::getSDKConfig()->frontUrl,  //前台通知地址
			'backUrl' => com\unionpay\acp\sdk\SDKConfig::getSDKConfig()->backUrl,	  //后台通知地址
			'signMethod' => com\unionpay\acp\sdk\SDKConfig::getSDKConfig()->signMethod,	              //签名方法
			'channelType' => '08',	              //渠道类型，07-PC，08-手机
			'accessType' => '0',		          //接入类型
			'currencyCode' => '156',	          //交易币种，境内商户固定156

			//TODO 以下信息需要填写
			'merId' => $_POST["merId"],		//商户代码，请改自己的测试商户号，此处默认取demo演示页面传递的参数
			'orderId' => $_POST["orderId"],	//商户订单号，8-32位数字字母，不能含“-”或“_”，此处默认取demo演示页面传递的参数，可以自行定制规则
			'txnTime' => $_POST["txnTime"],	//订单发送时间，格式为YYYYMMDDhhmmss，取北京时间，此处默认取demo演示页面传递的参数
			'txnAmt' => $_POST["txnAmt"],	//交易金额，单位分，此处默认取demo演示页面传递的参数
			'reqReserved' => $_POST["reqReserved"],	//保留域(账单时间/房间编号/缴费类型)
			// 订单超时时间。
			// 超过此时间后，除网银交易外，其他交易银联系统会拒绝受理，提示超时。 跳转银行网银交易如果超时后交易成功，会自动退款，大约5个工作日金额返还到持卡人账户。
			// 此时间建议取支付时的北京时间加15分钟。
			// 超过超时时间调查询接口应答origRespCode不是A6或者00的就可以判断为失败。
			'payTimeout' => date('YmdHis', strtotime('+15 minutes')),

			// 请求方保留域，
			// 透传字段，查询、通知、对账文件中均会原样出现，如有需要请启用并修改自己希望透传的数据。
			// 出现部分特殊字符时可能影响解析，请按下面建议的方式填写：
			// 1. 如果能确定内容不会出现&={}[]"'等符号时，可以直接填写数据，建议的方法如下。
			//    'reqReserved' =>'透传信息1|透传信息2|透传信息3',
			// 2. 内容可能出现&={}[]"'符号时：
			// 1) 如果需要对账文件里能显示，可将字符替换成全角＆＝｛｝【】“‘字符（自己写代码，此处不演示）；
			// 2) 如果对账文件没有显示要求，可做一下base64（如下）。
			//    注意控制数据长度，实际传输的数据长度不能超过1024位。
			//    查询、通知等接口解析时使用base64_decode解base64后再对数据做后续解析。
			//    'reqReserved' => base64_encode('任意格式的信息都可以'),

			//TODO 其他特殊用法请查看 special_use_purchase.php
		);

		com\unionpay\acp\sdk\AcpService::sign ( $params );
		$uri = com\unionpay\acp\sdk\SDKConfig::getSDKConfig()->frontTransUrl;
		$html_form = com\unionpay\acp\sdk\AcpService::createAutoFormHtml( $params, $uri );
		echo $html_form;
    }


	//银联缴费异步
    public function notify()
    {
        Vendor('Unionpay.acp_service');
        $logger = com\unionpay\acp\sdk\LogUtil::getLogger();
		$logger->LogInfo("receive back notify: " . com\unionpay\acp\sdk\createLinkString ( $_POST, false, true ));
        if (isset ( $_POST ['signature'] )) {
			echo com\unionpay\acp\sdk\AcpService::validate ( $_POST ) ? '验签成功' : '验签失败';
			$map['orderId'] = $_POST['orderId']; //其他字段也可用类似方式获取
			$map['txnAmt'] = $_POST['txnAmt']/100; //其他字段也可用类似方式获取
			$map['respCode'] = $_POST['respCode'];
			$map['txnTime'] = $_POST["txnTime"];	//订单发送时间，格式为YYYYMMDDhhmmss，取北京时间，此处默认取demo演示页面传递的参数
			$operaterdate = substr($_POST["txnTime"],0,4).'-'.substr($_POST["txnTime"],4,2).'-'.substr($_POST["txnTime"],6,2);
			$str = explode("@",$_POST["reqReserved"]);
			$map['houseCode'] = $str[0];
			$map['type'] = $str[1];
			$map['chargeMonth'] = $str[2];
			$map['clockNumber'] = $str[3];
			
			/*$str = $_POST["reqReserved"];
			$cd = strlen($str)-20;
			$map['houseCode'] = substr($str,0,13);
			$map['type'] = substr($str,13,1);
			$map['chargeMonth'] = substr($str,14,6);
			$map['clockNumber'] = substr($str,20,$cd);*/
			//判断respCode=00、A6后，对涉及资金类的交易，请再发起查询接口查询，确定交易成功后更新数据库。
			if($respCode == 00){
				$url = 'http://10.105.15.2/tjsck_impl/NewPayServlet';
				$post_data['vbankSerial'] = $_POST ['orderId'];
				$post_data['vrcvAmt'] = $_POST ['txnAmt']/100;

				$post_data['vclocknumber'] = $str[3];
				//$log = $map['houseCode'].'@'.$map['type'].'@'.$map['chargeMonth'].'@'.$map['clockNumber'];
				//file_put_contents('F:/websoft/web/phpwind/MyCms/Modules/Home/Action/log.txt',$log);
				$res = json_decode($this->request_post($url, $post_data));
				if($res->{'r_body'}[0]->{"rtnCode"}='9999'){
					$map['platformStatus']=$res->{'r_body'}[0]->{"rtnCode"};
					//$map['platformMessage']=$res->{'r_body'}[0]->{"rtnMsg"};
					$map['platformMessage']='交易成功';
					$Message="交易成功";
				}else{
					$map['platformStatus']=$res->{'r_body'}[0]->{"rtnCode"};
					//$map['platformMessage']=$res->{'r_body'}[0]->{"rtnMsg"};
					$map['platformMessage']='交易成功,收费平台同步失败,请在24小时后再查询!';
					$Message="交易成功,收费平台同步失败,请在24小时后再查询!";
				}
				$url = C('DB_ORACLE');
				$lszd = M('',null,$url);
				$sql = "insert into SF_BILL_YINLIAN VALUES ('".$_POST['orderId']."','".$_POST['respCode']."','".$map['txnAmt']."','".$operaterdate."','".$str[0]."','".$str[2]."','".$str[1]."','".$str[3]."','5890')";
				$lszd->query($sql);

				D('Bill_Yinlian')->add($map);

				$Message =  '交易成功';
			}

		} else {
			$Message =  '签名为空';
			//echo '签名为空';
		}
		$map['Message']=$Message;
        $this->map=$map;
        $this->display();


    }


	//银联缴费同步
    public function front()
    {
        Vendor('Unionpay.acp_service');
        $logger = com\unionpay\acp\sdk\LogUtil::getLogger();
		$logger->LogInfo("receive back notify: " . com\unionpay\acp\sdk\createLinkString ( $_POST, false, true ));
        if (isset ( $_POST ['signature'] )) {

			echo com\unionpay\acp\sdk\AcpService::validate ( $_POST ) ? '验签成功' : '验签失败';
			$map['orderId'] = $_POST ['orderId']; //其他字段也可用类似方式获取
			$map['txnAmt'] = $_POST ['txnAmt']/100; //其他字段也可用类似方式获取
			$map['respCode'] = $_POST ['respCode'];
			$map['txnTime'] = $_POST["txnTime"];	//订单发送时间，格式为YYYYMMDDhhmmss，取北京时间，此处默认取demo演示页面传递的参数
			$operaterdate = substr($_POST["txnTime"],0,4).'-'.substr($_POST["txnTime"],4,2).'-'.substr($_POST["txnTime"],6,2);
			$str = explode("@",$_POST["reqReserved"]);
			$map['houseCode'] = $str[0];
			$map['type'] = $str[1];
			$map['chargeMonth'] = $str[2];
			$map['clockNumber'] = $str[3];
			//判断respCode=00、A6后，对涉及资金类的交易，请再发起查询接口查询，确定交易成功后更新数据库。
			if($respCode == 00){
				$url = C('DB_ORACLE');
				$lszd = M('',null,$url);
				$sql = "insert into SF_BILL_YINLIAN VALUES ('".$_POST['orderId']."','".$_POST['respCode']."','".$map['txnAmt']."','".$operaterdate."','".$str[0]."','".$str[2]."','".$str[1]."','".$str[3]."')";

				$lszd->query($sql);

				D('Bill_Yinlian')->add($map);
				$Message =  '交易成功';
			}

		} else {
			$Message =  '交易失败';
		}
		$map['Message']=$Message;
        $this->map=$map;
        $this->display();


    }


	public function down(){

	   Vendor('Unionpay.acp_service');

	   $txnTime = date('YmdHis');
	   $merId = '104120049000035';
	   $settleDate =  date("md",strtotime("-1 day"));

	   $params = array (

		// 以下信息非特殊情况不需要改动
		'version' => com\unionpay\acp\sdk\SDKConfig::getSDKConfig()->version,//版本号
		'signMethod' => com\unionpay\acp\sdk\SDKConfig::getSDKConfig()->signMethod,//签名方法
		'encoding' => 'utf-8',		           //编码方式
		'txnType' => '76', // 交易类型
		'txnSubType' => '01', // 交易子类
		'bizType' => '000000', // 业务类型
		'accessType' => '0', // 接入类型
		'fileType' => '00', // 文件类型

		// TODO 以下信息需要填写
		'txnTime' => $txnTime, // 订单发送时间，取北京时间，格式为YYYYMMDDhhmmss，此处默认取demo演示页面传递的参数
		'merId' => $merId, // 商户代码，请替换实际商户号测试，如使用的是自助化平台注册的商户号（777开头的），该商户号没有权限测文件下载接口的，请使用测试参数里写的文件下载的商户号和日期测，如需真实交易文件，请使用自助化平台下载文件，此处默认取demo演示页面传递的参数
		'settleDate' => $settleDate
) // 清算日期，格式为MMDD，此处默认取demo演示页面传递的参数
;

		com\unionpay\acp\sdk\AcpService::sign ( $params );
		$url = com\unionpay\acp\sdk\SDKConfig::getSDKConfig()->fileTransUrl;

		$result_arr = com\unionpay\acp\sdk\AcpService::post ( $params, $url );

		if (!com\unionpay\acp\sdk\AcpService::validate ($result_arr) ){
			echo "应答报文验签失败<br>\n";
			return;
		}

		//echo "应答报文验签成功<br>\n";
		if ($result_arr["respCode"] == "98"){
			//文件不存在
			//TODO
			echo "文件不存在。<br>\n";
			return;
		} else if ($result_arr["respCode"] != "00") {
			//其他应答码做以失败处理
			//TODO
			echo "失败：respCode=" . $result_arr["respCode"] . "。<br>\n";
			return;
		}

		echo "返回成功。<br>\n";

		$filePath = "f:/file/";
		//TODO 处理文件，保存路径上面那行设置，注意预先建立文件夹并授读写权限
		if ( com\unionpay\acp\sdk\AcpService::deCodeFileContent( $result_arr,  $filePath) == false) {
			echo '文件保存失败，请查看日志提示的错误信息<br\n>';
			return;
		}

		echo '文件已成功保存到' . $filePath. "目录下<br\n>";
    }

    //电子表列表页
    public function bill_old()
    {
       $this->check();
	   $arr = array();
	   $arr1 = array();
	   $arr2 = array();
	   $pagesize = 3;
	   //开始时间
        if (!empty($_REQUEST['kssj'])) {
			$kssj = $_REQUEST['kssj'];
            $index['kssj'] = $_REQUEST['kssj'];
        }
		else{
			 //$index['kssj'] = date("Y-m-d", strtotime("-1 month"));
			 $index['kssj'] = date("Y").'-01-01';
		}

		//结束时间
        if (!empty($_REQUEST['jssj'])) {
          $jssj = $_REQUEST['jssj'];
          $index['jssj'] = $_REQUEST['jssj'];
        }
		else{
			$index['jssj'] = date("Y-m-d");
		}
		$houseCode = $_REQUEST['houseCode'];
		$houselist = R('User/fjbd', array($_SESSION['uid']));
		if($_REQUEST['houseCode'] == '' || $_REQUEST['houseCode'] == NULL){
			$houseCode = $houselist[0]['houseCode'];
		}
		if (!empty($_REQUEST['page'])) {
           $page = $_REQUEST['page'];
        }
		else{
		   $page = 1;
		}

	    $url = 'http://10.105.15.2/tjsck_impl/DzfpQueryServlet';
		$post_data['vhousecode'] = $houseCode;
		$post_data['vstartdate'] = $index['kssj'];
		$post_data['venddate'] = $index['jssj'];
		$res = json_decode($this->request_post($url, $post_data));

		//订单编号处理
		for($k = 0; $k < count($res->{'r_body'}); $k++){
			$arr1[] = $res->{'r_body'}[$k]->{"billcode"};
			/*$arr2[$k]['infor']['jffs'] = $res->{'r_body'}[$k]->{"jffs"};
			$arr2[$k]['infor']['operatedate'] = $res->{'r_body'}[$k]->{"operatedate"};
			$arr2[$k]['infor']['dizhi'] = $res->{'r_body'}[$k]->{"dizhi"};*/
		}
		$result_01 = array_flip($arr1);
		$result_02 = array_flip($result_01);
		$result    = array_merge($result_02);

		if($res->{"r_code"}='0000'){
			for($j = 0; $j < count($result); $j++){
				$key = 0;

				for ($i = 0; $i < count($res->{'r_body'}); $i++) {
					if($result[$j] == $res->{'r_body'}[$i]->{"billcode"}){
						$arr[$j]['data'][$key]['jffs'] = $res->{'r_body'}[$i]->{"jffs"};
						$sshj = $res->{'r_body'}[$i]->{"sshj"};
						if(strpos($sshj,'.')==0 && $sshj !=0){
							$arr[$j]['data'][$key]['sshj'] = '0'. $sshj;
						}else{
							$arr[$j]['data'][$key]['sshj'] = $sshj;
						}
						//$arr[$j]['data'][$key]['sshj'] = $res->{'r_body'}[$i]->{"sshj"};
						$arr[$j]['data'][$key]['dizhi'] = $res->{'r_body'}[$i]->{"dizhi"};
						$arr[$j]['data'][$key]['billcode'] = $res->{'r_body'}[$i]->{"billcode"};
						$latefeenow = $res->{'r_body'}[$i]->{"latefeenow"};
						if(strpos($latefeenow,'.')==0 && $latefeenow !=0){
							$arr[$j]['data'][$key]['latefeenow'] = '0'. $latefeenow;
						}else{
							$arr[$j]['data'][$key]['latefeenow'] = $latefeenow;
						}
						$arr[$j]['data'][$key]['chargearea'] = $res->{'r_body'}[$i]->{"chargearea"};
						$arr[$j]['data'][$key]['itemcode'] = $res->{'r_body'}[$i]->{"itemcode"};
						$arr[$j]['data'][$key]['price'] = $res->{'r_body'}[$i]->{"price"};
						$arr[$j]['data'][$key]['invstatus'] = $res->{'r_body'}[$i]->{"invstatus"};
						$arr[$j]['data'][$key]['invurl'] = $res->{'r_body'}[$i]->{"invurl"};
						$arr[$j]['data'][$key]['operatedate'] = $res->{'r_body'}[$i]->{"operatedate"};
						$arr[$j]['data'][$key]['itemname'] = $res->{'r_body'}[$i]->{"itemname"};
						$arr[$j]['data'][$key]['owner'] = $res->{'r_body'}[$i]->{"owner"};
						$cash = $res->{'r_body'}[$i]->{"cash"};
						if(strpos($cash,'.')==0 && $cash !=0){
							$arr[$j]['data'][$key]['cash'] = '0'. $cash;
						}else{
							$arr[$j]['data'][$key]['cash'] = $cash;
						}
						$arr[$j]['data'][$key]['housecode'] = $res->{'r_body'}[$i]->{"housecode"};
						$arr[$j]['data'][$key]['chargemonth'] = $res->{'r_body'}[$i]->{"chargemonth"};
						$arr[$j]['data'][$key]['invoicecode'] = $res->{'r_body'}[$i]->{"invoicecode"};
						$arr[$j]['data'][$key]['invoicenumber'] = $res->{'r_body'}[$i]->{"invoicenumber"};
						$arr[$j]['data'][$key]['examineopinion'] = $res->{'r_body'}[$i]->{"examineopinion"};
						$key++;
						$arr[$j]['infor'][$key]['account'] = $key;
						$arr[$j]['infor'][$key]['billcode'] = $res->{'r_body'}[$i]->{"billcode"};
						$arr[$j]['infor'][$key]['operatedate'] = $res->{'r_body'}[$i]->{"operatedate"};
						$arr[$j]['infor'][$key]['dizhi'] = $res->{'r_body'}[$i]->{"dizhi"};
						$arr[$j]['infor'][$key]['jffs'] = $res->{'r_body'}[$i]->{"jffs"};
					}
				}
			}
		}
		//dump($arr);
		/* 分页 */
		$totalPages = ceil(count($arr) / $pagesize); //总页数
		$nowPage = $page;
		//首页
		$first_row = $nowPage;
		$first_page = $first_row > 1 ? '<a href="javascript:getPage(1)" ><li class="pagingFirst">首页</li></a>' : '';
		//末页
		$last_row = $nowPage;
		$last_page =  count($arr) > $pagesize ? '<a href="javascript:getPage(' . $totalPages . ')" ><li>末页</li></a>' : '';
		//上一页
		$up_row = $nowPage - 1;
		$up_page = $up_row > 0 ? '<a href="javascript:getPage(' . $up_row . ')" ><li>上一页</li></a>' : '';
		//下一页
		$down_row = $nowPage + 1;
		$down_page = ($down_row <= $totalPages) ? '<a href="javascript:getPage(' . $down_row . ')" ><li>下一页</li></a>' : '';
		//数字连接
		$link_page = "";
		for($m = 1; $m <= $totalPages; $m++){
		  if($page > 0 && $m != $nowPage){
			if($p <= $totalPages){
			  $link_page .= '<a  href="javascript:getPage(' . $m . ')" ><li>' . $m . '</li></a>';
			}else{
			  break;
			}
		  }else{
			if($page > 0 && $totalPages != 1){
			  $link_page .= '<li style="background:linear-gradient(#f1f5e4, #d8efa7);color:#158402;background-color:#d8efa7\9">' . $m . '</li>';
			}
		  }
		}
		$start = ($nowPage-1)*$pagesize;
		//分页数量
		$account_page = count($arr) > $pagesize ? '<li class="pagingLast">共' . count($arr) . '条</li>' : '';
		//数组截取
		$list = array_slice($arr,$start,$pagesize);
		//分页
		$fy = $first_page.$up_page.$link_page.$down_page.$last_page.$account_page;
		$this->billlist = $list;
		$this->code = $houseCode;
		$this->page = $page;
		$this->fy = $fy;
		$this->account = count($arr);
		$this->houselist = $houselist;

	    $this->assign($index);
        $this->display();

    }
	//根据年份查询月份
	public function yue() {
		$nian=$_POST['nian'];
		$url2 = 'http://10.105.15.2/TjstcWebImpl/GetDzfpPayMonthServlet';
		$post_data2 ="vYEAR=$nian";
		$ch2 = curl_init();
		curl_setopt($ch2, CURLOPT_URL, $url2);
		curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch2, CURLOPT_POST, 1);
		curl_setopt($ch2, CURLOPT_POSTFIELDS, $post_data2);
		$output2 = curl_exec($ch2);
		$yuearr=json_decode($output2,true);
		for($k = 0; $k < count($yuearr['r_body']); $k++){
			$yue[$k]['MON']=$yuearr['r_body'][$k]['MON'];
		}
		echo $this->ajaxReturn($yue,'JSON');
        exit();
	}
	 //电子表列表页
    public function bill()
    {
		//echo $_REQUEST['kssj'];
		//echo $_REQUEST['jssj'];

       $this->check();
	   $arr = array();
	   $arr1 = array();
	   $arr2 = array();
	   $pagesize = 10;
		//获取有发票的时间
		$url = "http://10.105.15.2/TjstcWebImpl/GetDzfpPayChargemonthServlet";
		$post_data ="";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		$output = curl_exec($ch);
		$sjva1=json_decode($output,true);
		for($k = 0; $k < count($sjva1['r_body']); $k++){
			$sjva[$k]['DAY_ID']=$sjva1['r_body'][$k]['DAY_ID'];
		}
		$this->sjva = $sjva;


	   //开始时间
        if (!empty($_REQUEST['kssj'])) {
			$kssj = $_REQUEST['kssj'];
            $index['kssj'] = $_REQUEST['kssj'];
        }
		else{
			 //$index['kssj'] = date("Y-m-d", strtotime("-1 month"));
			 $index['kssj'] = date("Y").'-01-01';
		}

		//结束时间
        if (!empty($_REQUEST['jssj'])) {
          $jssj = $_REQUEST['jssj'];
          $index['jssj'] = $_REQUEST['jssj'];
        }
		else{
			$index['jssj'] = date("Y-m")."-31";
		}
		$houseCode = $_REQUEST['houseCode'];
		$houselist = R('User/fjbd', array($_SESSION['uid']));
		if($_REQUEST['houseCode'] == '' || $_REQUEST['houseCode'] == NULL){
			if($_POST['hcode']!="")
			{$houseCode=$_POST['hcode'];}
			else{$houseCode = $houselist[0]['houseCode'];}
		}
		if (!empty($_REQUEST['page'])) {
           $page = $_REQUEST['page'];
        }
		else{
		   $page = 1;
		}
		
	    $url = 'http://10.105.15.2/tjsck_impl/DzfpQueryServlet';
		$post_data['vhousecode'] = $houseCode;
		$post_data['vstartdate'] = $index['kssj'];
		$post_data['venddate'] = $index['jssj'];
		$res = json_decode($this->request_post($url, $post_data));

		//订单编号处理
		for($k = 0; $k < count($res->{'r_body'}); $k++){
			$arr1[] = $res->{'r_body'}[$k]->{"billcode"};
			/*$arr2[$k]['infor']['jffs'] = $res->{'r_body'}[$k]->{"jffs"};
			$arr2[$k]['infor']['operatedate'] = $res->{'r_body'}[$k]->{"operatedate"};
			$arr2[$k]['infor']['dizhi'] = $res->{'r_body'}[$k]->{"dizhi"};*/
		}
		$result_01 = array_flip($arr1);
		$result_02 = array_flip($result_01);
		$result    = array_merge($result_02);

		if($res->{"r_code"}='0000'){
			for($j = 0; $j < count($result); $j++){
				$key = 0;

				for ($i = 0; $i < count($res->{'r_body'}); $i++) {
					if($result[$j] == $res->{'r_body'}[$i]->{"billcode"}){
						$arr[$j]['data'][$key]['jffs'] = $res->{'r_body'}[$i]->{"jffs"};
						$sshj = $res->{'r_body'}[$i]->{"sshj"};
						if(strpos($sshj,'.')==0 && $sshj !=0){
							$arr[$j]['data'][$key]['sshj'] = '0'. $sshj;
						}else{
							$arr[$j]['data'][$key]['sshj'] = $sshj;
						}
						//$arr[$j]['data'][$key]['sshj'] = $res->{'r_body'}[$i]->{"sshj"};
						$arr[$j]['data'][$key]['dizhi'] = $res->{'r_body'}[$i]->{"dizhi"};
						$arr[$j]['data'][$key]['billcode'] = $res->{'r_body'}[$i]->{"billcode"};
						$latefeenow = $res->{'r_body'}[$i]->{"latefeenow"};
						if(strpos($latefeenow,'.')==0 && $latefeenow !=0){
							$arr[$j]['data'][$key]['latefeenow'] = '0'. $latefeenow;
						}else{
							$arr[$j]['data'][$key]['latefeenow'] = $latefeenow;
						}
						$arr[$j]['data'][$key]['chargearea'] = $res->{'r_body'}[$i]->{"chargearea"};
						$arr[$j]['data'][$key]['itemcode'] = $res->{'r_body'}[$i]->{"itemcode"};
						$arr[$j]['data'][$key]['price'] = $res->{'r_body'}[$i]->{"price"};
						$arr[$j]['data'][$key]['invstatus'] = $res->{'r_body'}[$i]->{"invstatus"};
						$arr[$j]['data'][$key]['invurl'] = $res->{'r_body'}[$i]->{"invurl"};
						$arr[$j]['data'][$key]['operatedate'] = $res->{'r_body'}[$i]->{"operatedate"};
						$arr[$j]['data'][$key]['itemname'] = $res->{'r_body'}[$i]->{"itemname"};
						$arr[$j]['data'][$key]['owner'] = $res->{'r_body'}[$i]->{"owner"};
						$cash = $res->{'r_body'}[$i]->{"cash"};
						if(strpos($cash,'.')==0 && $cash !=0){
							$arr[$j]['data'][$key]['cash'] = '0'. $cash;
						}else{
							$arr[$j]['data'][$key]['cash'] = $cash;
						}
						$arr[$j]['data'][$key]['housecode'] = $res->{'r_body'}[$i]->{"housecode"};
						$arr[$j]['data'][$key]['chargemonth'] = $res->{'r_body'}[$i]->{"chargemonth"};
						$arr[$j]['data'][$key]['invoicecode'] = $res->{'r_body'}[$i]->{"invoicecode"};
						$arr[$j]['data'][$key]['invoicenumber'] = $res->{'r_body'}[$i]->{"invoicenumber"};
						$arr[$j]['data'][$key]['examineopinion'] = $res->{'r_body'}[$i]->{"examineopinion"};
						$key++;
						$arr[$j]['infor'][$key]['account'] = $key;
						$arr[$j]['infor'][$key]['billcode'] = $res->{'r_body'}[$i]->{"billcode"};
						$arr[$j]['infor'][$key]['operatedate'] = $res->{'r_body'}[$i]->{"operatedate"};
						$arr[$j]['infor'][$key]['dizhi'] = $res->{'r_body'}[$i]->{"dizhi"};
						$arr[$j]['infor'][$key]['jffs'] = $res->{'r_body'}[$i]->{"jffs"};
					}
				}
			}
		}
		//dump($arr);
		/* 分页 */
		$totalPages = ceil(count($arr) / $pagesize); //总页数
		$nowPage = $page;

		//首页
		$first_row = $nowPage;
		$first_page = $first_row > 1 ? '<a href="javascript:getPage(1)" class="pageFirst" style="border-right:0;">首页</a>' : '';
		//末页
		$last_row = $nowPage;
		$last_page =  count($arr) > $pagesize ? '<a href="javascript:getPage(' . $totalPages . ')" >末页</a>' : '';
		//上一页
		$up_row = $nowPage - 1;
		$up_page = $up_row > 0 ? '<a href="javascript:getPage(' . $up_row . ')"  style="border-right:0;">上一页</a>' : '';
		//下一页
		$down_row = $nowPage + 1;
		$down_page = ($down_row <= $totalPages) ? '<a href="javascript:getPage(' . $down_row . ')"  style="border-right:0;">下一页</a>' : '';
		//数字连接
		$link_page = "";
		for($m = 1; $m <= $totalPages; $m++){
		  if($page > 0 && $m != $nowPage){
			if($p <= $totalPages){
			  $link_page .= '<a  href="javascript:getPage(' . $m . ')" style="border-right:0;">' . $m . '</a>';
			}else{
			  break;
			}
		  }else{
			if($page > 0 && $totalPages != 1){
			  $link_page .= '<a  style="text-decoration:none;color:#fff;background-color:#f89f3d" style="border-right:0;">' . $m . '</a>';
			}
		  }
		}
		$start = ($nowPage-1)*$pagesize;
		//分页数量
		$account_page = count($arr) > $pagesize ? '<a class="pageLast">共' . count($arr) . '条</a>' : '';
		//数组截取
		$list = array_slice($arr,$start,$pagesize);
		//分页
		$fy = $first_page.$up_page.$link_page.$down_page.$last_page.$account_page;

		$this->billlist = $list;
		$this->code = $houseCode;
		$this->page = $page;
		$this->fy = $fy;
		$this->account = count($arr);
		$this->houselist = $houselist;
		   $nowyear=date("Y");
		   $nowmonth=date("m");
        $this->assign('nowyear',$nowyear);
		//echo $_REQUEST['selectAge1'];
		//echo $nowyear;
		//echo $_REQUEST['selectyue1'];
		$yearzz=isset($_REQUEST['selectAge1'])?$_REQUEST['selectAge1']:'请选择';
		$yuezz=isset($_REQUEST['selectAge2'])?$_REQUEST['selectAge2']:'请选择';
		
		$this->assign('yearzz',$yearzz);
		$this->assign('yuezz',$yuezz);

	    $this->assign('selectAge1',$_REQUEST['selectAge1']);
	    $this->assign('selectyue1', $_REQUEST['selectyue1']);
		  $this->assign('selectAge2',$_REQUEST['selectAge2']);

	    $this->assign('selectyue2', $_REQUEST['selectyue2']==""?$nowmonth:$_REQUEST['selectyue2']);

	    $this->assign($index);
        $this->display();

    }

	 //发票申请
    function applyInvioce() {
        $this->check();
		$url = 'http://10.105.15.2/tjsck_impl/InsertInvExamineServlet';
		$post_data['vbillcode'] = $_POST['billcode'];
		$post_data['vapplyinvdate'] = date('Y-m-d H:i:s',time());
		$res = json_decode($this->request_post($url, $post_data));
		//$code = $res->{"r_code"};
        echo json_encode($res);
    }

 //历史缴费页
    public function lsjf()
    {
		 
      
		   //时间条件 1：近三个月2：近半年3：一年内4：两年内
		       $this->check();
	   $arr = array();
	   $arr1 = array();
	   $arr2 = array();
	   $pagesize = 10;

	   $date=$_REQUEST['date'];
	  
	   if($date=='近三个月'||$date=='')
		{

			$enddate=date("Y-m-d");
			
			$startdate=date('Y-m',strtotime("-2 month"))."-01";
		}
		if($date=='近半年')
		{
		
            $startdate=date('Y-m',strtotime("-5 month"))."-01";
			$enddate=date("Y-m-d");
		}
		
		if($date=='一年内')
		{
           $enddate=date("Y-m-d");
			$startdate=date('Y-m',strtotime("-11 month"))."-01";
		}
		if($date=='两年内')
		{
            $startdate=date('Y-m',strtotime("-23 month"))."-01";
			$enddate=date("Y-m-d");
		}
		//房间号
		$houseCode = $_REQUEST['houseCode'];
		$houselist = R('User/fjbd', array($_SESSION['uid']));
		
		if($_REQUEST['houseCode'] == '' || $_REQUEST['houseCode'] == NULL){
			if($_POST['hcode']!="")
			{$houseCode=$_POST['hcode'];}
			else{$houseCode = $houselist[0]['houseCode'];}
		}
		if (!empty($_REQUEST['page'])) {
           $page = $_REQUEST['page'];
        }
		else{
		   $page = 1;
		}
		//缴费途径
		
    $paymentchannel='全部';
 //$paymentchannel=$_REQUEST['paymentchannel'];
	  //if($paymentchannel==''||$paymentchannel==NULL)
		//{$paymentchannel="银行批扣";}
	 
	 
	
	     $url = 'http://10.105.15.2/TjstcWebImpl/CheckHistoryPaymentRecordServlet';
	     $post_data['vHOUSECODE'] = $houseCode;
		 $post_data['vPAYMENTCHANNEL'] = $paymentchannel;
		 $post_data['vSTARTDATE'] =$startdate;
		 $post_data['vENDDATE'] =$enddate;
		 $res = json_decode($this->curl_post($url,$post_data));
	

		//订单编号处理
		for($k = 0; $k < count($res->{'r_body'}); $k++){
			$arr1[] = $res->{'r_body'}[$k]->{"billcode"};
			
		}
		$result_01 = array_flip($arr1);
		$result_02 = array_flip($result_01);
		$result    = array_merge($result_02);
		//echo count($result);

		if($res->{"r_code"}='0000'){
			for($j = 0; $j < count($result); $j++){
				$key = 0;

				for ($i = 0; $i < count($res->{'r_body'}); $i++) {
					if($result[$j] == $res->{'r_body'}[$i]->{"billcode"}){
						$arr[$j]['data'][$key]['jffs'] = $res->{'r_body'}[$i]->{"jffs"};
						$sshj = $res->{'r_body'}[$i]->{"sshj"};
						if(strpos($sshj,'.')==0 && $sshj !=0){
							$arr[$j]['data'][$key]['sshj'] = '0'. $sshj;
						}else{
							$arr[$j]['data'][$key]['sshj'] = $sshj;
						}
					
						$arr[$j]['data'][$key]['dizhi'] = $res->{'r_body'}[$i]->{"dizhi"};
						$arr[$j]['data'][$key]['billcode'] = $res->{'r_body'}[$i]->{"billcode"};
						$latefeenow = $res->{'r_body'}[$i]->{"latefeenow"};
						if(strpos($latefeenow,'.')==0 && $latefeenow !=0){
							$arr[$j]['data'][$key]['latefeenow'] = '0'. $latefeenow;
						}else{
							$arr[$j]['data'][$key]['latefeenow'] = $latefeenow;
						}
						$arr[$j]['data'][$key]['chargearea'] = $res->{'r_body'}[$i]->{"chargearea"};
						$arr[$j]['data'][$key]['itemcode'] = $res->{'r_body'}[$i]->{"itemcode"};
						$arr[$j]['data'][$key]['price'] = $res->{'r_body'}[$i]->{"price"};
						$arr[$j]['data'][$key]['invstatus'] = $res->{'r_body'}[$i]->{"invstatus"};
						$arr[$j]['data'][$key]['invurl'] = $res->{'r_body'}[$i]->{"invurl"};
						$arr[$j]['data'][$key]['operatedate'] = $res->{'r_body'}[$i]->{"operatedate"};
						$arr[$j]['data'][$key]['itemname'] = $res->{'r_body'}[$i]->{"itemname"};
						$arr[$j]['data'][$key]['owner'] = $res->{'r_body'}[$i]->{"owner"};
						$cash = $res->{'r_body'}[$i]->{"cash"};
						if(strpos($cash,'.')==0 && $cash !=0){
							$arr[$j]['data'][$key]['cash'] = '0'. $cash;
						}else{
							$arr[$j]['data'][$key]['cash'] = $cash;
						}
						$arr[$j]['data'][$key]['housecode'] = $res->{'r_body'}[$i]->{"housecode"};
						$arr[$j]['data'][$key]['chargemonth'] = $res->{'r_body'}[$i]->{"chargemonth"};
						$arr[$j]['data'][$key]['invoicecode'] = $res->{'r_body'}[$i]->{"invoicecode"};
						$arr[$j]['data'][$key]['invoicenumber'] = $res->{'r_body'}[$i]->{"invoicenumber"};
						$arr[$j]['data'][$key]['examineopinion'] = $res->{'r_body'}[$i]->{"examineopinion"};
						$key++;
						$arr[$j]['infor'][$key]['account'] = $key;
						$arr[$j]['infor'][$key]['billcode'] = $res->{'r_body'}[$i]->{"billcode"};
						$arr[$j]['infor'][$key]['operatedate'] = $res->{'r_body'}[$i]->{"operatedate"};
						$arr[$j]['infor'][$key]['dizhi'] = $res->{'r_body'}[$i]->{"dizhi"};
						$arr[$j]['infor'][$key]['jffs'] = $res->{'r_body'}[$i]->{"jffs"};
					}
				}
			}
		}
		
		//dump($arr);
		/* 分页 */
		$totalPages = ceil(count($arr) / $pagesize); //总页数
		$nowPage = $page;

		//首页
		$first_row = $nowPage;
		$first_page = $first_row > 1 ? '<a href="javascript:getPage(1)" class="pageFirst" style="border-right:0;">首页</a>' : '';
		//末页
		$last_row = $nowPage;
		$last_page =  count($arr) > $pagesize ? '<a href="javascript:getPage(' . $totalPages . ')" >末页</a>' : '';
		//上一页
		$up_row = $nowPage - 1;
		$up_page = $up_row > 0 ? '<a href="javascript:getPage(' . $up_row . ')"  style="border-right:0;">上一页</a>' : '';
		//下一页
		$down_row = $nowPage + 1;
		$down_page = ($down_row <= $totalPages) ? '<a href="javascript:getPage(' . $down_row . ')"  style="border-right:0;">下一页</a>' : '';
		//数字连接
		$link_page = "";
		for($m = 1; $m <= $totalPages; $m++){
		  if($page > 0 && $m != $nowPage){
			if($p <= $totalPages){
			  $link_page .= '<a  href="javascript:getPage(' . $m . ')" style="border-right:0;">' . $m . '</a>';
			}else{
			  break;
			}
		  }else{
			if($page > 0 && $totalPages != 1){
			  $link_page .= '<a  style="text-decoration:none;color:#fff;background-color:#f89f3d" style="border-right:0;">' . $m . '</a>';
			}
		  }
		}
		$start = ($nowPage-1)*$pagesize;
		//分页数量
		$account_page = count($arr) > $pagesize ? '<a class="pageLast">共' . count($arr) . '条</a>' : '';
		//数组截取
		$list = array_slice($arr,$start,$pagesize);
		//分页
		$fy = $first_page.$up_page.$link_page.$down_page.$last_page.$account_page;
		 $paylist=array();
		$paylist[0]="在线缴费";
		$paylist[1]="银联天津";
		$paylist[2]="建行窗口";
        $paylist[3]="能源窗口";
        $paylist[4]="银行批扣";
        $this->paylist =$paylist;
		$this->plist=$paymentchannel;
		$datelist[0]="近三个月";
	    $datelist[1]="近半年";
		$datelist[2]="一年内";
		$datelist[3]="两年内";
		$this->datelist=$datelist;
		$dlist=$_REQUEST['date'];
		  if($dlist==''||$dlist==NULL)
		{$dlist='近三个月';}

		  
		$this->dlist=$dlist;
		$this->billlist = $list;
		$this->code = $houseCode;
		$this->page = $page;
		$this->fy = $fy;
		$this->account = count($arr);
		$this->houselist = $houselist;
		
       
	
	   $this->code = $houseCode;

	    $this->assign($index);
        $this->display();
		 

    }



}