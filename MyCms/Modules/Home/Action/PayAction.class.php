<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2016/8/23
 * Time: 15:10
 */

class PayAction extends HomeAction {

    public function index(){
        $this->check();
        $url = 'http://10.105.15.2/tjsck_impl/QueryQianfeiServlet';
        $housecode=$_POST['housecode'];
        $type=$_POST['type'];
        $chargemonth=$_POST['chargemonth'];
        $cardTyp=$_POST['cardTyp'];
        $post_data['vhousecode'] = $housecode;
        $post_data['vchargemonth'] = $chargemonth;
        $post_data['vitemcode'] = $type;
        $res = json_decode($this->request_post($url, $post_data));
		
        $lsh=null;
        $totalmoney=null;
        if($res->{'r_body'}[0]->{"code"}='9999'){
            $lsh=$res->{'r_body'}[0]->{"LSH"};
            if(strpos($res->{'r_body'}[0]->{"TOTALMONEY"},'.')==0){
                $totalmoney='0'.$res->{'r_body'}[0]->{"TOTALMONEY"};
            }else{
                $totalmoney=$res->{'r_body'}[0]->{"TOTALMONEY"};
            }
        }else{
          $this->redirect("Public/404");
        }
		$totalmoney=number_format($totalmoney, 2,'.','');
        $merchantNo="104120049000028";
        if($cardTyp==1){
            $merchantNo="104120049000027";
        }
        $orderNote = '天然气缴费';
        if($type=='B'){
            $orderNote = '水费缴费';
        }else if($type=='A'){
            $orderNote = '采暖缴费';
        }
        $this->lsh=$lsh;
        $this->orderNote=$orderNote;
        $this->dateTime=date("YmdHis",time());
        $this->totalmoney=$totalmoney;
        $this->merchantNo=$merchantNo;
        include_once("./MyCms/Lib/pay/boc.class.php");
        $pay = new boc("ny123456");
		//orderNo|orderTime|curCode|orderAmount|merchantNo
        $unsignData = $lsh."|".date("YmdHis",time())."|001|".$totalmoney."|".$merchantNo;
        $signData = $pay->signFromStr($unsignData);
        $this->assign("signData",$signData);
        $this->display();
    }

    public function returnPay(){
        //$this->check();
        include_once("./MyCms/Lib/pay/boc.class.php");
        $pay = new boc("ny123456");
        $unsignData = $_POST['merchantNo']."|".$_POST['orderNo']."|".$_POST['orderSeq'];
        $unsignData .= "|".$_POST['cardTyp']."|".$_POST['payTime']."|".$_POST['orderStatus']."|".$_POST['payAmount'];
        $map['merchantNo']=$_POST['merchantNo'];
        $map['orderNo']=$_POST['orderNo'];
        $map['orderSeq']=$_POST['orderSeq'];
        $map['cardTyp']=$_POST['cardTyp'];
        $map['payTime']=$_POST['payTime'];
        $map['orderStatus']=$_POST['orderStatus'];
        $map['payAmount']=$_POST['payAmount'];
		$map['qudao']="5890网站";
        $Message="";
		// D('PayInfo')->add($map);
         if($pay->verifyFormStr($_POST['signData'],$unsignData)){
			 //D('PayInfo')->add($map);
			 if($map['orderStatus']==1){
				$temo=$_POST['orderNo'];
				$tem = D('PayInfo')->where("orderNo='$temo'")->select();
				//dump(D('PayInfo')->getLastSql());
				if($tem){
					//dump("11111");
					$Message=$tem[0]["platformMessage"];
				}else{
					//dump("22222");
					$url = 'http://10.105.15.2/tjsck_impl/PayServlet';
					$post_data['vbankSerial'] = $_POST['orderNo'];
					$post_data['vrcvAmt'] = $_POST['payAmount'];
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
					$lszd->execute("insert into PAY_INFO VALUES ('".$map['orderNo']."','".$map['merchantNo']."','".$map['orderSeq']."','
					".$map['cardTyp']."','".$map['payTime']."','".$map['orderStatus']."',".$map['payAmount'].",'".$map['platformStatus']."','".$map['platformMessage']."','5890网站')");
					D('PayInfo')->add($map);
            }
			
			 }
			 
        }else{
            $Message="交易失败";
            $map['platformMessage']=$pay->dnData;
            D('PayInfo')->add($map);
        }
        $map['Message']=$Message;
        $this->map=$map;
        $this->display();
    }


	public function returnPay1(){
        //$this->check();
        include_once("./MyCms/Lib/pay/boc.class.php");
        $pay = new boc("ny123456");
        $unsignData = $_POST['merchantNo']."|".$_POST['orderNo']."|".$_POST['orderSeq'];
        $unsignData .= "|".$_POST['cardTyp']."|".$_POST['payTime']."|".$_POST['orderStatus']."|".$_POST['payAmount'];
        $map['merchantNo']=$_POST['merchantNo'];
        $map['orderNo']=$_POST['orderNo'];
        $map['orderSeq']=$_POST['orderSeq'];
        $map['cardTyp']=$_POST['cardTyp'];
        $map['payTime']=$_POST['payTime'];
        $map['orderStatus']=$_POST['orderStatus'];
        $map['payAmount']=$_POST['payAmount'];
		$map['qudao']="5890网站";
        $Message="";
		// D('PayInfo')->add($map);
         if($pay->verifyFormStr($_POST['signData'],$unsignData)){
			 //D('PayInfo')->add($map);
			 if($map['orderStatus']==1){
				$temo=$_POST['orderNo'];
				$tem = D('PayInfo')->where("orderNo='$temo'")->select();
				//dump(D('PayInfo')->getLastSql());
				if($tem){
					//dump("11111");
					$Message=$tem[0]["platformMessage"];
				}else{
					//dump("22222");
					$url = 'http://10.105.15.2/tjsck_impl/PayServlet';
					$post_data['vbankSerial'] = $_POST['orderNo'];
					$post_data['vrcvAmt'] = $_POST['payAmount'];
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
					$lszd->execute("insert into PAY_INFO VALUES ('".$map['orderNo']."','".$map['merchantNo']."','".$map['orderSeq']."','
					".$map['cardTyp']."','".$map['payTime']."','".$map['orderStatus']."',".$map['payAmount'].",'".$map['platformStatus']."','".$map['platformMessage']."','5890网站')");
					D('PayInfo')->add($map);
            }
			
			 }
			 
        }else{
            $Message="交易失败";
            $map['platformMessage']=$pay->dnData;
            D('PayInfo')->add($map);
        }
        $map['Message']=$Message;
        $this->map=$map;
        $this->display();
    }



    public function returnMovePay(){
        include_once("./MyCms/Lib/pay/boc.class.php");
        $pay = new boc("ny123456");
        $unsignData = $_POST['merchantNo']."|".$_POST['orderNo']."|".$_POST['orderSeq'];
        $unsignData .= "|".$_POST['cardTyp']."|".$_POST['payTime']."|".$_POST['orderStatus']."|".$_POST['payAmount'];
        $map['merchantNo']=$_POST['merchantNo'];
        $map['orderNo']=$_POST['orderNo'];
        $map['orderSeq']=$_POST['orderSeq'];
        $map['cardTyp']=$_POST['cardTyp'];
        $map['payTime']=$_POST['payTime'];
        $map['orderStatus']=$_POST['orderStatus'];
        $map['payAmount']=$_POST['payAmount'];
		$map['qudao']="app";
        $Message="";
        if($pay->verifyFormStr($_POST['signData'],$unsignData)){
			if($map['orderStatus']==1){
				$temo=$_POST['orderNo'];
				$tem = D('PayInfo')->where("orderNo='$temo'")->select();
				if($tem){
					$Message=$tem[0]["platformMessage"];
				}else{
					$url = 'http://10.105.15.2/tjsck_impl/PayServlet';
					$post_data['vbankSerial'] = $_POST['orderNo'];
					$post_data['vrcvAmt'] = $_POST['payAmount'];
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
					$lszd->execute("insert into PAY_INFO VALUES ('".$map['orderNo']."','".$map['merchantNo']."','".$map['orderSeq']."','
					".$map['cardTyp']."','".$map['payTime']."','".$map['orderStatus']."',".$map['payAmount'].",'".$map['platformStatus']."','".$map['platformMessage']."','app')");
					D('PayInfo')->add($map);
					}
				}
            
        }else{
            $Message="交易失败";
            $map['platformMessage']=$pay->dnData;
            D('PayInfo')->add($map);
        }
        $map['Message']=$Message;
        $this->map=$map;
        $this->display();
    }

}