<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2016/8/23
 * Time: 15:10
 */

class PayAction extends AdminAction {

    function getRandChar($length){
        $str = null;
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol)-1;
        for($i=0;$i<$length;$i++){
            $str.=$strPol[rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
        }
        return $str;
    }


    public function index(){
        $payInfo = D('PayInfo')->where("orderNo='".$_GET['orderNo']."'")->select();
        if($payInfo){
            $merchantNo=$payInfo[0]['merchantNo'];
            $refundAmount=$payInfo[0]['payAmount'];
            $mRefundSeq = $this->getRandChar(28);
            $curCode='001';
            include_once("./MyCms/Lib/pay/boc.class.php");
            $pay = new boc("ny123456");
            /*
            * 商户签名数据串格式，各项数据用管道符分隔：
           商户号|商户退款交易流水号|退款币种|退款金额|商户订单号
           merchantNo|mRefundSeq|curCode|refundAmount|orderNo
            */
            $temp = $merchantNo."|".$mRefundSeq."|".$curCode."|".$refundAmount."|".$payInfo[0]['orderNo'];
            $signData =$pay->signFromStr($temp);
            //dump($merchantNo."|".$mRefundSeq."|".$curCode."|".$refundAmount."|".$payInfo[0]['orderNo']);
            $info['merchantNo']=$merchantNo;
            $info['mRefundSeq']=$mRefundSeq;
            $info['curCode']=$curCode;
            $info['refundAmount']=$refundAmount;
            $info['orderNo']=$payInfo[0]['orderNo'];
            $info['add_time']=strtotime(date('Y-m-d H:i:s',time()));
            if($_SESSION['is_supper_admin']){
                $info['admin_id']=1;
            }else{
                $info['admin_id']=$_SESSION['common_admin_id'];
            }
            D('PayTk')->add($info);
            $url = "https://ebspay.boc.cn/PGWPortal/RefundOrder.do";
            $params = "merchantNo=".urlencode($merchantNo)."&mRefundSeq=".urlencode($mRefundSeq)
                ."&curCode=".urlencode($curCode)."&refundAmount=".urlencode($refundAmount)."&orderNo=".urlencode($payInfo[0]['orderNo']);
            $params.="&signData=".urlencode($signData);
            //dump($params);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            $output = curl_exec($ch);
            curl_close($ch);
            $xml = simplexml_load_string($output);
            $data = json_decode(json_encode($xml),TRUE);
            $pz_status=$data["header"]["dealStatus"];
            $orderNo=$info['orderNo'];
            D('PayTk')->execute(" update ad_pay_tk set pz_status = '$pz_status' where mRefundSeq='$mRefundSeq' ");
            dump(D('PayTk')->getLastSql());
            D('PayTk')->execute(" update ad_pay_info set tk_status = '$pz_status' where orderNo='$orderNo' ");
            dump(D('PayTk')->getLastSql());
        }
        $this->redirect("Admin/User/pay");
    }

}