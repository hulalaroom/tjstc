<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2016/8/23
 * Time: 15:10
 */

class ZfAction extends HomeAction {

    //银联缴费异步
   public function notify()
    {    
        Vendor('Unionpay.acp_service');
        $logger = com\unionpay\acp\sdk\LogUtil::getLogger();
		$logger->LogInfo("receive back notify: " . com\unionpay\acp\sdk\createLinkString ( $_POST, false, true ));
        if (isset ( $_POST ['signature'] )) {	
			//echo com\unionpay\acp\sdk\AcpService::validate ( $_POST ) ? '验签成功' : '验签失败';
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
			$map['quDao']="5890";
			//判断respCode=00、A6后，对涉及资金类的交易，请再发起查询接口查询，确定交易成功后更新数据库。
			if($respCode == 00){
				$url = 'http://10.105.15.2/tjsck_impl/NewPayServlet';
				$post_data['vbankSerial'] = $_POST ['orderId'];
				$post_data['vrcvAmt'] = $_POST ['txnAmt']/100;
				$post_data['vclocknumber'] = $str[3];
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
				//file_put_contents('F:/websoft/web/phpwind/MyCms/Modules/Home/Action/log.txt',$sql);
				$lszd->query($sql);
				
				D('bill_yinlian')->add($map);
				
				$Message =  '交易成功';
			}
			else {
				$Message =  '交易失败';
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
				
			//echo com\unionpay\acp\sdk\AcpService::validate ( $_POST ) ? '验签成功' : '验签失败';
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
				
				$Message =  '交易成功';

			} else {
				$Message =  '交易失败';
			}

		} else {
			$Message =  '交易失败';
		}
		$map['Message']=$Message;
        $this->map=$map;
        $this->display();

    }

}