<?php

class ApiAction extends HomeAction
{
    /* 2.2.1	用户绑定查询 */

    //查询用户需要绑定的房间列表
    function getBindHouseList($ownerName, $paperCode, $bankNumber)
    {
        $url = C('DB_ORACLE');
        $lszd = M('', null, $url);
        $sql = "select distinct houseCode,communityname||buildingname||cellcode||'单元'||floor||'层'||doorplatecode||'室' as Address  from view_house
					  where
            ownerName='$ownerName'";
        if (!empty($paperCode)) {
            $sql .= "and paperCode='$paperCode'";
        }
        if (!empty($bankNumber)) {
            $sql .= "and bankNumber='$bankNumber'";
        }
        $sql .= "order by houseCode";
        $list = $lszd->query($sql);
        return $list;
    }


    /* 2.2.2	房间信息查询 */

    //查询一个房间的基础信息
    function getHouse($houseCode)
    {
        $url = C('DB_ORACLE');
        $lszd = M('', null, $url);
        $list = $lszd->query("select distinct houseCode, ownerName,case when length(paperCode) = 18 then substr(paperCode,1,6)||'********'||substr(paperCode,15,3)||'*' when length(paperCode) = 15 then substr(paperCode,1,5)||'********'||substr(paperCode,15,1)
 else '' end   paperCode, workUnit,enterDate,address,contactMan,contactPhone,bank, case when bankNumber is not null then substr(bankNumber,1,4)||'********'||SUBSTR(bankNumber,13,4) else '' end bankNumber,
compact_id, hChargeArea,cChargeArea,hStandardName,MOBILEPHONE,cStandardName,communityname||buildingname||cellcode||'单元'||floor||'层'||doorplatecode||'室' as AADDRESS  from view_house_qi where houseCode = '$houseCode'");
        return $list[0];

    }

    /* 2.2.3	房间信息查询表读数(水/气) */

    //查询一个房间的表读数
    function getNumber($houseCode)
    {
		$sj = (date('Y')-1).'12';
        $url = C('DB_ORACLE');
        $lszd = M('', null, $url);
        $list = $lszd->query("SELECT HOUSECODE,WM_CONCAT(CASE WHEN ITEMCODE='B' THEN TO_CHAR(DECODE(NOWPOINTNUMBER,NULL,BEGINREADING,NOWPOINTNUMBER),'9999990.99') ELSE '' END) AS ZLSDS,WM_CONCAT(CASE WHEN ITEMCODE='D' then to_char(decode(nowpointnumber,null,BEGINREADING,nowpointnumber),'9999990.99') else '' end) as TRQDS from (SELECT FC.HOUSECODE,FC.ITEMCODE,FC.CLOCKNUMBER,FC.BEGINREADING,fp.chargemonth,fp.nowpointnumber FROM FC_CLOCK FC left join (select * from        (SELECT a.housecode,a.itemcode,a.clocknumber,a.chargemonth,a.nowpointnumber,RANK()OVER(PARTITION BY ITEMCODE ORDER BY CHARGEMONTH DESC) MM FROM SF_POINT a
WHERE HOUSECODE='$houseCode' AND CHARGEMONTH<='$sj' AND ITEMCODE IN ('B','D')) WHERE MM=1) FP ON FC.HOUSECODE=FP.HOUSECODE AND FC.ITEMCODE=FP.ITEMCODE AND FC.CLOCKNUMBER=FP.CLOCKNUMBER where FC.HOUSECODE='$houseCode' AND FC.ITEMCODE IN ('B','D'))GROUP BY HOUSECODE");
        return $list[0];

    }

	/* 2.2.4	房间信息查询表读数(供暖) */

    //查询一个房间的表读数
    function getHotNumber($houseCode)
    {
        $url = C('DB_ORACLE');
        $lszd = M('', null, $url);
        $list = $lszd->query("select t.nowreading from FC_CLOCK t where t.itemcode='E' and t.iserro='1' and housecode='$houseCode'");
        return $list;

    }

	/* 2.2.4	房间信息查询表读数(供暖) */

    //查询一个房间的表读数
    function getHotNumber1($houseCode)
    {
        $url = C('DB_ORACLE');
        $lszd = M('', null, $url);
		$year = date('Y');
		$strtime = date('Y')."-6-1 00:00:00";
		if(strtotime(date('Y-m-d H:i:s'))<=strtotime($strtime)){
			$year = date('Y')-1;
		}
       // $list = $lszd->query("select t.nowreading from FC_CLOCK t where t.itemcode='E' and t.iserro='1' and housecode='$houseCode'");
	    $list = $lszd->query("select
							C.HOUSECODE,C.CLOCKNUMBER,
							CASE WHEN AB.LAST_POINTNUMBER IS NULL THEN TO_CHAR(NVL(C.NOWREADING,'')) ELSE TO_CHAR(AB.LAST_POINTNUMBER) END AS RJLCSS,--初始数
							case when AB.pointnumber is null then '' else to_char(AB.pointnumber) end as RJLZZS--终止数
							from FC_CLOCK c
							left join
							(select B.* from FC_CLOCK a
							INNER JOIN JL_POINT_TOTAL_FINAL B
							on a.HOUSECODE=B.HOUSECODE and a.ITEMCODE=B.ITEMCODE and a.CLOCKNUMBER=B.CLOCKNUMBER and B.CHARGEMONTH='2017' and B.ITEMCODE='E'
							where a.HOUSECODE='$houseCode') AB on
							C.HOUSECODE=AB.HOUSECODE AND C.ITEMCODE=AB.ITEMCODE AND C.CLOCKNUMBER=AB.CLOCKNUMBER
							where C.HOUSECODE='$houseCode' and C.ITEMCODE='E' and C.ISERRO='1'");
        return $list;

    }

	/* 2.2.5	房间信息查询当前表读数(供暖) */

    //查询一个房间的表读数
    function getHotNowNumber($houseCode)
    {
        $url = C('DB_ORACLE');
        $lszd = M('', null, $url);
        $list = $lszd->query("select
							aa.CURRENT_MIU_SN,bb.DATA_DATE,bb.energy
							from (
							select a.HOUSECODE,a.CURRENT_MIU_SN,max(B.DATA_DATE) as DATA_DATE

							from FC_CLOCK a
							inner join JL_HEAT_POINTER_HISTORY B on a.current_miu_sn=b.miu_sn
							where a.HOUSECODE='$houseCode' and a.ITEMCODE='E' and a.ISERRO='1'
							group by a.HOUSECODE,a.CURRENT_MIU_SN
							) AA
							inner join JL_HEAT_POINTER_HISTORY bb on aa.current_miu_sn=bb.miu_sn and aa.DATA_DATE=bb.DATA_DATE");
        return $list[0];

    }

//查询一个房间的基础信息
    function getHouse1($houseCode)
    {
        $url = C('DB_ORACLE');
        $lszd = M('', null, $url);
        $list = $lszd->query("select distinct houseCode, ownerName,case when length(paperCode) = 18 then substr(paperCode,1,6)||'********'||substr(paperCode,15,3)||'*' when length(paperCode) = 15 then substr(paperCode,1,5)||'********'||substr(paperCode,15,1)
							else '' end   paperCode, workUnit,enterDate,address,contactMan,contactPhone,bank, case when bankNumber is not null then substr(bankNumber,1,4)||'********'||SUBSTR(bankNumber,13,4) else '' end bankNumber,
							compact_id, hChargeArea,cChargeArea,hStandardName,cStandardName,communityname||buildingname||cellcode||'单元'||floor||'层'||doorplatecode||'室' as AADDRESS  from view_house_qi where houseCode = '$houseCode'");
        return $list[0];
    }


    function getOracleHouse($houseCode)
    {
        $url = C('DB_ORACLE');
        $lszd = M('', null, $url);
        $list = $lszd->query("select distinct houseCode, ownerName,case when length(paperCode) = 18 then substr(paperCode,1,6)||'*****'||substr(paperCode,15,3)||'*' when length(paperCode) = 15 then substr(paperCode,1,5)||'*****'||substr(paperCode,15,1) else '' end  paperCode,paperCode as allpaperCode,
							workUnit,enterDate,address,contactMan,mobilephone,bank,case when bankNumber is not null then substr(bankNumber,1,4)||'*****'||SUBSTR(bankNumber,13,4) else '' end bankNumber,bankNumber as allbankNumber, compact_id,
							hChargeArea,cChargeArea,hStandardName,cStandardName,communityname,buildingname,cellcode,floor,doorplatecode FROM VIEW_HOUSE_QI WHERE HOUSECODE = '$houseCode'");
        return $list[0];
    }

    function getOracleFee($houseCode)
    {
        $url = C('DB_ORACLE');
        $lszd = M('', null, $url);
        $list = $lszd->query("SELECT CHARGEMONTH,COMMUNITYCODE,BUILDINGCODE,HOUSECODE,TOTALACCOUNT
							from sf_charge where itemcode='A' and chargemonth='2015' and COMMUNITYCODE not in ('8888') and housecode = '$houseCode'");
        return $list[0];
    }

    /* 2.2.3	历史费用查询 */

    function getChargeHistory($houseCode, $itemCode, $startTime, $endTime)
    {
        $url = C('DB_ORACLE');
        $lszd = M('', null, $url);
        $list = $lszd->query("select a.houseCode,
								a.chargeMonth,
								b.itemName,
								a.clockNumber,
								a.chargeArea,
								a.chargePrice,
								a.totalAccount,
								a.latefeeTotal,
								a.cash + a.delZero + a.addImprest cash,
								a.latefeeNow,
								c.imprestType,
								b.operateDate,
								case
								when a.rollbackFlag = '0' then
									'未冲账'
								else
									'已冲账'
								end chargeState,
								fc.communityname || fb.buildingname || fh.cellcode || '单元' ||
								fh.floor || '层' || fh.doorplatecode || '室' as address
								from sf_detail a
								inner join fc_house fh
								on a.housecode = fh.housecode
								inner join fc_building fb
								on fh.buildingcode = fb.buildingcode
								inner join fc_community fc
								on fh.communitycode = fc.communitycode
								left join sf_item b
								on b.itemCode = a.itemCode
								left join sf_bill c
								on c.billCode = a.billCode
								where a.houseCode in ('5010-031-01-05-03', '5006-005-01-14-04','1001-002-01-14-03')
								and a.itemCode = '$itemCode'
								order by address , chargemonth desc");
        //dump($lszd->getLastSql());
        return $list;
    }

    /* 2.2.4	历史用能查询(初步判断可清理) */

    function getPowerHistory($houseCode, $itemCode, $startMonth, $endMonth)
    {
        try {
            $wsdl = "http://" . C('api_url') . "/tjstc_ggsykf_webs/queryOnline?wsdl";
            if (check_url_status($wsdl) == false) {
                $data['error'] = 1;
                return ($data);
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
                'startMonth' => $startMonth,
                //截至时间
                'endMonth' => $endMonth,
            );
            $ret = $client->getPowerHistory($a);
        } catch (SoapFault $e) {
            $data['error'] = 1;
            return ($data);
        }
        $r = json_decode($ret->return);
        if ($r[0] == '00') {
            return ($r[2]); //数组内的内容输出
        }
    }

    /* 2.2.5	账单明细-当期账单查询 */

    function getOweCharge($houseCode)
    {
        $url = C('DB_ORACLE');
        $user = M('', null, $url);
        $housecode = $houseCode;
        $list = $user->query("SELECT * from view_house_lsfy WHERE  houseCode ='$housecode' ");
        return $list;
    }

    /* 2.2.5	账单明细-计量热费对比查询 */

    function getRjlCharge($houseCode, $chargeMonth)
    {
        $url = C('DB_ORACLE');
        $user = M('', null, $url);
        $housecode = $houseCode;
        $chargemonth = $chargeMonth;
        $list = $user->query("select  a.chargemonth,a.price,a.LASTREADDATE,a.DATA_DATE,a.REALACCOUNT,a.POINTNUMBER ,DAYREALACCOUN,
							case when a.DATA_DATE is not null and a.LASTREADDATE is not null then round((to_date(a.DATA_DATE,'yyyy-mm-dd')-to_date(a.LASTREADDATE,'yyyy-mm-dd') +1)*DAYREALACCOUN,2) else 0 end basecash,to_date(a.DATA_DATE,'yyyy-mm-dd')-to_date(a.LASTREADDATE,'yyyy-mm-dd') +1 as ts from sf_charge_web_jl a where a.housecode='$housecode' and chargemonth<='$chargemonth' order by a.LASTREADDATE
							");
        return $list;
    }

    /* 2.2.5	账单明细-柱状图查询 */

    function getZdmxZztCharge($houseCode, $itemCode, $accountDate)
    {
        $url = C('DB_ORACLE');
        $user = M('', null, $url);
        $list = $user->query("select a.housecode,a.nowchargemonth,a.nowrealaccount,a.NEWPOINTNUMBER,b.oldchargemonth,b.oldrealaccount,b.oldPOINTNUMBER
								from
								(
								select nowchargemonth,nowrealaccount,NEWPOINTNUMBER,housecode , rn
								from
								(
								SELECT  chargemonth as nowchargemonth ,CASE when latefee >0 then REALACCOUNT+latefee else REALACCOUNT end nowrealaccount ,POINTNUMBER as NEWPOINTNUMBER, housecode,row_number() over(order by chargemonth desc) rn
								FROM SF_CHARGE WHERE housecode='$houseCode'
								and  ITEMCODE='$itemCode' and chargemonth<='$accountDate'
								)
								where rn<=12
								) a ,
								--------
								(
								select oldchargemonth,housecode,oldrealaccount,oldPOINTNUMBER , rn
								from
								(
								SELECT  chargemonth as oldchargemonth ,realaccount as oldrealaccount, POINTNUMBER as oldPOINTNUMBER , housecode,row_number() over(order by chargemonth desc) rn
								FROM SF_CHARGE WHERE housecode='$houseCode'
								and  ITEMCODE='$itemCode' and chargemonth<=to_char(to_date('$accountDate','yyyymm')-365,'yyyymm')
								)
								where rn<=12
								) b
								where a.housecode=b.housecode and a.rn=b.rn
								order by a.nowchargemonth asc
							");
        return $list;
    }

    /* 2.2.6	曲线图查询(初步判断可清理) */

    function getHisChar($houseCode, $itemCode, $chargeMonth, $buss)
    {
        try {
            $wsdl = "http://" . C('api_url') . "/tjstc_ggsykf_webs/queryChar?wsdl";
            if (check_url_status($wsdl) == false) {
                $data['error'] = 1;
                return ($data);
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
            return ($data);
        }
        $r = json_decode($ret->return);
        if ($r[0] == '00') {
            return ($r[2]); //数组内的内容输出
        }
    }

    /* 2.2.7	能源类型 */

    function getPowerType()
    {
        try {
            $wsdl = "http://" . C('api_url') . "/tjstc_ggsykf_webs/queryOnline?wsdl";
            if (check_url_status($wsdl) == false) {
                $data['error'] = 1;
                return ($data);
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
            return ($data);
        }
        $r = json_decode($ret->return);
//        dump($r[2]);
        if ($r[0] == '00') {
            return ($r[2]); //数组内的内容输出
        }
    }

    /* 2.2.8	房间信息查询(初步判断可清理) */

    //查询一个房间的基础信息
    function getHouseAdress($houseCode)
    {
        try {
            $wsdl = "http://" . C('api_url') . "/tjstc_ggsykf_webs/queryOnline?wsdl";
            if (check_url_status($wsdl) == false) {
                $data['error'] = 1;
                return ($data);
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
            return ($data);
        }
        $r = json_decode($ret->return);

        if ($r[0] == '00') {
            return ($r[2][0]); //数组内的内容输出
        }
    }


    /* 2.2.9	基础信息保存、新数据读取 */

    //指定房间的基础信息保存、新数据读取
    function saveNewHouse($houseCode, $LinkMan, $OldLinkMan, $LinkTel, $OldLinkTel, $WorkUnit, $OldWorkUnit, $MailingAddress, $OldMailingAddress)
    {
        try {
            $wsdl = "http://" . C('api_url') . "/tjstc_ggsykf_webs/saveHouseDetail?wsdl";
            if (check_url_status($wsdl) == false) {
                $data['error'] = 1;
                return ($data);
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
            return ($data);
        }
        $r = json_decode($ret->return);

        if ($r[0] == '00') {
            return ($r[2][0]); //数组内的内容输出
        }

    }
    /* 1.1.1    1.1.1   验证接口 */

    //1.1.1 验证接口
    function checkRoom($houseCode, $itemCode, $username, $typeCode)
    {
        try {
            $wsdl = "http://" . C('api_url') . "/tjstc_ggsykf_webs/checkRoom?wsdl";
            if (check_url_status($wsdl) == false) {
                $data['error'] = 1;
                return ($data);
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
            return ($data);
        }
        $r = json_decode($ret->return);
        return $r;
        // if ($r[0] == '00') {
        //     return($r[2]); //数组内的内容输出
        // }

    }

    /* 2.2.10	费用明细按日期和房间查询(初步判断可清理) */

    //费用明细按日期和房间查询
    function getChargeInforsByHouse($houseCode, $chargeMonth, $itemCode = 'B', $appId = 'tjstc_fwmh', $appPass = '789654')
    {
        try {
            $wsdl = C('api_get_charge_url') . "/queryAll?wsdl";
            if (check_url_status($wsdl) == false) {
                $data['error'] = 1;
                return ($data);
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
    function getLineChartDatasByHouse($housecode, $itemcode)
    {
        $url = C('DB_ORACLE');
        $lszd = M('', null, $url);

        $list = $lszd->query("select a.REALACCOUNT,a.NOWCASH,(a.REALACCOUNT-a.NOWCASH) as qf,a.CHARGEMONTH,
									case when a.ITEMCODE='A' then '采暖面积'
								when a.ITEMCODE='B' then '自来水'
								when a.ITEMCODE='D' then '天然气' when a.ITEMCODE='E'  then '采暖计量' else '' end  ITEMCODE,
								c.COMMUNITYNAME||d.BUILDINGNAME||b.CELLCODE||'单元'||b.FLOOR||'层'||b.DOORPLATECODE||'室' as address,yl,b.HOUSECODE
								from(
								select CASE when latefee >0 then REALACCOUNT+latefee else REALACCOUNT+ NVL(PKG_CHARGE.getOVF_number(HOUSECODE,
									ITEMCODE,
									CHARGEMONTH,
									CLOCKNUMBER,
									LATEFEESTARTD,
									TO_CHAR(SYSDATE, 'YYYY-MM-DD'),
									REALACCOUNT - NOWCASH -
									BACKMONEY,
									'hnrlAdmin'),
									0) end  realaccount,nowcash+LATEFEEcash as nowcash,chargemonth,itemcode,housecode,
								case when itemcode='A' then chargearea else
								nowpointnumber - lastpointnumber end  yl
									from SF_CHARGE
								) a,FC_HOUSE b,FC_COMMUNITY c,FC_BUILDING d
								where a.HOUSECODE=b.HOUSECODE
								and  b.COMMUNITYCODE=c.COMMUNITYCODE
								and b.BUILDINGCODE=d.BUILDINGCODE
								and a.HOUSECODE = '$housecode'
								and (a.ITEMCODE = '$itemcode' or '$itemcode' is null)
								order by b.HOUSECODE,a.CHARGEMONTH desc

								");
        //dump($lszd->getLastSql());
        return $list;
    }

    /* 2.2.11	行业类型 */

    function getIndustryInfos($type, $value)
    {
        $url = C('api_get_industry_url') . '/' . $type;
        if (check_url_status($url) == false) {
            $data['error'] = 1;
            return ($data);
        }

        $param = array();
        if ('ItemCodeServletTwo' == $type) {
            $param['sjhy'] = $value;
        }

        $data = http_build_query($param);
        $opts = array(
            'http' => array(
                'method' => 'POST',
                'header' => "Content-type: application/x-www-form-urlencoded\r\n" .
                "Content-Length: " . strlen($data) . "\r\n",
                'content' => $data
            )
        );

        try {
            $context = stream_context_create($opts);
            $ret['ret'] = file_get_contents($url, false, $context);
            $ret['error'] = 0;
        } catch (Exception $e) {
            $ret['error'] = 1;
        }
        return ($ret);
    }

    /* 2.2.12	欠费查询房间地址*/

    function getAddress($houseCode)
    {
        $url = C('DB_ORACLE');
        $user = M('', null, $url);
        $housecode = $houseCode;
        $list = $user->query("select communityname||buildingname||cellcode||'单元'||floor||'层'||doorplatecode||'室' as address , housecode ,communityname  from view_house_qi
							where houseCode ='$houseCode'");
        //dump($user->getLastSql());
        return $list;
    }

    /* 2.2.13	自助服务供热*/
    function getReData($housecode, $itemcode)
    {
        $url = C('DB_ORACLE');
        $lszd = M('', null, $url);

        $list = $lszd->query("select a.REALACCOUNT,a.NOWCASH,(a.REALACCOUNT-a.NOWCASH) as qf,a.CHARGEMONTH,
									case when a.ITEMCODE='A' then '采暖面积'
								when a.ITEMCODE='B' then '自来水'
								when a.ITEMCODE='D' then '天然气' when a.ITEMCODE='E'  then '采暖计量' else '' end  ITEMCODE,
								c.COMMUNITYNAME||d.BUILDINGNAME||b.CELLCODE||'单元'||b.FLOOR||'层'||b.DOORPLATECODE||'室' as address,yl,b.HOUSECODE
								from(
								select CASE when REALACCOUNT=nowcash then REALACCOUNT else REALACCOUNT+ NVL(PKG_CHARGE.getOVF_number(HOUSECODE,
								ITEMCODE,
								CHARGEMONTH,
								CLOCKNUMBER,
								LATEFEESTARTD,
								TO_CHAR(SYSDATE, 'YYYY-MM-DD'),
								REALACCOUNT - NOWCASH -
								BACKMONEY,
								'hnrlAdmin'),
								0) end  realaccount,nowcash+LATEFEEcash as nowcash,chargemonth,itemcode,housecode,
								case when itemcode='A' then chargearea else
								nowpointnumber - lastpointnumber end  yl
									from SF_CHARGE
								) a,FC_HOUSE b,FC_COMMUNITY c,FC_BUILDING d
								where a.HOUSECODE=b.HOUSECODE
								and  b.COMMUNITYCODE=c.COMMUNITYCODE
								and b.BUILDINGCODE=d.BUILDINGCODE
								and a.nowcash >0
								and a.HOUSECODE = '$housecode'
								and (a.ITEMCODE  in ('A','E') or '$itemcode' is null)
								order by b.HOUSECODE,a.CHARGEMONTH desc
								");
        //dump($lszd->getLastSql());
        return $list;
    }

    /*2.2.14 历史账单和用量查询 */
    function getLastData($housecode, $itemcode, $year)
    {
        $url = C('DB_ORACLE');
        $lszd = M('', null, $url);

        $sql = "select a.REALACCOUNT,a.NOWCASH,(a.REALACCOUNT-a.NOWCASH) as qf,a.CHARGEMONTH,case when a.ITEMCODE='A' then '采暖面积' when a.ITEMCODE='B' then '自来水' when a.ITEMCODE='M' then '自来水阶梯2' when a.ITEMCODE='N' then '自来水阶梯3' when a.ITEMCODE='D' then '天然气' when a.ITEMCODE='P' then '天然气阶梯2'
				when a.ITEMCODE='Q' then '天然气阶梯3'   when a.ITEMCODE='E'  then '采暖计量' else '' end  ITEMCODE,";

        $sql .= "c.COMMUNITYNAME||d.BUILDINGNAME||b.CELLCODE||'单元'||b.FLOOR||'层'||b.DOORPLATECODE||'室' as address,yl,b.HOUSECODE";

        $sql .= " from(
                select CASE when latefee >0 then REALACCOUNT+latefee else REALACCOUNT end  realaccount,nowcash+LATEFEEcash as nowcash,chargemonth,itemcode,housecode,
                case when itemcode='A' then chargearea else
                POINTNUMBER  end  yl
                from SF_CHARGE where HOUSECODE='$housecode' ";
        if ($itemcode == 'B') {
            $sql .= " and ( ITEMCODE IN ('B','M','N')";
        }
        if ($itemcode == 'D') {
            $sql .= " and ( ITEMCODE IN ('D','P','Q')";
        }
        if ($itemcode == 'A' || $itemcode == 'E') {
            //$sql .= "  and   HOUSECODE in (select HOUSECODE from VIEW_ITEMPRICE t where HOUSECODE='$housecode' and rjldj =0) ";
            $sql .= "  and   ITEMCODE='A' ";
        }
        $sql .= "    and substr(chargemonth,0,4)='$year'";
        if ($itemcode == 'B' || $itemcode == 'D') {
            $sql .= "    )";
        }

        $sql .= "     and
		((length(CHARGEMONTH)=6 and
		CHARGEMONTH <=(
		CASE WHEN TO_CHAR(sysdate, 'dd') > '29'
			  THEN TO_CHAR(sysdate,'yyyymm')
			  else TO_CHAR(ADD_MONTHS(sysdate, -1),'yyyymm') end)
			  )
		or
		(length(CHARGEMONTH)=4 and
			  CHARGEMONTH <=(
				case when TO_CHAR(sysdate,'MM-DD')>='06-01' then TO_CHAR(sysdate,'YYYY') else TO_CHAR(add_months(sysdate, -12),'YYYY') end)
		))
                ) a,FC_HOUSE b,FC_COMMUNITY c,FC_BUILDING d
                where a.HOUSECODE=b.HOUSECODE
                and  b.COMMUNITYCODE=c.COMMUNITYCODE
                and b.BUILDINGCODE=d.BUILDINGCODE
                order by b.HOUSECODE,a.CHARGEMONTH desc";
        $list = $lszd->query($sql);
        return $list;
    }

	function getHistoryData($housecode, $itemcode, $year)
    {
        $url = 'http://10.105.15.2/TjstcWebImpl/GetHistoryBillServlet';
		$post_data['vHOUSECODE'] = $housecode;
		$post_data['vITEMCODE'] = $itemcode;
		$post_data['vCHARGEMONTH'] = $year;
		$list = json_decode($this->curl_post($url,$post_data));
		return $list;
    }
	function GetHouseInfoServlet($housecode)
    {
        $url = 'http://10.105.15.2/TjstcWebImpl/GetHouseInfoServlet';
		$post_data= "vHOUSECODE=$housecode";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		$output = curl_exec($ch);
		$date=json_decode($output,true);
		return $date;
    }
	function GetHistoryBill_AServlet($housecode, $year, $years)
    {
        $url = 'http://10.105.15.2/TjstcWebImpl/GetHistoryBill_AServlet';
		$post_data['vHOUSECODE'] = $housecode;
		$post_data['vCHARGEMONTHLAST'] = $year;
		$post_data['vCHARGEMONTH'] = $years;
		$list = json_decode($this->curl_post($url,$post_data));
		return $list;
    }



    /* 2.2.15	用户注册-插入ORACLE */

    function userInsert($id, $username, $password)
    {
        $url = C('DB_ORACLE');
        $user = M('', null, $url);
        $sql = "insert into MHNETUSER (ID,USERNAME,PASSWORD) values('$id','$username','$password')";
        $res = $user->query($sql);
        return $res;
    }

    /* 2.2.16	用户解绑-删除ORACLE */

    function bindDelete($id)
    {
        $url = C('DB_ORACLE');
        $user = M('', null, $url);
        $sql = "DELETE FROM MHNETBIND WHERE ID = '$id'";
        $res = $user->query($sql);
        return $res;
    }

    /* 2.2.17	用户绑定-插入ORACLE */

    function bindInsert($id, $user_id, $housecode)
    {
        $url = C('DB_ORACLE');
        $user = M('', null, $url);
        $sql = "insert into MHNETBIND (ID,USER_ID,HOUSECODE) values('$id','$user_id','$housecode')";
        $res = $user->query($sql);
        return $res;
    }

    /* 2.2.18	用户密码更正-更新ORACLE */

    function passUpdate($user_id, $password)
    {
        $url = C('DB_ORACLE');
        $user = M('', null, $url);
        $sql = "update MHNETUSER set PASSWORD = '$password' where ID = '$user_id'";
        $res = $user->query($sql);
        return $res;
    }

    /* 2.2.19	合同预约-小区名称 */

    function getVillage()
    {
        $url = C('DB_ORACLE');
        $ht = M('', null, $url);
        $sql = "select distinct   c.communitycode,c.communityname
				from fc_house  a,  fc_building b , fc_community c
				where a.buildingcode=b.buildingcode
				and a.communitycode=c.communitycode
				and a.usetype='居民' and c.communitycode not in ('8888','9999')
				order by c.communitycode";
        $list = $ht->query($sql);
        return $list;
    }

    /* 2.2.20	合同预约-楼号 */

    function getBuild($communitcode)
    {
        $url = C('DB_ORACLE');
        $ht = M('', null, $url);
        $sql = "select distinct  b.buildingcode,b.buildingname
				from fc_house  a,  fc_building b , fc_community c
				where a.buildingcode=b.buildingcode  and a.communitycode=c.communitycode
				and a.usetype='居民'  and c.communitycode not in ('8888','9999')
				and c.communitycode='$communitcode'
				order by b.buildingcode";
        $list = $ht->query($sql);
        return $list;
    }

    /* 2.2.21	合同预约-门 */

    function getUnitNum($buildingcode)
    {
        $url = C('DB_ORACLE');
        $ht = M('', null, $url);
        $sql = "select distinct a.cellcode ,a.cellcode　from fc_house a
				where  a.usetype='居民' and a.buildingcode='$buildingcode'
				order by a.cellcode";
        $list = $ht->query($sql);
        return $list;
    }

    /* 2.2.22	合同预约-号 */

    function getFloorNum($buildingcode, $cellcode)
    {
        $url = C('DB_ORACLE');
        $ht = M('', null, $url);
        $sql = "select distinct a.floor||a.doorplatecode as floor　from fc_house a
				where  a.usetype='居民' and a.buildingcode='$buildingcode'
				and a.cellcode='$cellcode'
				order by a.floor||a.doorplatecode";
        $list = $ht->query($sql);
        return $list;
    }

    /* 2.2.23	合同预约-缴费面积 */

    function getJfmj($housecode)
    {
        $url = C('DB_ORACLE');
        $ht = M('', null, $url);
        $sql = "select CHARGEAREA From fc_chargearea where housecode='$housecode' and itemcode='A'";
        $area = $ht->query($sql);
        return $area;
    }

    /* 2.2.24	合同预约-“本季应交金额”，“优惠金额”，“结余金额”，“实际应缴金额” */

    function getJffy($housecode)
    {
        $url = C('DB_ORACLE');
        $ht = M('', null, $url);
        $sql = "select cpl.housecode,CPL.TOTALACCOUNT,(MINUSMONEY+(case when (to_char(sysdate,'YYYY-MM-DD')>=cnt.startDate and  to_char(sysdate,'YYYY-MM-DD')<=nvl(cnt.endDate,'3000-12-31')) and not exists (select 1 from fc_stopheating fs where fs.housecode=cpl.housecode and fs.itemcode=cpl.itemcode and fs.mon=cpl.chargemonth and fs.clustercoding=cpl.clustercoding and fs.chid=cpl.clocknumber and fs.stopmark is null)
				then ROUND((TOTALACCOUNT)*CNT.PERCENT,2) ELSE 0 END)) AS JM,NVL(JY.JYJE,0) AS JYJE,case when CPL.TOTALACCOUNT-(MINUSMONEY+(case when (to_char(sysdate,'YYYY-MM-DD')>=cnt.startDate and  to_char(sysdate,'YYYY-MM-DD')<=nvl(cnt.endDate,'3000-12-31')) and not exists (select 1 from fc_stopheating fs where fs.housecode=cpl.housecode and fs.itemcode=cpl.itemcode and fs.mon=cpl.chargemonth
				and fs.clustercoding=cpl.clustercoding and fs.chid=cpl.clocknumber and fs.stopmark is null)then ROUND((TOTALACCOUNT)*CNT.PERCENT,2) ELSE 0 END))-NVL(JY.JYJE,0)<0 THEN 0
				ELSE
				CPL.TOTALACCOUNT-(MINUSMONEY+(case when (to_char(sysdate,'YYYY-MM-DD')>=cnt.startDate and  to_char(sysdate,'YYYY-MM-DD')<=nvl(cnt.endDate,'3000-12-31'))
				and not exists (select 1 from fc_stopheating fs where fs.housecode=cpl.housecode and fs.itemcode=cpl.itemcode and fs.mon=cpl.chargemonth and fs.clustercoding=cpl.clustercoding and fs.chid=cpl.clocknumber and fs.stopmark is null)
				then ROUND((TOTALACCOUNT)*CNT.PERCENT,2) ELSE 0 END))-NVL(JY.JYJE,0) end as sjyjje,cpl.*
				from sf_charge cpl,
				(select * from T_CONCESSIONS where to_char(sysdate,'YYYY-MM-DD')>=startDate AND TO_CHAR(SYSDATE,'YYYY-MM-DD')<=NVL(ENDDATE,'3000-12-31') AND CLUSTERCODING='hnrlAdmin') CNT,(SELECT a.housecode,nvl(a.nowaccount,0)+nvl(b.imprest,0) as jyje FROM SF_IMPREST A left join (select b.housecode,sum(imprest) as imprest from fc_house a INNER JOIN SF_DETAIL B ON A.HOUSECODE=B.HOUSECODE AND ITEMCODE='A' AND CHARGEMONTH='2016'and B.ROLLBACKFLAG='0' and imprest>0
				WHERE A.COMMUNITYCODE NOT IN ('8888','9999') GROUP BY B.HOUSECODE) B
				ON A.HOUSECODE=B.HOUSECODE where A.ITEMCODE='A' and a.COMMUNITYCODE NOT IN ('8888','9999') and a.housecode='$housecode') jy
				where cpl.chargemonth=cnt.chargemonth(+)
				AND CPL.ITEMCODE=CNT.ITEMCODE(+)
				and cpl.housecode='$housecode'
				AND CPL.ITEMCODE='A'
				and cpl.chargemonth=(select case when to_char(sysdate,'MM')>= financemonth then to_char(sysdate,'YYYY')
				ELSE TO_CHAR(TO_NUMBER(TO_CHAR(SYSDATE,'YYYY'))-1) END YEAR
				FROM SF_ITEM WHERE CLUSTERCODING='hnrlAdmin' AND ITEMCODE='A')
				and cpl.COMMUNITYCODE NOT IN ('8888','9999')
				AND CPL.HOUSECODE=JY.HOUSECODE(+)";
        $list = $ht->query($sql);
        return $list[0];
    }

    /* 2.2.25	张单明细-采暖明细 */

    function getCnmx($housecode)
    {
        $url = C('DB_ORACLE');
        $ht = M('', null, $url);
        $sql = "select price,chargearea,
		case
         when nowcash = (totalaccount - minusmoney) then
          minusmoney
         else
          YHJM
       end as DN_YHJM,
       totalaccount as DN_YJBJ,
       wyj as DN_WYJ,
       nowcash + latefeecash as DN_YJ,
       zqqfbj as zq_qfbj,
       zqwyj as zq_wyj,
       IMPREST as QN_JY
  from (select cpl.housecode,
               cpl.price,
               cpl.chargearea,
               cpl.totalaccount,
               case
                 when (cnt.chargemonth is null or sf_item.ifRateMinus = '0') then
                  0
                 else
                  (case
                    when (to_char(sysdate, 'YYYY-MM-DD') >= cnt.startDate and
                         to_char(sysdate, 'YYYY-MM-DD') <=
                         nvl(cnt.endDate, '3000-12-31')) and not exists
                     (select 1
                            from fc_stopheating fs
                           where fs.housecode = cpl.housecode
                             and fs.itemcode = cpl.itemcode
                             and fs.mon = cpl.chargemonth
                             and fs.clustercoding = cpl.clustercoding
                             and fs.chid = cpl.clocknumber
                             and fs.stopmark is null) and
                         cpl.standardcode = 'A01' then
                     round((totalaccount) * cnt.percent, 2)
                    else
                     0
                  end)
               end YHJM,
               nvl(sfd.IMPREST, 0) as IMPREST,
               cpl.minusmoney,
               cpl.realaccount,
               cpl.nowcash,
               cpl.latefeecash,
               case
                 when cpl.latefeeend is null then
                  PKG_CHARGE.getOVFNum(cpl.housecode,
                                       cpl.itemcode,
                                       cpl.chargemonth,
                                       cpl.clocknumber,
                                       CPL.LATEFEESTARTD,
                                       case
                                         when cpl.latefeeend is null then
                                          TO_CHAR(SYSDATE, 'YYYY-MM-DD')
                                         else
                                          latefeeend
                                       end,
                                       CPL.REALACCOUNT - cpl.nowcash,
                                       'hnrlAdmin')
                 else
                  cpl.latefee
               end as wyj
          from VIEW_HOUSE_CHARGE cpl,
               sf_item,
               SF_setPackage sfs,
               (select *
                  from T_CONCESSIONS
                 where to_char(sysdate, 'YYYY-MM-DD') >= startDate
                   and to_char(sysdate, 'YYYY-MM-DD') <=
                       nvl(endDate, '3000-12-31')
                   and clusterCoding = 'hnrlAdmin') cnt,
               (select sum(minusMoney) MIS,
                       chargemonth,
                       itemcode,
                       clocknumber
                  from sf_minus
                 where housecode = '$housecode'
                   and minustype <> '优惠减免'
                   and clusterCoding = 'hnrlAdmin'
                 group by itemcode, chargemonth, clocknumber) mis,
               (select housecode,
                       chargemonth,
                       itemcode,
                       clocknumber,
                       sum(IMPREST) IMPREST,
                       sum(DERATE) DERATE,
                       sum(LATEFEETOTAL) LATEFEETOTAL,
                       sum(LATEFEENOW) LATEFEENOW,
                       sum(ADDIMPREST) ADDIMPREST,
                       sum(CHARGEAREA) CHARGEAREA,
                       sum(CASH) CASH,
                       '' LATEFEEREASON,
                       '' LATEFEEMINUSRSN,
                       sum(YHJMMONEY) YHJMMONEY
                  from sf_detail
                 where rollbackflag = '0'
                   and clusterCoding = 'hnrlAdmin'
                   and realcount > 0
                 group by housecode, chargemonth, itemcode, clocknumber) sfd,
               (select housecode sendhc
                  from bi_bankpay bb, bi_presend bp
                 where bb.pay_id = bp.pay_id
                   and bb.pay_rectime is null
                   and bp.clusterCoding = 'hnrlAdmin'
                 group by housecode) presend
         where cpl.housecode = '$housecode'
           and cpl.chargemonth = '2016'
           and cpl.itemcode = sf_item.itemcode
           and cpl.itemcode = mis.itemcode(+)
           and cpl.chargemonth = mis.chargemonth(+)
           and cpl.clocknumber = mis.clocknumber(+)
           and sf_item.itemcode = sfs.itemcode(+)
           and cpl.chargemonth = cnt.chargemonth(+)
           and cpl.ItemCode = cnt.ItemCode(+)
           and sfd.housecode(+) = cpl.housecode
           and sfd.CLOCKNUMBER(+) = cpl.CLOCKNUMBER
           and sfd.chargemonth(+) = cpl.chargemonth
           and sfd.itemcode(+) = cpl.itemcode
           and cpl.clusterCoding = 'hnrlAdmin'
           and sf_item.clusterCoding = 'hnrlAdmin'
           and sf_item.clusterCoding = sfs.clusterCoding(+)
           and cpl.housecode = presend.sendhc(+)) dn,
       (select housecode, sum(zqqfbj) as zqqfbj, sum(zqwyj) as zqwyj
          from (select housecode,chargemonth,REALACCOUNT - nowcash as zqqfbj,PKG_CHARGE.getOVFNum(housecode,itemcode,chargemonth,clocknumber,LATEFEESTARTD,
                                            case
                                              when latefeeend is null then
                                               TO_CHAR(SYSDATE, 'YYYY-MM-DD')
                                              else
                                               latefeeend
                                            end,
                                            realaccount - nowcash,
                                            'hnrlAdmin') as zqwyj
                  from sf_charge
                 where itemcode = 'A'
                   and housecode = '$housecode'
                   and chargemonth < '2016')
         group by housecode) zq
 where dn.housecode = zq.housecode";

        $list = $ht->query($sql);
        return $list[0];
    }


/* 2.2.25	张单明细-采暖明细 */

    function getCnmx2($housecode)
    {
        $url = C('DB_ORACLE');
        $ht = M('', null, $url);
		$year = date('Y');
		$strtime = date('Y')."-6-1 00:00:00";
		if(strtotime(date('Y-m-d H:i:s'))<=strtotime($strtime)){
			$year = date('Y')-1;
		}
		$lastyear = $year-1;
        $sql = "
select a.housecode as housecode_old,
a.chargearea as mianji,a.totalaccount as mjrf_old,0 as yongliang,0 as rjlfy,0 as jieyu,0 as bujiao,
b.housecode as housecode_new,b.totalaccount as mjrf_new,b.minusmoney as youhuui,b.realaccount as yingjiao,b.nowcash as yijiao
from sf_charge a
left join sf_charge b on a.housecode=b.housecode and a.clocknumber=b.clocknumber and a.itemcode=b.itemcode and b.chargemonth='$year'
where a.itemcode='A' and a.chargemonth='2017' and a.housecode in (
select distinct housecode from fc_stopheating where mon='$lastyear' and stopmark is null
and communitycode not in ('8888','9999'))
and a.housecode='$housecode'
union all

select
a.housecode as housecode_old,
a.chargearea as mianji,a.totalaccount as mjrf_old,0 as yongliang,0 as rjlfy,0 as jieyu,0 as bujiao,
b.housecode as housecode_new,b.totalaccount as mjrf_new,b.minusmoney as youhuui,b.realaccount as yingjiao,b.nowcash as yijiao
from sf_charge a
left join sf_charge b on a.housecode=b.housecode and a.clocknumber=b.clocknumber and a.itemcode=b.itemcode and b.chargemonth='$year'
where a.itemcode='A' and a.chargemonth='$lastyear' and a.communitycode not in ('8888','9999')
and a.housecode not in (
select housecode from jl_point_total_final where chargemonth='$lastyear' and communitycode not in ('8888','9999'))
and a.housecode not in (
select housecode from fc_stopheating where mon='$lastyear' and stopmark is null and communitycode not in ('8888','9999'))
and a.housecode='$housecode'
union all

select
a.*
,B.HOUSECODE as HOUSECODE_NEW,B.TOTALACCOUNT as MJRF_NEW,
B.MINUSMONEY +
case when B.MINUSMONEY >0 then 0 else nvl((select case when (TO_CHAR(sysdate,'YYYY-MM-DD')>=CNT.STARTDATE and  TO_CHAR(sysdate,'YYYY-MM-DD')<=NVL(CNT.ENDDATE,'3000-12-31'))
and not exists (select 1 from fc_stopheating fs where fs.housecode=cpl.housecode and fs.itemcode=cpl.itemcode and fs.mon=cpl.chargemonth
and fs.clustercoding=cpl.clustercoding and fs.chid=cpl.clocknumber and fs.stopmark is null) and cpl.standardcode='A01'
then
round((totalaccount)*cnt.percent,2)  else 0 end aaa
from sf_charge cpl,
(select * from T_CONCESSIONS
		  where TO_CHAR(sysdate,'YYYY-MM-DD')>=STARTDATE
        and TO_CHAR(sysdate,'YYYY-MM-DD')<=NVL(ENDDATE,'3000-12-31')) CNT
where cpl.CHARGEMONTH=CNT.CHARGEMONTH(+)
	    and CPL.ITEMCODE=CNT.ITEMCODE(+)
      and CPL.ITEMCODE='A' and CPL.CHARGEMONTH='$year'
      and cpl.housecode='$housecode'),0) end
as YOUHUUI,
case when B.MINUSMONEY >0 then B.REALACCOUNT
else B.REALACCOUNT - nvl((select case when (TO_CHAR(sysdate,'YYYY-MM-DD')>=CNT.STARTDATE and  TO_CHAR(sysdate,'YYYY-MM-DD')<=NVL(CNT.ENDDATE,'3000-12-31'))
and not exists (select 1 from fc_stopheating fs where fs.housecode=cpl.housecode and fs.itemcode=cpl.itemcode and fs.mon=cpl.chargemonth
and fs.clustercoding=cpl.clustercoding and fs.chid=cpl.clocknumber and fs.stopmark is null) and cpl.standardcode='A01'
then
round((totalaccount)*cnt.percent,2)  else 0 end aaa
from sf_charge cpl,
(select * from T_CONCESSIONS
		  where TO_CHAR(sysdate,'YYYY-MM-DD')>=STARTDATE
        and TO_CHAR(sysdate,'YYYY-MM-DD')<=NVL(ENDDATE,'3000-12-31')) CNT
where cpl.CHARGEMONTH=CNT.CHARGEMONTH(+)
	    and CPL.ITEMCODE=CNT.ITEMCODE(+)
      and CPL.ITEMCODE='A' and CPL.CHARGEMONTH='$year'
      and cpl.housecode='$housecode'),0) end
as yingjiao,b.nowcash as yijiao
from (
select
housecode as housecode_old
,chargearea as mianji
,REALACCOUNT as mjrf_old
,total_point as yongliang
,TOTAL_CHARGE as rjlfy
,case when sh.sfhhousecode is not null
then 0
else case when TOTAL_CHARGE >= REALACCOUNT or CHASTA = -1 then 0 else REALACCOUNT - TOTAL_CHARGE end
end jieyu
,case when TOTAL_CHARGE-REALACCOUNT<=0 then 0 else TOTAL_CHARGE-REALACCOUNT end bujiao
from
(
SELECT
  p.HOUSECODE,
  p.STANDARDNAME,
  p.CHARGEMONTH,
  WM_CONCAT(p.CLOCKNUMBER) CLOCKNOMBERS,
  p.price,
  p.chargearea,
  SUM(NVL(p.TOTAL_POINT,0)) TOTAL_POINT,
  round(p.areaPrice * p.chargearea * 0.3, 2) + SUM(round(p.price * NVL(p.TOTAL_POINT,0), 2)) TOTAL_CHARGE,
  p.areaPrice * p.chargearea REALACCOUNT,
  case when p.BALANCE_FLAG = '0' then '应收未结' else '应收已结' end BALANCE_FLAG,
  WM_CONCAT(p.LAST_POINTNUMBER) LAST_POINTNUMBER,
  WM_CONCAT(p.POINTNUMBER) POINTNUMBER,
  round(p.areaPrice * p.chargearea * 0.3, 2) AREATOTAL,
  sum(round(p.price * NVL(p.TOTAL_POINT,0), 2)) POINTTOTAL,
  p.COMMUNITYCODE,
  p.BUILDINGCODE,
  p.CHASTA
FROM
  (
  SELECT
      T1.HOUSECODE,
      T3.STANDARDNAME,
      T1.CHARGEMONTH,
      T1.CLOCKNUMBER,
      T3.PRICE,
      NVL(T1.TOTAL_POINT,0) TOTAL_POINT,
      t4.price areaPrice,
      t4.chargearea,
      T1.BALANCE_FLAG,
      t1.LAST_POINTNUMBER,
      t1.POINTNUMBER,
      T4.COMMUNITYCODE,
      T4.BUILDINGCODE,
          sign(t4.nowcash - t4.realaccount) CHASTA
  FROM
          JL_POINT_TOTAL_FINAL T1,
          SF_STANDARD T3,
          SF_CHARGE T4
  WHERE
          T1.ITEMCODE = T3.ITEMCODE
  AND T1.STANDARDCODE = T3.STANDARDCODE

  AND T1.HOUSECODE = T4.HOUSECODE
  AND T1.ITEMCODE = 'E'
  AND T4.ITEMCODE = 'A'
  AND T1.CHARGEMONTH = T4.CHARGEMONTH
  ) p
group by p.housecode, p.standardname, p.chargemonth, p.price,p.chargearea, p.areaprice * p.chargearea, p.balance_flag ,
round(p.areaPrice * p.chargearea * 0.3, 2), p.COMMUNITYCODE, p.BUILDINGCODE,p.CHASTA
) m
left join (
    select distinct c.HOUSECODE as sfhhousecode from FC_HOUSE a
    inner join SF_CHARGE B on a.HOUSECODE=B.HOUSECODE and B.ITEMCODE='A' and B.CHARGEMONTH='$lastyear'
    inner join SF_DETAIL C
    on b.housecode=c.housecode and b.itemcode=c.itemcode and b.chargemonth=c.chargemonth
    and B.CLOCKNUMBER=C.CLOCKNUMBER and c.rollbackflag='0'
    and C.OPERATEDATE>B.OPERATEDATE
    inner join SF_BILL D on C.BILLCODE=D.BILLCODE and D.IMPRESTTYPE='收费'
    where a.CNSFFS='计量收费' and a.HOUSECODE not in (
    select housecode from FC_STOPHEATING where mon='$lastyear' and STOPMARK is null
    )
) sh on m.housecode=sh.sfhhousecode
where chargemonth = '$lastyear') a
left join SF_CHARGE B on a.HOUSECODE_OLD=B.HOUSECODE and B.ITEMCODE='A' and B.CHARGEMONTH='$year'
where a.housecode_old='$housecode'";

        $list = $ht->query($sql);
        return $list[0];
    }

	/* 2.2.25	张单明细-采暖明细 */

    function getCnmx1($housecode)
    {
        $url = C('DB_ORACLE');
        $ht = M('', null, $url);
		$year = date('Y');
		$strtime = date('Y')."-6-1 00:00:00";
		if(strtotime(date('Y-m-d H:i:s'))<=strtotime($strtime)){
			$year = date('Y')-1;
		}
		$sql = "select HOUSECODE,PRICE,CHARGEAREA,case when fshousecode is null then round(PRICE*CHARGEAREA,2) else totalaccount end totalaccount from (select cpl.housecode,cpl.chargemonth,cpl.itemcode,cpl.clocknumber,cpl.price,cpl.chargearea,cpl.totalaccount,fs.housecode as fshousecode from SF_CHARGE cpl left join FC_STOPHEATING FS on fs.housecode = cpl.housecode and fs.itemcode = cpl.itemcode and fs.mon = cpl.chargemonth and fs.clustercoding = cpl.clustercoding and FS.CHID = CPL.CLOCKNUMBER and FS.STOPMARK is null where CPL.housecode='$housecode' and CPL.itemcode='A' and cpl.chargemonth='$year')";
        $list = $ht->query($sql);
        return $list;
    }

	/* 2.2.25	张单明细-采暖明细 */

    function getCnmx3($housecode)
    {
        $url = C('DB_ORACLE');
        $ht = M('', null, $url);

		$sql = "SELECT * from view_house_lsfy WHERE houseCode ='$housecode'";
        $list = $ht->query($sql);
        return $list;
    }


	/* 2.2.25	张单明细-采暖明细计量数值 */

    function getCnjl($housecode)
    {
        $url = C('DB_ORACLE');
        $ht = M('', null, $url);

		$sql = "select
			HOUSECODE
			,CHARGEMONTH
			,STANDARDNAME
			,CLOCKNOMBERS
			,PRICE
			,TOTAL_POINT
			,TOTAL_CHARGE
			,REALACCOUNT
			,case when sh.sfhhousecode is not null
	        then 0
	        else case when TOTAL_CHARGE >= REALACCOUNT or CHASTA = -1 then 0 else REALACCOUNT - TOTAL_CHARGE end
	        end SURPLUS
	        ,case when TOTAL_CHARGE-REALACCOUNT<=0 then 0 else TOTAL_CHARGE-REALACCOUNT end SUPPLEMENTPAY
			,BALANCE_FLAG
			,LAST_POINTNUMBER
			,POINTNUMBER
			,AREATOTAL
			,POINTTOTAL
			,COMMUNITYCODE
			,BUILDINGCODE
			from
			(
			SELECT
				p.HOUSECODE,
				p.STANDARDNAME,
				p.CHARGEMONTH,
				WM_CONCAT(p.CLOCKNUMBER) CLOCKNOMBERS,
				p.PRICE,
				SUM(NVL(p.TOTAL_POINT,0)) TOTAL_POINT,
				round(p.areaPrice * p.chargearea * 0.3, 2) + SUM(round(p.price * NVL(p.TOTAL_POINT,0), 2)) TOTAL_CHARGE,
				p.areaPrice * p.chargearea REALACCOUNT,
				case when p.BALANCE_FLAG = '0' then '应收未结' else '应收已结' end BALANCE_FLAG,
				WM_CONCAT(p.LAST_POINTNUMBER) LAST_POINTNUMBER,
				WM_CONCAT(p.POINTNUMBER) POINTNUMBER,
				round(p.areaPrice * p.chargearea * 0.3, 2) AREATOTAL,
				sum(round(p.price * NVL(p.TOTAL_POINT,0), 2)) POINTTOTAL,
				p.COMMUNITYCODE,
				p.BUILDINGCODE,
				p.CHASTA
			FROM
				(
				SELECT
				    T1.HOUSECODE,
				    T3.STANDARDNAME,
				    T1.CHARGEMONTH,
				    T1.CLOCKNUMBER,
				    T3.PRICE,
				    NVL(T1.TOTAL_POINT,0) TOTAL_POINT,
				    t4.price areaPrice,
				    t4.chargearea,
				    T1.BALANCE_FLAG,
				    t1.LAST_POINTNUMBER,
				    t1.POINTNUMBER,
				    T4.COMMUNITYCODE,
				    T4.BUILDINGCODE,
					sign(t4.nowcash - t4.realaccount) CHASTA
			  	FROM
					JL_POINT_TOTAL_FINAL T1,
					SF_STANDARD T3,
					SF_CHARGE T4
				WHERE
					T1.ITEMCODE = T3.ITEMCODE
				AND T1.STANDARDCODE = T3.STANDARDCODE

				AND T1.HOUSECODE = T4.HOUSECODE
				AND T1.ITEMCODE = 'E'
				AND T4.ITEMCODE = 'A'
				AND T1.CHARGEMONTH = T4.CHARGEMONTH
				) p
				GROUP BY p.HOUSECODE, p.STANDARDNAME, p.CHARGEMONTH, p.PRICE, p.areaPrice * p.chargearea, p.BALANCE_FLAG , round(p.areaPrice * p.chargearea * 0.3, 2), p.COMMUNITYCODE, p.BUILDINGCODE,p.CHASTA
			) m
			left join (
				select distinct c.HOUSECODE as sfhhousecode from FC_HOUSE a
				inner join SF_CHARGE B on a.HOUSECODE=B.HOUSECODE and B.ITEMCODE='A' and B.CHARGEMONTH='2017'
				inner join SF_DETAIL C
				on B.HOUSECODE=C.HOUSECODE and B.ITEMCODE=C.ITEMCODE and B.CHARGEMONTH=C.CHARGEMONTH and B.CLOCKNUMBER=C.CLOCKNUMBER and c.rollbackflag='0'
				and C.OPERATEDATE>B.OPERATEDATE
				inner join SF_BILL D on C.BILLCODE=D.BILLCODE and D.IMPRESTTYPE='收费'
				where a.CNSFFS='计量收费' and a.HOUSECODE not in (
				select housecode from FC_STOPHEATING where mon='2017' and STOPMARK is null
				)
			) SH ON M.HOUSECODE=SH.SFHHOUSECODE
			where CHARGEMONTH = '2017' and m.housecode='$housecode'";
        $list = $ht->query($sql);
        return $list[0];
    }



    /* 2.2.26	帐单明细-判断房间计费方式（面积/计量） */

    function getJffs($housecode)
    {
        $url = C('DB_ORACLE');
        $ht = M('', null, $url);
        $sql = " select   cnsffs ,CASE WHEN TO_CHAR(sysdate, 'MM') in( '04' , '05') THEN 'show' else 'hide' end showflag from fc_house where housecode='$housecode'";
        $list = $ht->query($sql);
        return $list[0];
    }

    /* 2.2.27	帐单明细-获取房间计费用量 */

    function getJlyl($housecode)
    {
        $url = C('DB_ORACLE');
        $ht = M('', null, $url);
        $sql = " select sum(TOTAL_POINT) TOTAL_POINT from jl_point_total_final where housecode='$housecode' and chargemonth=TO_CHAR(add_months(sysdate, -12),'yyyy')";
        $list = $ht->query($sql);
        return $list[0];
    }

	 /**
     * 判断水费状态
     */
    function getShuiStatus($housecode) {
		 $url = C('DB_ORACLE');
         $lszd = M('',null,$url);
		 $sql = "SELECT count(1) as cou FROM BI_EXCLUDE_HOUSE A INNER JOIN SF_ITEM B ON A.ITEMCODE = B.ITEMCODE WHERE HOUSECODE = '$housecode' and a.itemcode in ('B','M','N')";
		 $list = $lszd->query($sql);
         return $list;

	}

	/**
     * 判断燃气费状态
     */
    function getQiStatus($housecode) {
		 $url = C('DB_ORACLE');
         $lszd = M('',null,$url);
		 $sql = "SELECT count(1) as cou FROM BI_EXCLUDE_HOUSE A INNER JOIN SF_ITEM B ON A.ITEMCODE = B.ITEMCODE WHERE HOUSECODE = '$housecode' and a.itemcode in ('D','P','Q')";
		 $list = $lszd->query($sql);
         return $list;

	}

	/**
     * 判断供热费状态
     */
    function getReStatus($housecode) {
		 $url = C('DB_ORACLE');
         $lszd = M('',null,$url);
		 $sql = "SELECT count(1) as cou FROM BI_EXCLUDE_HOUSE A INNER JOIN SF_ITEM B ON A.ITEMCODE = B.ITEMCODE WHERE HOUSECODE = '$housecode' and a.itemcode in ('A')";
		 $list = $lszd->query($sql);
         return $list;

	}

    /**
     * 查询欠费信息
     */
    function getQfxxShui($housecode) {
        $url = C('DB_ORACLE');
        $lszd = M('',null,$url);
        $sql = "
 select * from (
 select
cpl.housecode,cpl.chargemonth,cpl.clocknumber,
sum(case when cpl.itemcode in ('B','M','N') then nvl(cpl.pointnumber,0) else 0 end) zls_yl,--自来水用量
sum(case when cpl.itemcode in ('B','M','N') then cpl.realaccount-cpl.nowcash+
  (case when cpl.latefeeend is null
    then
      nvl(PKG_CHARGE.getOVF_number(
        cpl.housecode,
        cpl.itemcode,
        cpl.chargemonth,
        cpl.CLOCKNUMBER,
        cpl.LATEFEESTARTD,
        case when cpl.latefeeend is null then TO_CHAR(SYSDATE,'YYYY-MM-DD') else cpl.latefeeend end,
          case when nvl(cpl.REALACCOUNT,0)<=nvl(cpl.NOWCASH,0) then 0 else nvl(cpl.REALACCOUNT,0)-nvl(cpl.NOWCASH,0) end,
          'hnrlAdmin'
        ),0)
  else
    nvl(cpl.LATEFEE,0)
  end)
else 0 end) zls_qf,--自来水欠费
sum(case when cpl.itemcode in ('D','P','Q') then nvl(cpl.pointnumber,0) else 0 end) trq_yl,--天然气用量
sum(case when cpl.itemcode in ('D','P','Q') then cpl.realaccount-cpl.nowcash+
  (case when cpl.latefeeend is null
    then
      nvl(PKG_CHARGE.getOVF_number(
        cpl.housecode,
        cpl.itemcode,
        cpl.chargemonth,
        cpl.CLOCKNUMBER,
        cpl.LATEFEESTARTD,
        case when cpl.latefeeend is null then TO_CHAR(SYSDATE,'YYYY-MM-DD') else cpl.latefeeend end,
          case when nvl(cpl.REALACCOUNT,0)<=nvl(cpl.NOWCASH,0) then 0 else nvl(cpl.REALACCOUNT,0)-nvl(cpl.NOWCASH,0) end,
          'hnrlAdmin'
        ),0)
  else
    nvl(cpl.LATEFEE,0)
  end)
else 0 end) trq_qf,--天然气欠费
sum(case when cpl.itemcode in ('A') then nvl(cpl.chargearea,0) else 0 end) gn_mj,--供暖面积
sum(case when cpl.itemcode in ('A') then
cpl.realaccount-
(case when (to_char(sysdate,'YYYY-MM-DD')>=cnt.startDate and  to_char(sysdate,'YYYY-MM-DD')<=nvl(cnt.endDate,'3000-12-31'))
  and not exists (select 1 from fc_stopheating fs where fs.housecode=cpl.housecode and fs.itemcode=cpl.itemcode and fs.mon=cpl.chargemonth
  and fs.clustercoding=cpl.clustercoding and fs.chid=cpl.clocknumber and fs.stopmark is null) and cpl.standardcode='A01'
  then
  round((cpl.totalaccount)*cnt.percent,2)  else 0 end
)-cpl.nowcash+
(case when cpl.latefeeend is null
    then
      nvl(PKG_CHARGE.getOVF_number(
        cpl.housecode,
        cpl.itemcode,
        cpl.chargemonth,
        cpl.CLOCKNUMBER,
        cpl.LATEFEESTARTD,
        case when cpl.latefeeend is null then TO_CHAR(SYSDATE,'YYYY-MM-DD') else cpl.latefeeend end,
          case when nvl(cpl.REALACCOUNT,0)
          -(case when (to_char(sysdate,'YYYY-MM-DD')>=cnt.startDate and  to_char(sysdate,'YYYY-MM-DD')<=nvl(cnt.endDate,'3000-12-31'))
              and not exists (select 1 from fc_stopheating fs where fs.housecode=cpl.housecode and fs.itemcode=cpl.itemcode and fs.mon=cpl.chargemonth
              and fs.clustercoding=cpl.clustercoding and fs.chid=cpl.clocknumber and fs.stopmark is null) and cpl.standardcode='A01'
              then
              round((cpl.totalaccount)*cnt.percent,2)  else 0 end
            )
          <=nvl(cpl.NOWCASH,0) then 0 else nvl(cpl.REALACCOUNT,0)-
          (case when (to_char(sysdate,'YYYY-MM-DD')>=cnt.startDate and  to_char(sysdate,'YYYY-MM-DD')<=nvl(cnt.endDate,'3000-12-31'))
            and not exists (select 1 from fc_stopheating fs where fs.housecode=cpl.housecode and fs.itemcode=cpl.itemcode and fs.mon=cpl.chargemonth
            and fs.clustercoding=cpl.clustercoding and fs.chid=cpl.clocknumber and fs.stopmark is null) and cpl.standardcode='A01'
            then
            round((cpl.totalaccount)*cnt.percent,2)  else 0 end
          )
          -nvl(cpl.NOWCASH,0) end,
          'hnrlAdmin'
        ),0)
  else
    nvl(cpl.LATEFEE,0)
  end)
else 0 end) gn_qf --供暖欠费
,to_char(sysdate,'hh24') hh24h
from sf_charge cpl,(select * from T_CONCESSIONS
  where to_char(sysdate,'YYYY-MM-DD')>=startDate
  and to_char(sysdate,'YYYY-MM-DD')<=nvl(endDate,'3000-12-31') and clusterCoding='hnrlAdmin') cnt
where --itemcode='M' and
cpl.chargemonth=cnt.chargemonth(+)
and cpl.ItemCode=cnt.ItemCode(+)
and cpl.housecode='$housecode'
and (cpl.realaccount-cpl.nowcash>0 or cpl.latefee-cpl.latefeecash>0)
group by cpl.housecode,cpl.chargemonth,cpl.clocknumber ) where  zls_qf>0 order by chargemonth";
        $list = $lszd->query($sql);
        return $list;
    }

    function getQfxxQi($housecode) {
        $url = C('DB_ORACLE');
        $lszd = M('',null,$url);
        $sql = "select * from (
select
cpl.housecode,cpl.chargemonth,cpl.clocknumber,
sum(case when cpl.itemcode in ('B','M','N') then nvl(cpl.pointnumber,0) else 0 end) zls_yl,--自来水用量
sum(case when cpl.itemcode in ('B','M','N') then cpl.realaccount-cpl.nowcash+
  (case when cpl.latefeeend is null
    then
      nvl(PKG_CHARGE.getOVF_number(
        cpl.housecode,
        cpl.itemcode,
        cpl.chargemonth,
        cpl.CLOCKNUMBER,
        cpl.LATEFEESTARTD,
        case when cpl.latefeeend is null then TO_CHAR(SYSDATE,'YYYY-MM-DD') else cpl.latefeeend end,
          case when nvl(cpl.REALACCOUNT,0)<=nvl(cpl.NOWCASH,0) then 0 else nvl(cpl.REALACCOUNT,0)-nvl(cpl.NOWCASH,0) end,
          'hnrlAdmin'
        ),0)
  else
    nvl(cpl.LATEFEE,0)
  end)
else 0 end) zls_qf,--自来水欠费
sum(case when cpl.itemcode in ('D','P','Q') then nvl(cpl.pointnumber,0) else 0 end) trq_yl,--天然气用量
sum(case when cpl.itemcode in ('D','P','Q') then cpl.realaccount-cpl.nowcash+
  (case when cpl.latefeeend is null
    then
      nvl(PKG_CHARGE.getOVF_number(
        cpl.housecode,
        cpl.itemcode,
        cpl.chargemonth,
        cpl.CLOCKNUMBER,
        cpl.LATEFEESTARTD,
        case when cpl.latefeeend is null then TO_CHAR(SYSDATE,'YYYY-MM-DD') else cpl.latefeeend end,
          case when nvl(cpl.REALACCOUNT,0)<=nvl(cpl.NOWCASH,0) then 0 else nvl(cpl.REALACCOUNT,0)-nvl(cpl.NOWCASH,0) end,
          'hnrlAdmin'
        ),0)
  else
    nvl(cpl.LATEFEE,0)
  end)
else 0 end) trq_qf,--天然气欠费
sum(case when cpl.itemcode in ('A') then nvl(cpl.chargearea,0) else 0 end) gn_mj,--供暖面积
sum(case when cpl.itemcode in ('A') then
cpl.realaccount-
(case when (to_char(sysdate,'YYYY-MM-DD')>=cnt.startDate and  to_char(sysdate,'YYYY-MM-DD')<=nvl(cnt.endDate,'3000-12-31'))
  and not exists (select 1 from fc_stopheating fs where fs.housecode=cpl.housecode and fs.itemcode=cpl.itemcode and fs.mon=cpl.chargemonth
  and fs.clustercoding=cpl.clustercoding and fs.chid=cpl.clocknumber and fs.stopmark is null) and cpl.standardcode='A01'
  then
  round((cpl.totalaccount)*cnt.percent,2)  else 0 end
)-cpl.nowcash+
(case when cpl.latefeeend is null
    then
      nvl(PKG_CHARGE.getOVF_number(
        cpl.housecode,
        cpl.itemcode,
        cpl.chargemonth,
        cpl.CLOCKNUMBER,
        cpl.LATEFEESTARTD,
        case when cpl.latefeeend is null then TO_CHAR(SYSDATE,'YYYY-MM-DD') else cpl.latefeeend end,
          case when nvl(cpl.REALACCOUNT,0)
          -(case when (to_char(sysdate,'YYYY-MM-DD')>=cnt.startDate and  to_char(sysdate,'YYYY-MM-DD')<=nvl(cnt.endDate,'3000-12-31'))
              and not exists (select 1 from fc_stopheating fs where fs.housecode=cpl.housecode and fs.itemcode=cpl.itemcode and fs.mon=cpl.chargemonth
              and fs.clustercoding=cpl.clustercoding and fs.chid=cpl.clocknumber and fs.stopmark is null) and cpl.standardcode='A01'
              then
              round((cpl.totalaccount)*cnt.percent,2)  else 0 end
            )
          <=nvl(cpl.NOWCASH,0) then 0 else nvl(cpl.REALACCOUNT,0)-
          (case when (to_char(sysdate,'YYYY-MM-DD')>=cnt.startDate and  to_char(sysdate,'YYYY-MM-DD')<=nvl(cnt.endDate,'3000-12-31'))
            and not exists (select 1 from fc_stopheating fs where fs.housecode=cpl.housecode and fs.itemcode=cpl.itemcode and fs.mon=cpl.chargemonth
            and fs.clustercoding=cpl.clustercoding and fs.chid=cpl.clocknumber and fs.stopmark is null) and cpl.standardcode='A01'
            then
            round((cpl.totalaccount)*cnt.percent,2)  else 0 end
          )
          -nvl(cpl.NOWCASH,0) end,
          'hnrlAdmin'
        ),0)
  else
    nvl(cpl.LATEFEE,0)
  end)
else 0 end) gn_qf --供暖欠费
,to_char(sysdate,'hh24') hh24h
from sf_charge cpl,(select * from T_CONCESSIONS
  where to_char(sysdate,'YYYY-MM-DD')>=startDate
  and to_char(sysdate,'YYYY-MM-DD')<=nvl(endDate,'3000-12-31') and clusterCoding='hnrlAdmin') cnt
where --itemcode='M' and
cpl.chargemonth=cnt.chargemonth(+)
and cpl.ItemCode=cnt.ItemCode(+)
and cpl.housecode='$housecode'
and (cpl.realaccount-cpl.nowcash>0 or cpl.latefee-cpl.latefeecash>0)
group by cpl.housecode,cpl.chargemonth,cpl.clocknumber ) where trq_qf>0 order by chargemonth ";
        $list = $lszd->query($sql);
        return $list;
    }

    function getQfxxRe($housecode) {
        $url = C('DB_ORACLE');
        $lszd = M('',null,$url);
        $sql = "select * from ( select
cpl.housecode,cpl.chargemonth,cpl.clocknumber,
sum(case when cpl.itemcode in ('B','M','N') then nvl(cpl.pointnumber,0) else 0 end) zls_yl,--自来水用量
sum(case when cpl.itemcode in ('B','M','N') then cpl.realaccount-cpl.nowcash+
  (case when cpl.latefeeend is null
    then
      nvl(PKG_CHARGE.getOVF_number(
        cpl.housecode,
        cpl.itemcode,
        cpl.chargemonth,
        cpl.CLOCKNUMBER,
        cpl.LATEFEESTARTD,
        case when cpl.latefeeend is null then TO_CHAR(SYSDATE,'YYYY-MM-DD') else cpl.latefeeend end,
          case when nvl(cpl.REALACCOUNT,0)<=nvl(cpl.NOWCASH,0) then 0 else nvl(cpl.REALACCOUNT,0)-nvl(cpl.NOWCASH,0) end,
          'hnrlAdmin'
        ),0)
  else
    nvl(cpl.LATEFEE,0)
  end)
else 0 end) zls_qf,--自来水欠费
sum(case when cpl.itemcode in ('D','P','Q') then nvl(cpl.pointnumber,0) else 0 end) trq_yl,--天然气用量
sum(case when cpl.itemcode in ('D','P','Q') then cpl.realaccount-cpl.nowcash+
  (case when cpl.latefeeend is null
    then
      nvl(PKG_CHARGE.getOVF_number(
        cpl.housecode,
        cpl.itemcode,
        cpl.chargemonth,
        cpl.CLOCKNUMBER,
        cpl.LATEFEESTARTD,
        case when cpl.latefeeend is null then TO_CHAR(SYSDATE,'YYYY-MM-DD') else cpl.latefeeend end,
          case when nvl(cpl.REALACCOUNT,0)<=nvl(cpl.NOWCASH,0) then 0 else nvl(cpl.REALACCOUNT,0)-nvl(cpl.NOWCASH,0) end,
          'hnrlAdmin'
        ),0)
  else
    nvl(cpl.LATEFEE,0)
  end)
else 0 end) trq_qf,--天然气欠费
sum(case when cpl.itemcode in ('A') then nvl(cpl.chargearea,0) else 0 end) gn_mj,--供暖面积
sum(case when cpl.itemcode in ('A') then
cpl.realaccount-
(case when (to_char(sysdate,'YYYY-MM-DD')>=cnt.startDate and  to_char(sysdate,'YYYY-MM-DD')<=nvl(cnt.endDate,'3000-12-31'))
  and not exists (select 1 from fc_stopheating fs where fs.housecode=cpl.housecode and fs.itemcode=cpl.itemcode and fs.mon=cpl.chargemonth
  and fs.clustercoding=cpl.clustercoding and fs.chid=cpl.clocknumber and fs.stopmark is null) and cpl.standardcode='A01'
  then
  round((cpl.totalaccount)*cnt.percent,2)  else 0 end
)-cpl.nowcash+
(case when cpl.latefeeend is null
    then
      nvl(PKG_CHARGE.getOVF_number(
        cpl.housecode,
        cpl.itemcode,
        cpl.chargemonth,
        cpl.CLOCKNUMBER,
        cpl.LATEFEESTARTD,
        case when cpl.latefeeend is null then TO_CHAR(SYSDATE,'YYYY-MM-DD') else cpl.latefeeend end,
          case when nvl(cpl.REALACCOUNT,0)
          -(case when (to_char(sysdate,'YYYY-MM-DD')>=cnt.startDate and  to_char(sysdate,'YYYY-MM-DD')<=nvl(cnt.endDate,'3000-12-31'))
              and not exists (select 1 from fc_stopheating fs where fs.housecode=cpl.housecode and fs.itemcode=cpl.itemcode and fs.mon=cpl.chargemonth
              and fs.clustercoding=cpl.clustercoding and fs.chid=cpl.clocknumber and fs.stopmark is null) and cpl.standardcode='A01'
              then
              round((cpl.totalaccount)*cnt.percent,2)  else 0 end
            )
          <=nvl(cpl.NOWCASH,0) then 0 else nvl(cpl.REALACCOUNT,0)-
          (case when (to_char(sysdate,'YYYY-MM-DD')>=cnt.startDate and  to_char(sysdate,'YYYY-MM-DD')<=nvl(cnt.endDate,'3000-12-31'))
            and not exists (select 1 from fc_stopheating fs where fs.housecode=cpl.housecode and fs.itemcode=cpl.itemcode and fs.mon=cpl.chargemonth
            and fs.clustercoding=cpl.clustercoding and fs.chid=cpl.clocknumber and fs.stopmark is null) and cpl.standardcode='A01'
            then
            round((cpl.totalaccount)*cnt.percent,2)  else 0 end
          )
          -nvl(cpl.NOWCASH,0) end,
          'hnrlAdmin'
        ),0)
  else
    nvl(cpl.LATEFEE,0)
  end)
else 0 end) gn_qf --供暖欠费
,to_char(sysdate,'hh24') hh24h
from sf_charge cpl,(select * from T_CONCESSIONS
  where to_char(sysdate,'YYYY-MM-DD')>=startDate
  and to_char(sysdate,'YYYY-MM-DD')<=nvl(endDate,'3000-12-31') and clusterCoding='hnrlAdmin') cnt
where --itemcode='M' and
cpl.chargemonth=cnt.chargemonth(+)
and cpl.ItemCode=cnt.ItemCode(+)
and cpl.housecode='$housecode'
and (cpl.realaccount-cpl.nowcash>0 or cpl.latefee-cpl.latefeecash>0)
group by cpl.housecode,cpl.chargemonth,cpl.clocknumber ) where gn_qf>0 order by chargemonth";
        $list = $lszd->query($sql);
        return $list;
    }

	/* 2.2.29	张单明细-采暖时间 */

    function getCnsj()
    {
		$url = 'http://10.105.15.2/TjstcWebImpl/GetRecoveryYearServlet';
		$post_data= '';
		$list = json_decode($this->curl_post($url,$post_data));
		return $list;

        /*$url = C('DB_ORACLE');
        $ht = M('', null, $url);
        $sql = "select itemcode, case when to_char(sysdate,'MM')>= financemonth then to_char(sysdate,'YYYY')
	else to_char(to_number(to_char(sysdate,'YYYY'))-1) end year
	from sf_item where itemcode='A'";
        $list = $ht->query($sql);
        return $list[0];*/
    }
	/*投诉建议在线报修*/
	/*查询涉及行业*/
	function GetInvolveIndustryServlet()
    {
		$url = 'http://10.105.15.2/TjstcWebImpl/GetInvolveIndustryServlet';
		$post_data ="";
		$list = json_decode($this->curl_post($url,$post_data));
		return $list;
	}
	/*根据涉及行业名称查询涉及种类*/	
	function GetInvolveTypeServlet()
    {
		$url = 'http://10.105.15.2/TjstcWebImpl/GetInvolveTypeServlet';
		$post_data['vINVOLVEINDUSTRY'] ="$involveindustry";
		$list = json_decode($this->curl_post($url,$post_data));
		return $list;
	}
	/*根据涉及种类ID查询办理类别、工单类别*/	
	function GetOnlineRepairTypeServlet()
    {
		$url = 'http://10.105.15.2/TjstcWebImpl/GetOnlineRepairTypeServlet';
		$post_data['vINVOLVETYPE'] ="$involvetype";
		$list = json_decode($this->curl_post($url,$post_data));
		return $list;
	}
	 /*2.2.30 当期账单 */
    function getNowData($housecode)
    {
		$url = 'http://10.105.15.2/TjstcWebImpl/GetCurrentPeriodBillServlet';
		$post_data['vHOUSECODE'] = $housecode;
		$list = json_decode($this->curl_post($url,$post_data));
		return $list;
	}

	 /*供暖账单*/
    function getGrData($housecode)
    {
		$url = 'http://10.105.15.2/TjstcWebImpl/GetCurrentPeriodBill_AServlet';
		$post_data['vHOUSECODE'] = $housecode;
		$list = json_decode($this->curl_post($url,$post_data));
		return $list;
	}

	 /*百分比*/
    function getBFB($housecode,$yonglian)
    {
		$url = 'http://10.105.15.2/TjstcWebImpl/GetHeatMeteringProportionServlet';
		$post_data['vHOUSECODE'] = $housecode;
		$post_data['vPOINTNUMBER'] = $yonglian;
		$list = json_decode($this->curl_post($url,$post_data));
		return $list;
	}
	//查看缴费历史记录
	function  lsjf($housecode,$paymentchannel,$startdate,$enddate){
         $url = 'http://10.105.15.2/TjstcWebImpl/CheckHistoryPaymentRecordServlet';
      //   $post_data['vHOUSECODE'] = $housecode;
	//	 $post_data['vPAYMENTCHANNEL'] = $paymentchannel;
	//	 $post_data['vSTARTDATE'] = $startdate;
  //		 $post_data['vENDDATE'] =$enddate;
		  $post_data['vHOUSECODE'] = "5030-010-01-04-01";
		 $post_data['vPAYMENTCHANNEL'] = "在线缴费";
		 $post_data['vSTARTDATE'] = "2018-09-01";
		 $post_data['vENDDATE'] ="2018-11-09";
		 //$list = json_decode($this->curl_post($url,$post_data));
		 return $post_data;

	}

	/*2.2.31 单价 */
    function getPriceData($housecode)
    {
		$url = 'http://10.105.15.2/TjstcWebImpl/GetThisYearTotalAmountServlet';
		$post_data['vHOUSECODE'] = $housecode;
		$list = json_decode($this->curl_post($url,$post_data));
		return $list;
	}

	/*2.2.32 初始读数 */
    function getNum($housecode)
    {
		$url = 'http://10.105.15.2/TjstcWebImpl/GetThisYearInitialNumberServlet';
		$post_data['vHOUSECODE'] = $housecode;
		$list = json_decode($this->curl_post($url,$post_data));
		return $list;
	}

	function maskPhoneNumber($phone) {
		if (empty($phone)) {
			return "";
		}

		return substr_replace($phone, '****', 3, 4);
	}
	/**
	 * 根据房间编号查询我的业务表列
	 *
	 * @param $housecodes 房间编号(逗号分割)
	 * @return HOUSECODE(房间编号),OWNERNAME(业主姓名),APPLYDATE(申请时间),BUSINESSTYPE(业务类型),EXAMINESTATUS(办理状态),SLSX(受理时限),BUSINESSCODE(业务编号)
	 */
	function callMyBusinessListInterface($housecodes)
    {
        $url = C('CALL_URL') . '/GetMyBusinessListServlet';
		$post_data['vUID'] = $housecodes;
		$list = json_decode($this->curl_post($url,$post_data));
		return $list;
    }

    function callMyBusinessListSwInterface($housecodes)
    {
        $url = C('CALL_URL') . '/GetMyBusinessListSwServlet';
        $post_data['vUID'] = $housecodes;
        $list = json_decode($this->curl_post($url,$post_data));
        return $list;
    }
	function callMyBusinessListHBServlet($housecodes)
    {
        $url = C('CALL_URL') . '/GetMyBusinessListHBServlet';
        $post_data['vUID'] = $housecodes;
        $list = json_decode($this->curl_post($url,$post_data));
        return $list;
    }

	/*智能物回后台数据接口
	*获取token
	*/
    function getJfToken($UserName,$UserPass,$UserHost)
    {
		$url = 'http://103.233.5.21:8076/AppService.svc/stcapi/gettoken';
		$post_data['UserName'] = $UserName;
		$post_data['UserPass'] = $UserPass;
		$post_data['UserHost'] = $UserHost;

		$postUrl = $url;
        $curlPost = json_encode($post_data);
		$header = array(
			'Content-Type: application/json;charset=UTF-8',
			'Content-Length: ' . strlen($curlPost)
		);
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch);//运行curl
        curl_close($ch);

		$list = json_decode($data,true);
		return $list;
	}

	/*智能物回后台数据接口
	*获取积分
	*/
    function getJf($CustIDCard,$UserToken,$UserHost)
    {
		$url = 'http://103.233.5.21:8076/AppService.svc/stcapi/querybonus';
		$post_data['CustIDCard'] = $CustIDCard;
		$post_data['UserToken'] = $UserToken;
		$post_data['UserHost'] = $UserHost;

		$postUrl = $url;
        $curlPost = json_encode($post_data);
		$header = array(
			'Content-Type: application/json;charset=UTF-8',
			'Content-Length: ' . strlen($curlPost)
		);
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch);//运行curl
        curl_close($ch);

		$list = json_decode($data,true);
		return $list;
	}

	/*智能物回后台数据接口
	*获取积分历史
	*/
    function getJfHistory($CustIDCard)
    {

		$url = 'http://103.233.5.21:8076/AppService.svc/stcapi/consumptioninfo';
		$post_data['CustIDCard'] = $CustIDCard;

		$postUrl = $url;
        $curlPost = json_encode($post_data);
		$header = array(
			'Content-Type: application/json;charset=UTF-8',
			'Content-Length: ' . strlen($curlPost)
		);
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch);//运行curl
        curl_close($ch);

		$list = json_decode($data,true);
		return $list;
	}

    /*智能物回后台数据接口
    *预约接口
    */
    function revNow($Phone,$CardNo,$Class1,$Class2,$CastTime,$Note,$vHOUSECODE,$vOWNERNAME,$vBUSINESSSOURCE,$filebase64)
    {
		$url = C('CALL_URL') . '/SubmitAddSubscribeServlet';
        $post_data['vPhone'] = $Phone;
        $post_data['vCardNo'] = $CardNo;
        $post_data['vClass1'] = $Class1;
        $post_data['vClass2'] = $Class2;
        $post_data['vCastTime'] = $CastTime;
        $post_data['vNote'] = $Note;
		$post_data['vHOUSECODE'] = $vHOUSECODE;
		$post_data['vOWNERNAME'] = $vOWNERNAME;
		$post_data['vBUSINESSSOURCE'] = $vBUSINESSSOURCE;
		$post_data['filebase64'] = $filebase64;
        $list = json_decode($this->curl_post($url,$post_data),true);
        return $list;
    }

	function stopstatus($housecodes)
    {
        $url = C('CALL_URL') . '/GetThisYearStopStatusServlet';
		$post_data['vHOUSECODE'] = $housecodes;
		$list = json_decode($this->curl_post($url,$post_data));
		return $list;
    }
	function stopstatusrq($housecodes)
    {
        $url = C('CALL_URL') . '/CheckGasRepeatServlet';
		$post_data['vHOUSECODE'] = $housecodes;
		$list = json_decode($this->curl_post($url,$post_data));
		return $list;
    }
	function stopstatushz($housecodes)
    {
        $url = C('CALL_URL') . '/GetHouseItemStateServlet';
		$post_data['vHOUSECODE'] = $housecodes;
		$list = json_decode($this->curl_post($url,$post_data));
		return $list;
    }

    function chargePhone($housecodes,$phone,$owername,$laoyuan)
    {
        $url = C('CALL_URL') . '/ChangeContactInfoServlet';
        $post_data['vHOUSECODE'] = $housecodes;
        $post_data['vMOBILEPHONE'] = $phone;
        $post_data['vOWNERNAME'] = $owername;
        $post_data['vBUSINESSSOURCE'] = $laoyuan;
        $list = json_decode($this->curl_post($url,$post_data));
        return $list;
    }

    function chargeSwPhone($housecodes,$phone,$owername,$laoyuan)
    {
        $url = C('CALL_URL') . '/ChangeContactInfoSwServlet';
        $post_data['vHOUSECODE'] = $housecodes;
        $post_data['vMOBILEPHONE'] = $phone;
        $post_data['vOWNERNAME'] = $owername;
        $post_data['vBUSINESSSOURCE'] = $laoyuan;
        $list = json_decode($this->curl_post($url,$post_data));
        return $list;
    }
    function SubmitRecoveryProcessServlet($type,$housecodes,$owername,$month,$laiyuan,$fileBase64)
    {
        $url = C('CALL_URL') . '/SubmitRecoveryProcessServlet';
        $post_data['vBUSINESSTYPE'] = $type;
        $post_data['vHOUSECODE'] = $housecodes;
        $post_data['vOWNERNAME'] = $owername;
        $post_data['vCHARGEMONTH'] = $month;
        $post_data['vBUSINESSSOURCE'] = $laiyuan;
        $post_data['fileBase64'] = $fileBase64;
        $list = json_decode($this->curl_post($url,$post_data));
        return $list;
    }
	function ApplyBindHouseServlet($housecodes,$owername,$papercode,$laiyuan,$uid,$fileBase64)
    {
        $url = C('CALL_URL') . '/ApplyBindHouseServlet';
        $post_data['vHOUSECODE'] = $housecodes;
        $post_data['vOWNERNAME'] = $owername;
        $post_data['vPAPERCODE'] = $papercode;
        $post_data['vBUSINESSSOURCE'] = $laiyuan;
		$post_data['vUID'] = $uid;
        $post_data['fileBase64'] = $fileBase64;
        $list = json_decode($this->curl_post($url,$post_data));
        return $list;
    }
    function SubmitBankProcessServlet($vBUSINESSTYPE,$vHOUSECODE,$vOWNERNAME,$vBANK,$vBANKNUMBER,$vCARDENDDATE,$vBUSINESSSOURCE,$fileBase64,$fileName)
    {
        $url = C('CALL_URL') . '/SubmitBankProcessServlet';
        $post_data['vBUSINESSTYPE'] = $vBUSINESSTYPE;
        $post_data['vHOUSECODE'] = $vHOUSECODE;
        $post_data['vOWNERNAME'] = $vOWNERNAME;
        $post_data['vBANK'] = $vBANK;
        $post_data['vBANKNUMBER'] = $vBANKNUMBER;
        $post_data['vCARDENDDATE'] = $vCARDENDDATE;
        $post_data['vBUSINESSSOURCE'] = $vBUSINESSSOURCE;
        $post_data['fileBase64'] = $fileBase64;
		$post_data['fileName'] = $fileName;
        $list = json_decode($this->curl_post($url,$post_data));
        return $list;
    }
    function SubmitBankProcessSwServlet($vBUSINESSTYPE,$vHOUSECODE,$vOWNERNAME,$vBANK,$vBANKNUMBER,$vCARDENDDATE,$vBUSINESSSOURCE,$fileBase64,$fileName)
    {
        $url = C('CALL_URL') . '/SubmitBankProcessSwServlet';
        $post_data['vBUSINESSTYPE'] = $vBUSINESSTYPE;
        $post_data['vHOUSECODE'] = $vHOUSECODE;
        $post_data['vOWNERNAME'] = $vOWNERNAME;
        $post_data['vBANK'] = $vBANK;
        $post_data['vBANKNUMBER'] = $vBANKNUMBER;
        $post_data['vCARDENDDATE'] = $vCARDENDDATE;
        $post_data['vBUSINESSSOURCE'] = $vBUSINESSSOURCE;
        $post_data['fileBase64'] = $fileBase64;
		$post_data['fileName'] = $fileName;
        $list = json_decode($this->curl_post($url,$post_data));
        return $list;
    }
    function GetMyBusinessDetailServlet($vBUSINESSCODE,$vBUSINESSTYPE)
    {
        $url = C('CALL_URL') . '/GetMyBusinessDetailServlet';
        $post_data['vBUSINESSCODE'] = $vBUSINESSCODE;
        $post_data['vBUSINESSTYPE'] = $vBUSINESSTYPE;
        $list = json_decode($this->curl_post($url,$post_data));
        return $list;
    }
    function GetMyBusinessDetailSwServlet($vBUSINESSCODE,$vBUSINESSTYPE)
    {
        $url = C('CALL_URL') . '/GetMyBusinessDetailSwServlet';
        $post_data['vBUSINESSCODE'] = $vBUSINESSCODE;
        $post_data['vBUSINESSTYPE'] = $vBUSINESSTYPE;
        $list = json_decode($this->curl_post($url,$post_data));
        return $list;
    }
    function SubmitLadderProcessServlet($vBUSINESSTYPE,$vHOUSECODE,$vOWNERNAME,$vADDNUMPEOPLE,$vBUSINESSSOURCE,$fileBase64)
    {
        $url = C('CALL_URL') . '/SubmitLadderProcessServlet';
        $post_data['vBUSINESSTYPE'] = $vBUSINESSTYPE;
        $post_data['vHOUSECODE'] = $vHOUSECODE;
        $post_data['vOWNERNAME'] = $vOWNERNAME;
        $post_data['vADDNUMPEOPLE'] = $vADDNUMPEOPLE;
        $post_data['vBUSINESSSOURCE'] = $vBUSINESSSOURCE;
        $post_data['fileBase64'] = $fileBase64;
        $list = json_decode($this->curl_post($url,$post_data));
        return $list;
    }

	 function SubmitZssOpenSwServlet($vBUSINESSTYPE,$vHOUSECODE,$vOWNERNAME,$vWEEK,$vTIMESLOT,$vBUSINESSSOURCE,$fileBase64,$fileName)
    {
        $url = C('CALL_URL') . '/SubmitZssOpenSwServlet';
        $post_data['vBUSINESSTYPE'] = $vBUSINESSTYPE;
        $post_data['vHOUSECODE'] = $vHOUSECODE;
        $post_data['vOWNERNAME'] = $vOWNERNAME;
        $post_data['vWEEK'] = $vWEEK;
        $post_data['vTIMESLOT'] = $vTIMESLOT;
        $post_data['vBUSINESSSOURCE'] = $vBUSINESSSOURCE;
        $post_data['fileBase64'] = $fileBase64;
		$post_data['fileName'] = $fileName;
        $list = json_decode($this->curl_post($url,$post_data));
        return $list;
    }

		 function SubmitZssCloseSwServlet($vBUSINESSTYPE,$vHOUSECODE,$vOWNERNAME,$vWEEK,$vTIMESLOT,$vBANKCARDINFO,$vBUSINESSSOURCE,$fileBase64)
    {
        $url = C('CALL_URL') . '/SubmitZssCloseSwServlet';
        $post_data['vBUSINESSTYPE'] = $vBUSINESSTYPE;
        $post_data['vHOUSECODE'] = $vHOUSECODE;
        $post_data['vOWNERNAME'] = $vOWNERNAME;
        $post_data['vZSSCLOSEDATE'] = $vWEEK;
        $post_data['vISPRESTORE'] = $vTIMESLOT;
		$post_data['vBANKCARDINFO'] = $vBANKCARDINFO;
        $post_data['vBUSINESSSOURCE'] = $vBUSINESSSOURCE;
        $post_data['fileBase64'] = $fileBase64;
        $list = json_decode($this->curl_post($url,$post_data));
        return $list;
    }
    function GetHouseChargemonthServlet($vHOUSECODE,$vITEMCODE)
    {
        $url = C('CALL_URL') . '/GetHouseChargemonthServlet';
        $post_data['vHOUSECODE'] = $vHOUSECODE;
        $post_data['vITEMCODE'] = $vITEMCODE;
        $list = json_decode($this->curl_post($url,$post_data));
        return $list;
    }
	function GetHouseChargemonthAServlet($vHOUSECODE)
    {
        $url = C('CALL_URL') . '/GetHouseChargemonthAServlet';
        $post_data['vHOUSECODE'] = $vHOUSECODE;
        $list = json_decode($this->curl_post($url,$post_data));
        return $list;
    }
	//取供热仪表盘数据
	function GetHeatMeteringDashboardServlet($housecode,$PD)
	{
		  $url = 'http://10.105.15.2/TjstcWebImpl/GetHeatMeteringDashboardServlet';
		$post_data['vHOUSECODE'] = $housecode;
       $post_data['vPD'] = $PD;
		$list = json_decode($this->curl_post($url,$post_data));
		return $list;

	}
	function GetHeatMeteringMonthlyDosageServlet($vHOUSECODE,$vCHARGEMONTH,$vPD)
	{
		  $url = 'http://10.105.15.2/TjstcWebImpl/GetHeatMeteringMonthlyDosageServlet';
		$post_data['vHOUSECODE'] = $vHOUSECODE;
		$post_data['vCHARGEMONTH'] = $vCHARGEMONTH;
		$post_data['vPD'] =$vPD;
		$list = json_decode($this->curl_post($url,$post_data));
		return $list;

	}
	function SubmitGasOpeningApplyServlet($vBUSINESSTYPE,$vHOUSECODE,$vOWNERNAME,$vAPPLYTIME,$vPHONE,$vBUSINESSSOURCE)
    {
        $url = C('CALL_URL') . '/SubmitGasOpeningApplyServlet';
        $post_data['vBUSINESSTYPE'] = $vBUSINESSTYPE;
        $post_data['vHOUSECODE'] = $vHOUSECODE;
        $post_data['vOWNERNAME'] = $vOWNERNAME;
        $post_data['vAPPLYTIME'] = $vAPPLYTIME;
        $post_data['vPHONE'] = $vPHONE;
        $post_data['vBUSINESSSOURCE'] = $vBUSINESSSOURCE;
        $list = json_decode($this->curl_post($url,$post_data));
        return $list;
    }

}


?>
