<?php

class ApiAction extends AdminAction {
    /* 2.2.1	用户绑定查询 */

    //查询用户需要绑定的房间列表
    function getBindHouList($ownerName, $paperCode, $bankNumber) {
        try {
            $wsdl = "http://".C('api_url')."/tjstc_ggsykf_webs/bindUser?wsdl";
            if (check_url_status($wsdl) == false) {
                $data['error'] = 1;
                return($data);
            }

            $client = new SoapClient($wsdl);
            //传入的参数
            $a = array(
                //认证用户
                'appId' => 'tjstc_fwmh',
                //认证密码
                'appPass' => '789654',
                //用户姓名
                'ownerName' => $ownerName,
                //用户身份证号
                'paperCode' => $paperCode,
                //用户银行卡号
                'bankNumber' => $bankNumber,
            );
            $ret = $client->getBindHouList($a);
        } catch (SoapFault $e) {
            $data['error'] = 1;
            return($data);
        }
        $r = json_decode($ret->return);
        if ($r[0] == '00') {
            return($r[2]);
        }
    }

    /* 2.2.2	房间信息查询 */

    function getHouse($houseCode) {
        $url = C('DB_ORACLE');
        $lszd = M('',null,$url);
        $list = $lszd->query("select distinct houseCode, ownerName,case when length(paperCode) = 18 then substr(paperCode,1,6)||'********'||substr(paperCode,15,3)||'*' when length(paperCode) = 15 then substr(paperCode,1,5)||'********'||substr(paperCode,15,1)
 else '' end   paperCode, workUnit,enterDate,address,contactMan,contactPhone,bank, case when bankNumber is not null then substr(bankNumber,1,4)||'********'||SUBSTR(bankNumber,13,4) else '' end bankNumber,
compact_id, hChargeArea,cChargeArea,hStandardName,cStandardName,communityname||buildingname||cellcode||'单元'||floor||'层'||doorplatecode||'室' as AADDRESS  from view_house_qi where houseCode = '$houseCode'");
        return $list[0];
    }

    /* 2.2.3	历史费用查询 */

    function getChargeHistory($houseCode, $itemCode, $startTime, $endTime) {
        try {
            $wsdl = "http://".C('api_url')."/tjstc_ggsykf_webs/queryOnline?wsdl";
            if (check_url_status($wsdl) == false) {
                $data['error'] = 1;
                return($data);
            }
            $client = new SoapClient($wsdl);
            //传入的参数
            $a = array(
                //认证用户
                'appId' => 'tjstc_fwmh',
                //认证密码
                'appPass' => '789654',
                //房间编号
                'houseCode' => $houseCode,
                //能源类型编号
                'itemCode' => $itemCode,
                //开始时间
                'startTime' => $startTime,
                //截至时间
                'endTime' => $endTime,
            );
            $ret = $client->getChargeHistory($a);
        } catch (SoapFault $e) {
            $data['error'] = 1;
            return($data);
        }
        $r = json_decode($ret->return);
        if ($r[0] == '00') {
            return($r[2]); //数组内的内容输出
        }
    }

    /* 2.2.4	历史用能查询 */

    function getPowerHistory($houseCode) {
        $url = C('DB_ORACLE');
		$lszd = M('',null,$url);

        $sql = "select rownum as num,a.houseCode,a.chargeMonth,a.clockNumber,c.CURRENT_MIU_SN,
b.itemName,a.pointNumber,pointDate,nvl(d.totalaccount-d.minusmoney-d.delzero,0) as cash, 
fc.communityname||fb.buildingname||fh.cellcode||'单元'||fh.floor||'层'||fh.doorplatecode||'室' as address
from sf_point a
inner join  fc_house fh on a.housecode=fh.housecode 
inner join  fc_building fb on fh.buildingcode=fb.buildingcode 
inner join  fc_community fc on fh.communitycode=fc.communitycode
left join sf_item b on b.itemCode = a.itemCode
left join fc_clock c on c.houseCode = a.houseCode and c.clockNumber = a.clockNumber
left join sf_charge d  on a.housecode=d.housecode and a.itemcode=d.itemcode and a.chargemonth=d.chargemonth 
and a.clocknumber=d.clocknumber ";
        if(!empty($houseCode)){
            $sql .= "where a.houseCode in ($houseCode)";
        }
        $sql .= "  order by address desc , a.chargeMonth desc";
		$list = $lszd->query($sql);
		return $list;
    }

    /* 2.2.5	欠费查询 */

    function getOweCharge($houseCode) {
        $url = C('DB_ORACLE');
		$user = M('',null,$url);
		$housecode = $houseCode;
		$list = $user->query("SELECT * from view_house_lsfy WHERE  houseCode ='$housecode' ");
		return $list;
    }

    /* 2.2.6	曲线图查询 */

    function getHisChar($houseCode, $itemCode, $chargeMonth, $buss) {
        try {
            $wsdl = "http://".C('api_url')."/tjstc_ggsykf_webs/queryChar?wsdl";
            if (check_url_status($wsdl) == false) {
                $data['error'] = 1;
                return($data);
            }
            $client = new SoapClient($wsdl);
            //传入的参数
            $a = array(
                //认证用户
                'appId' => 'tjstc_fwmh',
                //认证密码
                'appPass' => '789654',
                //房间编号
                'houseCode' => $houseCode,
                //项目编号。A采暖  B自来水 C燃气
                'itemCode' => $itemCode,
                //查询年份   2013
                'chargeMonth' => $chargeMonth,
                //标识   0历史费用  1历史用能
                'buss' => $buss,
            );
            $ret = $client->getHisChar($a);
        } catch (SoapFault $e) {
            $data['error'] = 1;
            return($data);
        }
        $r = json_decode($ret->return);
//        dump($r[2]);
        if ($r[0] == '00') {
            return($r[2]); //数组内的内容输出
        }
    }

    /* 2.2.7	能源类型 */

    function getPowerType() {
        try {
            $wsdl = "http://".C('api_url')."/tjstc_ggsykf_webs/queryOnline?wsdl";
            if (check_url_status($wsdl) == false) {
                $data['error'] = 1;
                return($data);
            }
            $client = new SoapClient($wsdl);
            //传入的参数
            $a = array(
                //认证用户
                'appId' => 'tjstc_fwmh',
                //认证密码
                'appPass' => '789654',
            );
            $ret = $client->getPowerType($a);
        } catch (SoapFault $e) {
            $data['error'] = 1;
            return($data);
        }
        $r = json_decode($ret->return);
//        dump($r[2]);
        if ($r[0] == '00') {
            return($r[2]); //数组内的内容输出
        }
    }
    
     /* 2.2.8	房间信息查询 */

    //查询一个房间的基础信息
    function getHouseAdress($houseCode) {
        try {
            $wsdl = "http://".C('api_url')."/tjstc_ggsykf_webs/queryOnline?wsdl";
            if (check_url_status($wsdl) == false) {
                $data['error'] = 1;
                return($data);
            }
            $client = new SoapClient($wsdl);
			
            //传入的参数
            $a = array(
                //认证用户
                'appId' => 'tjstc_fwmh',
                //认证密码
                'appPass' => '789654',
                //房间编号
                'houseCode' => $houseCode,
            );
            $ret = $client->getHouse($a);
			
        } catch (SoapFault $e) {
            $data['error'] = 1;
            return($data);
        }
        $r = json_decode($ret->return);
		
        if ($r[0] == '00') {
            return($r[2][0]); //数组内的内容输出
        }
    }
    
    
     /* 2.2.9	基础信息保存、新数据读取 */
	
	//指定房间的基础信息保存、新数据读取
    function saveNewHouse($houseCode, $LinkMan, $OldLinkMan, $LinkTel, $OldLinkTel, $WorkUnit, $OldWorkUnit, $MailingAddress, $OldMailingAddress) {
        try {
            $wsdl = "http://".C('api_url')."/tjstc_ggsykf_webs/saveHouseDetail?wsdl";
            if (check_url_status($wsdl) == false) {
                $data['error'] = 1;
                return($data);
            }
            $client = new SoapClient($wsdl);
			
            //传入的参数
            $a = array(
                //认证用户
                'appId' => 'tjstc_fwmh',
                //认证密码
                'appPass' => '789654',
                //房间编号
                'houseCode' => $houseCode,
				//新改联系人
                'LinkMan' => $LinkMan,
				//未改联系人
                'OldLinkMan' => $OldLinkMan,
				//新改联系电话
                'LinkTel' => $LinkTel,
				//未改联系电话
                'OldLinkTel' => $OldLinkTel,
				//新改工作单位
                'WorkUnit' => $WorkUnit,
				//未改工作单位
                'OldWorkUnit' => $OldWorkUnit,
				//新改邮件地址
                'MailingAddress' => $MailingAddress,
				//未改邮寄地址
                'OldMailingAddress' => $OldMailingAddress,
            );
            $ret = $client->saveNewHouse($a);
			
        } catch (SoapFault $e) {
            $data['error'] = 1;
            return($data);
        }
        $r = json_decode($ret->return);
		
		if ($r[0] == '00') {
            return($r[2][0]); //数组内的内容输出
        }
		
    }
    /* 1.1.1    1.1.1   验证接口 */

    //1.1.1 验证接口
    function checkRoom($houseCode, $itemCode, $username, $typeCode) {
         try {
            $wsdl = "http://".C('api_url')."/tjstc_ggsykf_webs/checkRoom?wsdl";
            if (check_url_status($wsdl) == false) {
                $data['error'] = 1;
                return($data);
            }
            $client = new SoapClient($wsdl);
            //传入的参数
            $a = array(
               //认证用户
                'appId' => 'tjstc_fwmh',
                //认证密码
                'appPass' => '789654',
                //房间编号
                'houseCode' => $houseCode,
                //能源类型
                'itemCode' => $itemCode,
                //用户名
                'username' => $username,
                //类型编号 1：开通服务，0：停供服务
                 'typeCode' => $typeCode,
            );
            $ret = $client->getCheckRoom($a);
        } catch (SoapFault $e) {
            $data['error'] = 1;
            return($data);
        }
        $r = json_decode($ret->return);    
        return $r;   
        // if ($r[0] == '00') {
        //     return($r[2]); //数组内的内容输出
        // }

    }

	 /* 2.2.10	费用明细按日期和房间查询 */
	
	//费用明细按日期和房间查询
    function getChargeInforsByHouse($houseCode, $chargeMonth, $itemCode = 'B', $appId = 'tjstc_fwmh', $appPass = '789654') {
        try {
            $wsdl = C('api_get_charge_url')."/queryAll?wsdl";
            if (check_url_status($wsdl) == false) {
                $data['error'] = 1;
                return($data);
            }
            $client = new SoapClient($wsdl);
            //传入的参数
            $a = array(
                //认证用户
                'appId' => $appId,
                //认证密码
                'appPass' => $appPass,
                //房间编号
                'houseCode' => $houseCode,

                'statTime' => $chargeMonth,
                //能源类型
                'itemCode' => $itemCode
            );
            $data = $client->getIqueryAll($a);
        } catch (SoapFault $e) {
            $data['error'] = 1;
            $data = json_encode($data);
        }
        return $data;
    }
	
	/* 2.2.10	费用明细曲线图查询 */
	
	//费用明细曲线图查询
    function getLineChartDatasByHouse($houseCode, $chargeMonth, $itemCode, $appId = 'tjstc_fwmh', $appPass = '789654') {
        try {
            if (null == $itemCode) {
                $data['error'] = 1;
                return($data);
            }

            $wsdl = C('api_get_charge_url')."/queryLine?wsdl";
            if (check_url_status($wsdl) == false) {
                $data['error'] = 1;
                return($data);
            }
            $client = new SoapClient($wsdl);
            //传入的参数
            $a = array(
                //认证用户
                'appId' => $appId,
                //认证密码
                'appPass' => $appPass,
                //房间编号
                'houseCode' => $houseCode,

                'statTime' => $chargeMonth,
                //能源类型
                'itemCode' => $itemCode
            );
            $data = $client->getIqueryLine($a);
        } catch (SoapFault $e) {
            $data['error'] = 1;
            $data = json_encode($data);
        }
        return $data;
    }

	 /* 2.2.11	用户-用户查询 */
	
    function checkUser($zhuce,$username,$nickname,$email,$phone,$bangding,$yezhumingcheng, $lianxidianhua, $shenfenzhenghao, $nengyuankahao, $fangjiandizhi, $daijiaoyinhang, $qishimianji, $jieshumianji, $qishishijian, $jieshushijian) {

		$url = C('DB_ORACLE');
		$yhcx = M('',null,$url);
        $sql = "select x.HOUSECODE from view_house_qi x";
		$sql .="  where   1=1   ";
		//业主名称 
		if(!empty($yezhumingcheng)  && null != $yezhumingcheng){
			
			$sql .= "  and  x.OWNERNAME    like  '%$yezhumingcheng' ";
		}

		//联系电话
		if(!empty($lianxidianhua)){
			$sql .= "  and  x.mobilephone    like  '%$lianxidianhua' ";
		}

		//身份证号 
		if(!empty($shenfenzhenghao)){
			$sql .= "  and  x.PAPERCODE   like  '%$shenfenzhenghao' ";
		}

		//能源卡号
		if(!empty($nengyuankahao)){
			$sql .= "  and  x.BANKNUMBER   like  '%$nengyuankahao' ";
		}

		//代缴银行
		if(!empty($daijiaoyinhang)){
			$sql .= "  and  x.BANK   like  '%$daijiaoyinhang' ";
		}

		//起始面积
		if(!empty($qishimianji)){
			$sql .= "  and  x.CCHARGEAREA  >=$qishimianji ";
		}

		//结束面积
		if(!empty($jieshumianji)){
			$sql .= "  and  x.CCHARGEAREA   <=$jieshumianji ";
		}

		//起始入住时间
		if(!empty($qishishijian)){
			$sql .= "  and  x.ENTERDATE   >='$qishishijian' ";
		}

		//结束入住时间
		if(!empty($jieshushijian)){
			$sql .= "  and  x.ENTERDATE   <='$jieshushijian' ";
		}

		//房间地址
		if(!empty($fangjiandizhi)){
			$sql .=" and  ( x.communityname||x.buildingname||x.cellcode||'单元'||floor||'层'||x.doorplatecode||'室' )like  '%$fangjiandizhi' ";
		}
		
		$list = $yhcx->query($sql);
		return $list;
        
		
    }

	 /* 2.2.12	平台用户筛选 */
	
     function chooseUser($ownername, $mobilephone, $papercode, $bankcode, $village, $building, $usetype, $qishimianji, $jieshumianji, $qishishijian, $jieshushijian) {
		$url = C('DB_ORACLE');
        $lszd = M('',null,$url);
		$sql = "select  distinct  a.housecode, b.ownername,c.communityname,d.buildingname ,c.communityname||d.buildingname||a.cellcode||'单元'||a.floor||'层'||a.doorplatecode||'室'  as dz,a.usetype,b.papercode,b.banknumber,b.mobilephone,e.chargearea,b.enterdate from fc_house  a, fc_owner  b  ,fc_community c, fc_building d,fc_chargearea e  where   1=1   and  a.housecode=b.housecode and a.communitycode=c.communitycode and a.buildingcode=d.buildingcode and a.housecode=e.housecode and  e.itemcode='A' and b.isowner='1'";
		//姓名 
		if(!empty($ownername)  && null!= $ownername){
			$sql .= " and  b.OWNERNAME like '%$ownername%' ";
		}

		//联系电话
		if(!empty($mobilephone)  && null!=$mobilephone){
			$sql .= "  and  b.mobilephone   like '%$mobilephone%'";
		}

		//身份证号 
		if(!empty($papercode)  && null!=$papercode){
			$sql .= "  and  b.PAPERCODE  like  '%$papercode%' ";
		}

		//能源卡号
		if(!empty($bankcode)  && null!=$bankcode){
			$sql .= "  and  b.BANKNUMBER  like '%$bankcode%' ";
		}

		//用户类型
		if(!empty($usetype)  && null!=$usetype){
			$sql .= "  and  a.usetype='$usetype' ";
		}

		//起始面积
		if(!empty($qishimianji)  && null!=$qishimianji){
			$sql .= "  and  e.chargearea >='$qishimianji' ";
		}

		//结束面积
		if(!empty($jieshumianji)  && null!=$jieshumianji){
			$sql .= "  and  e.chargearea <='$jieshumianji' ";
		}

		//起始入住时间
		if(!empty($qishishijian)  && null!=$qishishijian){
			$sql .= "  and  b.ENTERDATE >='$qishishijian' ";
		}

		//结束入住时间
		if(!empty($jieshushijian)  && null!=$jieshushijian){
			$sql .= "  and  b.ENTERDATE <='$jieshushijian' ";
		}

		//小区
		if(!empty($village)  && null!=$village){
			$sql .= "  and  a.COMMUNITYCODE ='$village' ";
		}

		//大楼
		if(!empty($building)  && null!=$building){
			$sql .= "  and  a.BUILDINGCODE ='$building' ";
		}
        $list = $lszd->query($sql);
        return $list;
		
    }

	/* 2.2.13	平台用户详细信息查询 */

    //查询一个房间的基础信息(初步判断可删除)
    function getInfor($id) {
        try {
            $wsdl = "http://".C('api_url')."/tjstc_ggsykf_webs/queryOnline?wsdl";
            if (check_url_status($wsdl) == false) {
                $data['error'] = 1;
                return($data);
            }
            $client = new SoapClient($wsdl);
            //传入的参数
            $a = array(
                //认证用户
                'appId' => 'tjstc_fwmh',
                //认证密码
                'appPass' => '789654',
                //房间编号
                'id' => $id,
            );
            $ret = $client->getInfor($a);
        } catch (SoapFault $e) {
            $data['error'] = 1;
            return($data);
        }
        $r = json_decode($ret->return);
        if ($r[0] == '00') {
            return($r[2][0]); //数组内的内容输出
        }
    }

	/* 2.2.14	行业类型 */
	
    function getIndustryInfos($type, $value) {
        $url = C('api_get_industry_url').'/'.$type;
        if (check_url_status($url) == false) {
            $data['error'] = 1;
            return($data);
        }

        $param = array();
        if ('ItemCodeServletTwo' == $type) {
            $param['sjhy'] = $value;
        }

        $data = http_build_query($param);
        $opts = array (
            'http' => array (
                'method' => 'POST',
                'header'=> "Content-type: application/x-www-form-urlencoded\r\n" .
                    "Content-Length: " . strlen($data) . "\r\n",
                'content' => $data
            )
        );

        try {
            $context = stream_context_create($opts);
            $ret['ret'] = file_get_contents($url, false, $context);
            $ret['error'] = 0;
        } catch(Exception $e) {
            $ret['error'] = 1;
        }
        return($ret);
    }

	/* 2.2.15 获取小区名称 */
	function getVillage() {
        $url = C('DB_ORACLE');
        $lszd = M('',null,$url);
        $list = $lszd->query("select a.communitycode,a.communityname from fc_community a");
        return $list;
    }

	/* 2.2.16 获取楼号 */
	function getBuilding($communitycode) {
        $url = C('DB_ORACLE');
        $lszd = M('',null,$url);
        $list = $lszd->query("select b.buildingcode,b.buildingname from fc_building b  where b.communitycode='$communitycode'");
        return $list;
    }

	/* 2.2.17	用户密码更正-更新ORACLE */

    function passUpdate($user_id,$password) {
        $url = C('DB_ORACLE');
		$user = M('',null,$url);
		$sql = "update MHNETUSER set PASSWORD = '$password' where ID = '$user_id'";
		$res = $user->query($sql);
		return $res;
    }

	/* 2.2.18	用户删除-删除ORACLE */

    function userDelete($id) {
        $url = C('DB_ORACLE');
		$user = M('',null,$url);
		$sql = "DELETE FROM MHNETUSER WHERE ID = '$id'";
		$res = $user->query($sql);
		return $res;
    }
}
?>
