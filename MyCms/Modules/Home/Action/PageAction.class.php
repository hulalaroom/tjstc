<?php

class PageAction extends HomeAction
{
    /**
     * 共通设置顶部导航项目高亮显示
     */
    public function _initialize() {
        parent::_initialize();
        // 服务公告id
        $this->assign('topid', '20');
    }

    public function _empty()
    {
        $this->redirect("Public/404");
    }

	//单页内容页
    public function index()
    {
        $id = intval($this->_param('id'));

        if ($id <= 0) {
            $this->redirect("Public/404");
        }
        $where['id'] = $id;
        D('Page')->where($where)->setInc('click');
        $list = D('Page')->where($where)->relation(true)->find();
        if (empty($list)) {
            $this->redirect("Public/404");
        }
        /*$this->pid = D('Cate')->where('id=' . $list['cat_id'])->getField('pid'); //左侧导航
        $this->nav = $this->nav($this->cate, $list['cat_id'], $id); //当前位置*/
        $p = get_parents($this->cate, $list['cat_id']);
        $this->topid = $p[0]['id']; //顶部导航选中效果
        $this->assign($list);
        $this->display();
    }


    //自助服务
    public function zizhu()
    {
        $this->check();
        $this->id = intval($this->_param('id'));
        $this->cat = get_catarray($this->cate, 'child', $this->id);

        $this->nav = $this->nav($this->cate, $this->id, ''); //当前位置
        $p = get_parents($this->cate, $this->id);
        if (empty($p)) {
            $this->redirect("Public/404");
        }
        $this->topid = $p[0]['id']; //顶部导航选中效果
        $this->display();
    }

	//在线查询
    public function zxcx()
    {
        $this->check();

        $this->id = intval($this->_param('id'));

        $this->cat = get_catarray($this->cate, 'child', $this->id);

        $this->nav = $this->nav($this->cate, $this->id, ''); //当前位置

        $p = get_parents($this->cate, $this->id);
        if (empty($p)) {
            $this->redirect("Public/404");
        }

        /* 接口 */
       $houselist = R('User/fjbd', array($_SESSION['uid']));
	   $houseCode = $_POST['houseCode'];
	   $age = $_POST['year'];
	    if(empty($age)) {
			$age = '';
		}
		if($_POST['houseCode'] == '' || $_POST['houseCode'] == NULL){
			$houseCode = $houselist[0]['houseCode'];
		}

		$time = time();
        $startMonth = intval(date("Y", $time) . '01');
        //本年当前时间
        $endMonth = intval(date("Ym", $time));
        $startTime = "";
		$charlist = array();
		$char = array();
		$char1 = array();
		$char2 = array();
		$j=0;
		$k=0;
        //本年当前时间
        $endTime = date("Y-m", $time);
		if($this->id == 88){
		//本年当前时间
		$endTime = date("Y", $time);
			$itemCode = 'A'.','.'E';
		}
		else if($this->id == 82){
			$itemCode = 'D';
		}
		else{
			$itemCode = 'B';
		}
        /* 费用 */
		if($this->id == 88){
			$char[] = R('Api/getReData', array($houseCode,$itemCode));
		}
		else{
			$char[] = R('Api/getLastData', array($houseCode,$itemCode,$age));
		}
        //$charlist = R('Api/getChargeHistory', array($houseCode, $itemCode, $startTime, $endTime));
		foreach($char as $key=>$value) {
			foreach($value as $v){
				$charlist[$j]['NOWCASH']=$v['NOWCASH'];
				$charlist[$j]['REALACCOUNT']=$v['REALACCOUNT'];
				$charlist[$j]['QF']=$v['QF'];
				$charlist[$j]['CHARGEMONTH']=$v['CHARGEMONTH'];
				$charlist[$j]['ITEMCODE']=$v['ITEMCODE'];
				$charlist[$j]['ADDRESS']=$v['ADDRESS'];
				$j++;
			}
		}
		$fee_count = "";
		$arr = array();
		/* 费用总计 */
		foreach($charlist as $key=>$value){
			$arr[$key]['address'] = $value['ADDRESS'];
			$arr[$key]['chargeMonth'] = $value['CHARGEMONTH'];
			$arr[$key]['itemName'] = $value['ITEMCODE'];
			$arr[$key]['cash'] = $value['REALACCOUNT'];
			$fee_count += $value['REALACCOUNT'];
		}
		$this->year = date('Y', time());
		$this->age = $age;
		$this->code = $houseCode;
        $this->assign('houselist', $houselist);
        $this->assign('charlist', $arr);
		$this->assign('fee_count', $fee_count);
		$this->topid = $p[0]['id']; //顶部导航选中效果
        $this->display();
    }


    //在线查询——基础信息
    public function jcxx()
    {
        $this->check();
        $this->id = intval($this->_param('id'));
        $this->pid = D('Cate')->where('id=' . $this->id)->getField('pid'); //左侧导航
        $this->nav = $this->nav($this->cate, $this->id, ''); //当前位置
        $p = get_parents($this->cate, $this->id);
        if (empty($p)) {
            $this->redirect("Public/404");
        }
        //$this->topid = $p[0]['id']; //顶部导航选中效果
        /* 查找已绑定的房间 */
        $this->houselist = R('User/fjbd', array($_SESSION['uid']));

        $this->fjxx = R('Api/getHouse', array($this->houselist[0]['houseCode']));
		$this->assign('housedetail', $this->fjxx);
	$this->topid = $p[0]['id']; //顶部导航选中效果
        $this->display();
    }

    //在线查询——历史费用
    public function lsfy()
    {
        $this->check();
		$houselist = R('User/fjbd', array($_SESSION['uid']));//用户绑定房间
		if(empty($houselist)) {
			$this->error('您没有绑定房间，不能进行在线查询!', '/index.php?s=/user/bind.html');
		}
        $this->id = intval($this->_param('id'));
        $this->pid = D('Cate')->where('id=' . $this->id)->getField('pid'); //左侧导航
        $this->nav = $this->nav($this->cate, $this->id, ''); //当前位置
        $p = get_parents($this->cate, $this->id);
        if (empty($p)) {
            $this->redirect("Public/404");
        }

        /* 能源类型 */
        //$this->powertype = R('Api/getPowerType');
        $this->now = time();
		/*start update by wangshipeng 2014.10.28*/
		//当月明细
		$houselist = R('User/fjbd', array($_SESSION['uid']));
		$count = count($houselist);//统计数组长度
		$arr = array();
		$accountTime = null ;
		foreach($houselist as $key=>$val) {
			$list = R('Api/getOweCharge', array($val['houseCode']));
			//当期账单时间
			if(!empty($list)){
				$accountTime = $list[0]['CHARGEMONTH'];
			}
			$arr[$key]['CHARGEMONTH'] = $accountTime;
			$rjldj = number_format($list[0]['RJLDJ'],4);
			$arr[$key]['DNCNF'] = $list[0]['DNCNF'];
			$arr[$key]['CNFYL'] = $list[0]['CNFYL'];
			$arr[$key]['ZQNDCNFZJ'] =  $list[0]['ZQNDCNFZJ'];
			$arr[$key]['ZQNDCNF'] = $list[0]['ZQNDCNF'];
			$arr[$key]['ZQNDCNFZNJ'] = $list[0]['ZQNDCNFZNJ'];
			$arr[$key]['DYSF'] = $list[0]['DYSF'];
			$arr[$key]['SFYL'] = $list[0]['SFYL'];
			$arr[$key]['ZQYSFZJ'] = $list[0]['ZQYSFZJ'] ;
			$arr[$key]['ZQYSF'] = $list[0]['ZQYSF'];
			$arr[$key]['ZQYSFZNJ'] = $list[0]['ZQYSFZNJ'];
			$arr[$key]['DYRQF'] = $list[0]['DYRQF'];
			$arr[$key]['RQYL'] = $list[0]['RQYL'];
			$arr[$key]['ZQYRQFZJ'] = $list[0]['ZQYRQFZJ'] ;
			$arr[$key]['ZQYRQF'] = $list[0]['ZQYRQF'];
			$arr[$key]['ZQYRQFZNJ'] = $list[0]['ZQYRQFZNJ'];
			$arr[$key]['DQSUM'] = $list[0]['DYSF']+$list[0]['DYRQF']+$list[0]['DNCNF']+$list[0]['DYRJLFY'];

			if($rjldj != 0){
				$arr[$key]['DQSUM'] = $list[0]['DYSF']+$list[0]['DYRQF'];
			}
			else{
				$arr[$key]['DQSUM'] = $list[0]['DYSF']+$list[0]['DYRQF']+$list[0]['DNCNF']+$list[0]['DYRJLFY'];
			}
			$arr[$key]['ZNJSUM'] = $list[0]['ZQYSFZNJ']+$list[0]['ZQYRQFZNJ']+$list[0]['ZQNDCNFZNJ'];

			//$arr[$key]['HOUSENAME'] = $val['houseName'];
			/*-- update wangshipeng 2015.-3.12   start*/
			$value = R('Api/getAddress', array($val['houseCode']));
			$arr[$key]['HOUSENAME'] = $value[0]['ADDRESS'];
			/*-- update wangshipeng 2015.-3.12   end*/


			/*-- 供热分开显示   */
			$arr[$key]['GRMJDJ'] = $list[0]['GRMJDJ'];
			$arr[$key]['ZLSDJ'] = $list[0]['ZLSDJ'];
			$arr[$key]['TRQDJ'] = $list[0]['TRQDJ'];
			$arr[$key]['RJLDJ'] = number_format($list[0]['RJLDJ'],4);
			$arr[$key]['RJLYL'] = $list[0]['RJLYL'];
			$arr[$key]['ZQYRJLFY'] = $list[0]['ZQYRJLFY'];
			$arr[$key]['ZQYRJLZNJ'] = $list[0]['ZQYRJLZNJ'];

			$arr[$key]['DYRJLFY'] = $list[0]['DYRJLFY'];
			$arr[$key]['ZQYRJLZJ'] = $list[0]['ZQYRJLZJ'];
			//$arr[$key]['RJLSUM'] = $list[0]['ZQYRJLFY']+$list[0]['ZQYRJLZNJ']+$list[0]['ZQYRJLFY']+$list[0]['DYRJLFY'];
			if($rjldj != 0){
				$arr[$key]['YJJESUM'] = $arr[$key]['ZQYSFZJ']+$arr[$key]['ZQYRQFZJ'];
			}
			else{
				$arr[$key]['YJJESUM'] = $arr[$key]['ZQNDCNFZJ']+$arr[$key]['ZQYSFZJ']+$arr[$key]['ZQYRQFZJ']+$arr[$key]['ZQYRJLZJ'];
			}
			/*  --*/
		}

		/*-- 修改显示日期   */
		$tempaa = $accountTime."01";
		$startTime =date('Y年m',strtotime("$tempaa -1 month "))."月21日";
		$accountYear = substr($accountTime,0,4);
		$accountMonth = substr($accountTime,4,2);
		$endTime = $accountYear . '年' . $accountMonth . '月' .'20日';
		/* 修改显示日期   --*/
        foreach ($houselist as $v) {
            $hlist[] = $v['houseCode'];
			$hlist[] = $v['houseName'];
        }
		//历史账单
		$arr1 = array();
		$houseCode = $_POST['houseCode'];
		$itemCode = $_POST['itemCode'];
		$age = $_POST['year'];
		$moon = $_POST['month'];
		$chargeMonth = $_POST['year'] . $_POST['month'];
		if (empty($chargeMonth)) {
			$chargeMonth = date('Y', time()) . date('m', time());
		}
		if($_POST['houseCode'] == '' || $_POST['houseCode'] == NULL){
			$houseCode = $houselist[0]['houseCode'];
			$moon = date('Y',time());
		}
		if($accountYear == ''){
			$this->notice = '';
		}
		else{
			$this->notice = '(结算周期' . $startTime ."-". $endTime . ')';
		}
        $this->houselist = $houselist;
		$this->historylist = $arr1;
		$this->feelist = $arr;
		$this->code = $houseCode;
		$this->itemCode = $itemCode;
		$this->age = $age;
		$this->moon = $moon;
        $this->year = date('Y', time());
        $this->month = date('m', time());
        $this->monthList = array("01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12");
		/*end update by wangshipeng 2014.10.28*/

        /*-- 增加显示当期账号号*/
        $this-> current = $accountTime;
        /* --*/

        $this->topid = $p[0]['id']; //顶部导航选中效果
        $this->display();
    }




    //在线查询——历史费用
    public function lsfy2()
    {
        $this->check();
		$houselist = R('User/fjbd', array($_SESSION['uid']));//用户绑定房间
		if(empty($houselist)) {
			$this->error('您没有绑定房间，不能进行在线查询!', '/index.php?s=/user/bind.html');
		}
        $this->id = intval($this->_param('id'));
        $this->pid = D('Cate')->where('id=' . $this->id)->getField('pid'); //左侧导航
        $this->nav = $this->nav($this->cate, $this->id, ''); //当前位置
        $p = get_parents($this->cate, $this->id);
        if (empty($p)) {
            $this->redirect("Public/404");
        }

        /* 能源类型 */
        $this->now = time();
		/*start update by wangshipeng 2014.10.28*/
		//当月明细
		$houselist = R('User/fjbd', array($_SESSION['uid']));
		$count = count($houselist);//统计数组长度
		$arr = array();
		$rjl = array();
		$fjrjl = array();
		$zqlj = array();
		$zqljSum = array();
		$zqljCount = array();
		$accountTime = null;
		$i = 0;
		//采暖时间
		$list4 = R('Api/getCnsj', array());
		$sj = intval($list4['YEAR']);
		foreach($houselist as $key=>$val) {
			//当月明细
			$list = R('Api/getOweCharge', array($val['houseCode']));
			//当期账单时间
			if(!empty($list)){
				$accountTime = $list[0]['CHARGEMONTH'];
			}
			$arr[$key]['CHARGEMONTH'] = $accountTime;

			//热计量单价
			$rjldj = number_format($list[0]['RJLDJ'],4);

			/*if($val['houseCode'] == '5010-031-01-05-03'){
				$ceshi = '5006-006-02-05-01';
			}
			else{
				$ceshi = $val['houseCode'];
			}*/
			$ceshi = $val['houseCode'];
			//判断房间计费方式（面积/计量）
			//$list2 = R('Api/getJffs', array($val['houseCode']));
			$list2 = R('Api/getJffs', array($ceshi));
			//采暖明细
			//$list1 = R('Api/getCnmx', array($val['houseCode']));

			$strtime = date('Y')."-6-1 00:00:00";
			if(strtotime(date('Y-m-d H:i:s'))<=strtotime($strtime)){
				$sj = date('Y')-1;
			}

			$arr[$key]['DQCNSJ'] = $sj.'-'.($sj+1).'采暖期';
			$arr[$key]['ZQCNSJ'] = ($sj-1).'-'.$sj;
			$list1 = R('Api/getCnmx', array($ceshi));
			if($list2['CNSFFS'] == '采暖面积' && $list2['SHOWFLAG'] == 'hide'){
				$arr[$key]['ITEMNAME'] = '采暖面积';
				$arr[$key]['PRICE'] = floatval($list1['PRICE'])."元";
				$arr[$key]['CHARGEAREA'] = $list1['CHARGEAREA']."(m²)";
				$arr[$key]['JBRF'] = '-';
				$arr[$key]['QF'] = 1;

			}
			else{
				//获取计量用量
				$list3 = R('Api/getJlyl', array($ceshi));

				$arr[$key]['ITEMNAME'] = '采暖计量';
				$arr[$key]['PRICE'] = "0.13元";

				//对时间戳进行格式化
				$sj1 = time();
				$year = date('Y');
				$start = strtotime($year."-05-01 00:00:00");
				$end = strtotime( $year."-11-15 00:00:00");
				if($sj1<$end && $sj1>start){
					//$arr[$key]['CHARGEAREA'] = $list3['TOTAL_POINT']."(kwh)";
					$arr[$key]['CHARGEAREA'] = '-';
				}
				else{
					$arr[$key]['CHARGEAREA'] = '-';
				}

				$arr[$key]['CNMJ'] = $list1['CHARGEAREA']."(m²)";
				$arr[$key]['JBRF'] = floatval($list1['CHARGEAREA'] * $list1['PRICE'] * 0.3);
				$arr[$key]['QF'] = 2;
			}

			$arr[$key]['DN_YHJM'] = $list1['DN_YHJM'];
			$arr[$key]['DN_YJBJ'] = floatval($list1['DN_YJBJ']-$list1['DN_YHJM']);
			$arr[$key]['DN_WYJ'] =  $list1['DN_WYJ'];
			$arr[$key]['DN_YJ'] = $list1['DN_YJ'];
			$arr[$key]['QN_JY'] = $list1['QN_JY'];
			$arr[$key]['ZQ_QFBJ'] = $list1['ZQ_QFBJ'];
			$arr[$key]['ZQ_WYJ'] = $list1['ZQ_WYJ'];

			$arr[$key]['DNCNF'] = $list[0]['DNCNF'];
			$arr[$key]['CNFYL'] = $list[0]['CNFYL'];
			$arr[$key]['DNCNFYIS'] = $list[0]['DNCNFYIS'];
			$arr[$key]['DNCNFQF'] = $list[0]['DNCNFQF'];
			$arr[$key]['ZQNDCNFZJ'] =  $list[0]['ZQNDCNFZJ'];
			$arr[$key]['ZQNDCNF'] = $list[0]['ZQNDCNF'];
			$arr[$key]['ZQNDCNFZNJ'] = $list[0]['ZQNDCNFZNJ'];

			$arr[$key]['DYSF'] = $list[0]['DYSF'];
			$arr[$key]['DYSF2'] = $list[0]['DYSF2'];
			$arr[$key]['DYSF3'] = $list[0]['DYSF3'];
			$arr[$key]['DYSFYIS'] = $list[0]['DYSFYIS'];
			$arr[$key]['DYSFYIS2'] = $list[0]['DYSFYIS2'];
			$arr[$key]['DYSFYIS3'] = $list[0]['DYSFYIS3'];
			$arr[$key]['QDSFQF'] = $list[0]['QDSFQF'];
			$arr[$key]['QDSFQF2'] = $list[0]['QDSFQF2'];
			$arr[$key]['QDSFQF3'] = $list[0]['QDSFQF3'];
			$arr[$key]['SFYL'] = $list[0]['SFYL'];
			$arr[$key]['SFYL2'] = $list[0]['SFYL2'];
			$arr[$key]['SFYL3'] = $list[0]['SFYL3'];
			//自来水应交金额
			$zqfsfzj = ($list[0]['DYSFYIS']-$list[0]['DYSF'])+($list[0]['SFDYJCQ']-$list[0]['ZQYSFZNJ']-$list[0]['ZQYSF']);
			$zqfsfzj2 = ($list[0]['DYSFYIS2']-$list[0]['DYSF2'])+($list[0]['SFDYJCQ']-$list[0]['ZQYSFZNJ']-$list[0]['ZQYSF2']);
			$zqfsfzj3 = ($list[0]['DYSFYIS3']-$list[0]['DYSF3'])+($list[0]['SFDYJCQ']-$list[0]['ZQYSFZNJ']-$list[0]['ZQYSF3']);
			if($zqfsfzj >=0){
				$arr[$key]['ZQYSFZJ'] = 0;
			}
			else{
				$arr[$key]['ZQYSFZJ'] = abs(number_format($zqfsfzj,2));
			}
			if($zqfsfzj2 >=0){
				$arr[$key]['ZQYSFZJ2'] = 0;
			}
			else{
				$arr[$key]['ZQYSFZJ2'] = abs(number_format($zqfsfzj2,2));
			}
			if($zqfsfzj3 >=0){
				$arr[$key]['ZQYSFZJ3'] = 0;
			}
			else{
				$arr[$key]['ZQYSFZJ3'] = abs(number_format($zqfsfzj3,2));
			}

			$arr[$key]['ZQYSF'] = $list[0]['ZQYSF'];
			$arr[$key]['ZQYSFZNJ'] = $list[0]['ZQYSFZNJ'];
			$arr[$key]['ZQYSFYIS'] = $list[0]['SFDYJCQ'];
			$arr[$key]['DYRQF'] = $list[0]['DYRQF'];
			$arr[$key]['DYRQF2'] = $list[0]['DYRQF2'];
			$arr[$key]['DYRQF3'] = $list[0]['DYRQF3'];
			$arr[$key]['DYRQFYIS'] = $list[0]['DYRQFYIS'];
			$arr[$key]['DYRQFYIS2'] = $list[0]['DYRQFYIS2'];
			$arr[$key]['DYRQFYIS3'] = $list[0]['DYRQFYIS3'];
			$arr[$key]['DQRQFQH'] = $list[0]['DQRQFQH'];
			$arr[$key]['DQRQFQH2'] = $list[0]['DQRQFQH2'];
			$arr[$key]['DQRQFQH3'] = $list[0]['DQRQFQH3'];
			$arr[$key]['RQYL'] = $list[0]['RQYL'];
			$zqyrqfzj = ($list[0]['DYRQFYIS']-$list[0]['DYRQF'])+($list[0]['RQFDYJCQ']-$list[0]['ZQYRQF']-$list[0]['ZQYRQFZNJ']);
			$zqyrqfzj2 = ($list[0]['DYRQFYIS2']-$list[0]['DYRQF2'])+($list[0]['RQFDYJCQ']-$list[0]['ZQYRQF']-$list[0]['ZQYRQFZNJ']);
			$zqyrqfzj3 = ($list[0]['DYRQFYIS3']-$list[0]['DYRQF3'])+($list[0]['RQFDYJCQ']-$list[0]['ZQYRQF']-$list[0]['ZQYRQFZNJ']);
			if($zqyrqfzj >=0){
				$arr[$key]['ZQYRQFZJ'] = 0;
			}
			else{
				$arr[$key]['ZQYRQFZJ'] = abs(number_format($zqyrqfzj,2));
			}
			if($zqyrqfzj2 >=0){
				$arr[$key]['ZQYRQFZJ2'] = 0;
			}
			else{
				$arr[$key]['ZQYRQFZJ2'] = abs(number_format($zqyrqfzj2,2));
			}
			if($zqyrqfzj3 >=0){
				$arr[$key]['ZQYRQFZJ3'] = 0;
			}
			else{
				$arr[$key]['ZQYRQFZJ3'] = abs(number_format($zqyrqfzj3,2));
			}
			$arr[$key]['ZQYRQFYIS'] = $list[0]['RQFDYJCQ'] ;
			$arr[$key]['ZQYRQF'] = $list[0]['ZQYRQF'];
			$arr[$key]['ZQYRQFZNJ'] = $list[0]['ZQYRQFZNJ'];
			/*if($rjldj != 0){
				$arr[$key]['DQSUM'] = $list[0]['DYSF']+$list[0]['DYRQF'];
				$arr[$key]['YISSUM'] = $list[0]['DYSFYIS']+$list[0]['DYRQFYIS'];
				$arr[$key]['QFSUM'] = $list[0]['DQRQFQH']+$list[0]['QDSFQF'];
			}
			else{
				$arr[$key]['DQSUM'] = $list[0]['DYSF']+$list[0]['DYRQF']+$list[0]['DNCNF']+$list[0]['DYRJLFY'];
				$arr[$key]['YISSUM'] = $list[0]['DYSFYIS']+$list[0]['DYRQFYIS']+$list[0]['DNCNFYIS'];
				$arr[$key]['QFSUM'] = $list[0]['DQRQFQH']+$list[0]['QDSFQF']+$list[0]['DNCNFQF'];
			}*/
			//自来水小计
			$arr[$key]['SFBJXJ'] = $list[0]['DYSF']+$list[0]['DYSF2']+$list[0]['DYSF3'];
			$arr[$key]['SFWYJXJ'] = 0;
			$arr[$key]['SFYLXJ'] = $list[0]['SFYL']+$list[0]['SFYL2']+$list[0]['SFYL3'];
			$arr[$key]['SFYJXJ'] = $list[0]['DYSFYIS']+$list[0]['DYSFYIS2']+$list[0]['DYSFYIS3'];
			$arr[$key]['SFQFBJXJ'] = $list[0]['ZQYSF']+0+0;
			$arr[$key]['SFZQWYJXJ'] = $list[0]['ZQYSFZNJ']+0+0;
			$arr[$key]['SFZQYJXJ'] = $list[0]['SFDYJCQ']+0+0;
			$arr[$key]['SFYJJEXJ'] = $arr[$key]['ZQYSFZJ']+$arr[$key]['ZQYRQFZJ2']+$arr[$key]['ZQYRQFZJ3'];
			//天然气小计
			$arr[$key]['RQBJXJ'] = $list[0]['DYRQF']+$list[0]['DYRQF2']+$list[0]['DYRQF3'];
			$arr[$key]['RQWYJXJ'] = 0;
			$arr[$key]['RQYLXJ'] = $list[0]['RQYL']+$list[0]['RQYL2']+$list[0]['RQYL3'];
			$arr[$key]['RQYJXJ'] = $list[0]['DYRQFYIS']+$list[0]['DYRQFYIS2']+$list[0]['DYRQFYIS3'];
			$arr[$key]['RQQFBJXJ'] = $list[0]['ZQYRQF']+0+0;
			$arr[$key]['RQZQWYJXJ'] = $list[0]['ZQYRQFZNJ']+0+0;
			$arr[$key]['RQZQYJXJ'] = $list[0]['RQFDYJCQ']+0+0;
			$arr[$key]['RQYJJEXJ'] = $arr[$key]['ZQYRQFZJ']+$arr[$key]['ZQYRQFZJ2']+$arr[$key]['ZQYRQFZJ3'];
			//总合计
			$arr[$key]['DQSUM'] = $list[0]['DYSF']+$list[0]['DYRQF']+$list[0]['DYRQF2']+$list[0]['DYRQF3']+$list[0]['DYSF2']+$list[0]['DYSF3'];
			$arr[$key]['YISSUM'] = $list[0]['DYSFYIS']+$list[0]['DYRQFYIS']+$list[0]['DYRQFYIS2']+$list[0]['DYRQFYIS3']+$list[0]['DYSFYIS2']+$list[0]['DYSFYIS3'];
			$arr[$key]['QFSUM'] = $list[0]['DQRQFQH']+$list[0]['QDSFQF']+$list[0]['QDSFQF2']+$list[0]['QDSFQF3']+$list[0]['DQRQFQH2']+$list[0]['DQRQFQH3'];
			$arr[$key]['QFBJSUM'] = $list[0]['ZQYSF']+$list[0]['ZQYRQF'];
			$arr[$key]['ZNJSUM'] = $list[0]['ZQYSFZNJ']+$list[0]['ZQYRQFZNJ'];
			$arr[$key]['ZQYISSUM'] = $list[0]['SFDYJCQ']+$list[0]['RQFDYJCQ'];


			/*-- update wangshipeng 2015.-3.12   start*/
			$value = R('Api/getAddress', array($val['houseCode']));
			$arr[$key]['HOUSENAME'] = $value[0]['ADDRESS'];
			/*-- update wangshipeng 2015.-3.12   end*/


			/*-- 供热分开显示   */
			$arr[$key]['GRMJDJ'] = $list[0]['GRMJDJ'];
			$arr[$key]['ZLSDJ'] = $list[0]['ZLSDJ'];
			$arr[$key]['ZLSDJ2'] = $list[0]['ZLSDJ2'];
			$arr[$key]['ZLSDJ3'] = $list[0]['ZLSDJ3'];
			$arr[$key]['TRQDJ'] = $list[0]['TRQDJ'];
			$arr[$key]['TRQDJ2'] = $list[0]['TRQDJ2'];
			$arr[$key]['TRQDJ3'] = $list[0]['TRQDJ3'];
			$arr[$key]['RJLDJ'] = number_format($list[0]['RJLDJ'],4);
			$arr[$key]['RJLYL'] = $list[0]['RJLYL'];
			$arr[$key]['ZQYRJLFY'] = $list[0]['ZQYRJLFY'];
			$arr[$key]['ZQYRJLZNJ'] = $list[0]['ZQYRJLZNJ'];

			$arr[$key]['DYRJLFY'] = $list[0]['DYRJLFY'];
			$arr[$key]['ZQYRJLZJ'] = $list[0]['ZQYRJLZJ'];
			if($rjldj != 0){
				$arr[$key]['YJJESUM'] = $arr[$key]['ZQYSFZJ']+$arr[$key]['ZQYRQFZJ']+$arr[$key]['ZQYRQFZJ2']+$arr[$key]['ZQYRQFZJ3']+$arr[$key]['ZQYSFZJ2']+$arr[$key]['ZQYSFZJ3'];
			}
			else{
				$arr[$key]['YJJESUM'] = $arr[$key]['ZQNDCNFZJ']+$arr[$key]['ZQYSFZJ']+$arr[$key]['ZQYRQFZJ']+$arr[$key]['ZQYRJLZJ']+$arr[$key]['ZQYRQFZJ2']+$arr[$key]['ZQYRQFZJ3']+$arr[$key]['ZQYSFZJ2']+$arr[$key]['ZQYSFZJ3'];
			}

			/*-- 计量热费对比 --*/
			if($rjldj != 0){
				$rjlList = R('Api/getRjlCharge', array($val['houseCode'],$accountTime));

				$count = count($rjlList)+2;
				$fjrjl[$key]['NUM'] = $key;
				$fjrjl[$key]['NUMBER'] = $count;
				if(!empty($rjlList)){
					foreach($rjlList as $flg=>$va) {
						$rjl[$key][$flg]['NUM'] = $key;
						//计量日期
						$jlrq = date('m', strtotime($va['LASTREADDATE']))."月".date('d', strtotime($va['LASTREADDATE']))."日-".date('m', strtotime($va['DATA_DATE']))."月".date('d', strtotime($va['DATA_DATE']))."日";
						$rjl[$key][$flg]['LASTREADDATE'] = $va['LASTREADDATE'];
						$rjl[$key][$flg]['DATA_DATE'] = $va['DATA_DATE'];
						$rjl[$key][$flg]['DATA'] = $jlrq;
						$chargeMonth = $va['CHARGEMONTH'];
						//计量热价
						$rjl[$key][$flg]['PRICE'] = (float)$va['PRICE'];
						//用量
						$rjl[$key][$flg]['POINTNUMBER'] = $va['POINTNUMBER'];
						//计量热费
						$rjl[$key][$flg]['REALACCOUNT'] = $va['REALACCOUNT'];
						//对比
						if($va['BASECASH']>$va['REALACCOUNT']){
							$db = "节约";
							//$dbjg = "-".$va['DAYREALACCOUN'];
							$dbjg = "-".($va['BASECASH']-$va['REALACCOUNT']);
						}
						else{
							$db = "超出";
							//$dbjg = "+".$va['DAYREALACCOUN'];
							$dbjg = "+".($va['BASECASH']+$va['REALACCOUNT']);
						}
						$rjl[$key][$flg]['DAYREALACCOUN'] = $db;
						//计量热价小计
						$jldjSum[$key]['jlrlxj'] += $va['PRICE'];

						//用量小计
						$jldjSum[$key]['ylxj'] += $va['POINTNUMBER'];

						//计量热费小计
						$jldjSum[$key]['jlrfxj'] += $va['REALACCOUNT'];

						//对比
						$jldjSum[$key]['dbCount'] += $dbjg;


						//房间key
						$this->fjKey .=  $key.",";

						//当期热计量费用、对比
						$dqrjlsj = date('Y', strtotime($va['LASTREADDATE'])).date('m', strtotime($va['LASTREADDATE']));

						if($accountTime == $chargeMonth){
							$fjrjl[$key]['dqrjlCount'] = $va['REALACCOUNT'];
							$fjrjl[$key]['dqrjlDb'] = abs($dbjg);
							$fjrjl[$key]['dqrjlDbz'] = $dbjg;

							//计量费用与BASECASH对比
							if($dbjg<0){
								$dqSum = $va['REALACCOUNT']+abs($dbjg);
							}
							$fjrjl[$key]['dqjlDbPercent'] = round($va['REALACCOUNT']/$va['BASECASH'] * 100);
							//当期热计量节约/超出百分比
							$dqjlDbPercent = round($va['REALACCOUNT']/$va['BASECASH'] * 100);
							if($dqjlDbPercent >= 100){
								$fjrjl[$key]['dqjlDbPercents'] = ($dqjlDbPercent -100);
							}
							else{
								$fjrjl[$key]['dqjlDbPercents'] = (100 - $dqjlDbPercent);
							}
						}
						//之前累计计量费用、对比
						if(intval($chargeMonth) <= intval($accountTime)){
							$jldjSum[$key]['zqljjlfy'] += $va['REALACCOUNT'];

							$jldjSum[$key]['zqljjldb'] += $dbjg;

							//之前累计计量费用与BASECASH对比
							/*if($ljdbCount<0){
								$zqSum = abs($ljdbCount);
							}*/

							$jldjSum[$key]['zqljCash'] += $va['BASECASH'];

							//$fjrjl[$key]['zqjlDbPercent'] = round($ljjlSum/$zqljCash * 100);


						}


						if($flg == 0){
							$fjrjl[$key]['HOUSENAME'] = $val['houseName'];
							$date = $list[0]['CNYEAR']."-".($list[0]['CNYEAR']+1)."年采暖费";
							$fjrjl[$key]['CNYEAR'] = $date;
							$fjrjl[$key]['JCRJ'] = 7.5;
							$fjrjl[$key]['CNMJ'] = $list[0]['CNFYL'];
							$fjrjl[$key]['JCRF'] = number_format($fjrjl[$key]['JCRJ'] * $fjrjl[$key]['CNMJ'], 2);
							$fjrjl[$key]['DB'] = '相同';
						}


					}

					$fjrjl[$key]['JLDJCount'] = $jldjSum[$key]['jlrlxj'];
					$fjrjl[$key]['JLYLCount'] = $jldjSum[$key]['ylxj'];
					$fjrjl[$key]['JCRFCount'] = $jldjSum[$key]['jlrfxj'];
					$fjrjl[$key]['DBCount'] = $jldjSum[$key]['dbCount'];
					$fjrjl[$key]['DBSum'] = abs($jldjSum[$key]['dbCount']);
					$fjrjl[$key]['LJJLCount'] = $jldjSum[$key]['zqljjlfy'];
					$fjrjl[$key]['LJDBCount'] = $jldjSum[$key]['zqljjldb'];
					$fjrjl[$key]['LJDBSum'] = abs($jldjSum[$key]['zqljjldb']);
					$fjrjl[$key]['zqljCash'] = $jldjSum[$key]['zqljCash'];
					$fjrjl[$key]['zqljCash'] = abs($jldjSum[$key]['zqljCash']);
					$jishu = $jldjSum[$key]['zqljCash'] - $fjrjl[$key]['LJJLCount'];
					$fjrjl[$key]['LJDBCount'] = $jishu;

					//$fjrjl[$key]['zqjlDbPercent'] = round(abs($jishu)/(abs($jldjSum[$key]['zqljCash'])) * 100);
					$fjrjl[$key]['zqjlDbPercent'] = round(abs($fjrjl[$key]['JCRFCount'])/abs(25*$list[0]['CNFYL']-number_format($fjrjl[$key]['JCRJ'] * $fjrjl[$key]['CNMJ'], 2)) * 100);
					$fjrjl[$key]['zqjlDbPercentPic'] = round(abs($fjrjl[$key]['JCRFCount'])/abs(25*$list[0]['CNFYL']-number_format($fjrjl[$key]['JCRJ'] * $fjrjl[$key]['CNMJ'], 2)) * 100);
					//采暖季度合计
					$fjrjl[$key]['CNJDCount'] = number_format(7.5 * $list[0]['CNFYL'], 2) + $jldjSum[$key]['jlrfxj'];

				}
			}

		}

		//$data1 = R('Api/getZdmxZztCharge', array('5010-031-01-05-03', 'D', '201511'));
		//dump($data1);
		/*-- 修改显示日期   */
		$tempaa = $accountTime."01";
		$startTime =date('Y年m',strtotime("$tempaa -1 month "))."月21日";
		$accountYear = substr($accountTime,0,4);
		$accountMonth = substr($accountTime,4,2);
		$endTime = $accountYear . '年' . $accountMonth . '月' .'20日';
		/* 修改显示日期   --*/
        foreach ($houselist as $v) {
            $hlist[] = $v['houseCode'];
			$hlist[] = $v['houseName'];
        }

		if($accountYear == ''){
			$this->notice = '';
		}
		else{
			$this->notice = '(结算周期' . $startTime ."-". $endTime . ')';
		}
		$sffs = $list2['CNSFFS'];
		$flag = $list2['SHOWFLAG'];

        $this->houselist = $houselist;
		$this->historylist = $arr1;
		$this->feelist = $arr;
		$this->rjl = $rjl;
		$this->fjrjl = $fjrjl;
		$this->code = $houseCode;
		$this->itemCode = $itemCode;
		$this->age = $age;
		$this->moon = $moon;
		$this->sffs = $sffs;
		$this->flag = $flag;
        $this->year = date('Y', time());
        $this->month = date('m', time());
        $this->monthList = array("01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12");
		/*end update by wangshipeng 2014.10.28*/

        /*-- 增加显示当期账号号*/
        $this-> current = $accountTime;
        /* --*/

        $this->topid = $p[0]['id']; //顶部导航选中效果
        $this->display();
    }


	 //在线查询——当期账单
    public function dqzd()
    {
		session_start();
		$_SESSION['varname'] = "zxjf";
	
        if (!isset($_SESSION['checklogin'])) {
            $this->redirect("Public/login");
        }

		$data = array();
		$data1 = array();
		$data2 = array();
		$hcode=$_POST['hcode'];
	
		$houselist = R('User/fjbd', array($_SESSION['uid']));//用户绑定房间
		if(empty($houselist)) {
			$this->error('您没有绑定房间，不能进行在线查询!', '/index.php?s=/user/bind.html');
		}

        $this->id = intval($this->_param('id'));
        $this->pid = D('Cate')->where('id=' . $this->id)->getField('pid'); //左侧导航
        $this->nav = $this->nav($this->cate, $this->id, ''); //当前位置
        $p = get_parents($this->cate, $this->id);
        /*if (empty($p)) {
            $this->redirect("Public/404");
        }*/
		
	
		$houseCode = $_REQUEST['houseCode'];
        if($_REQUEST['houseCode'] == '' || $_REQUEST['houseCode'] == NULL){
//			$houseCode = $houselist[0]['houseCode'];
if($hcode!="")
            {$houseCode =$hcode;}
else{$houseCode = $houselist[0]['houseCode'];}
		}


		$shuiHis=$qiHis=$zaiHis=0;
		$yingyeDate=date('H');
		$result = R('Api/getNowData', array($houseCode));//print_r($result);
		for($k = 0; $k < count($result->{'r_body'}); $k++){
			if($yingyeDate >= 10 && $yingyeDate <= 16){
				$data[$k]['hh24h'] ='on';
			}else{
				$data[$k]['hh24h'] ='off';
			}
			$data[$k]['HOUSECODE'] = $result->{'r_body'}[$k]->{"HOUSECODE"};
			$data[$k]['CHARGEMONTH'] = $result->{'r_body'}[$k]->{"CHARGEMONTH"};
			$data[$k]['ITEMNAME'] = $result->{'r_body'}[$k]->{"ITEMNAME"};
			if($data[$k]['ITEMNAME'] == '自来水'){
				if($shuiHis == 0){
					//判断是否历史欠费
					$data[$k]['payOn'] ='on';
					$shuiHis = 1;
				}else{
					$data[$k]['payOn'] ='off';
				}
				//判断是否锁定
				$shui = R('Api/getShuiStatus', array($data[$k]['HOUSECODE']));
				$data[$k]['STATUS'] = $shui[0]['COU'];
			}
			if($data[$k]['ITEMNAME'] == '天然气'){
				if($qiHis == 0){
					//判断是否历史欠费
					$data[$k]['payOn'] ='on';
					$qiHis = 1;
				}else{
					$data[$k]['payOn'] ='off';
				}
				//判断是否锁定
				$qi = R('Api/getQiStatus', array($data[$k]['HOUSECODE']));
				$data[$k]['STATUS'] = $qi[0]['COU'];
			}
			if($data[$k]['ITEMNAME'] == '再生水'){
				if($zaiHis == 0){
					//判断是否历史欠费
					$data[$k]['payOn'] ='on';
					$zaiHis = 1;
				}else{
					$data[$k]['payOn'] ='off';
				}
				$data[$k]['STATUS'] = 0;
			}
			$data[$k]['POINTNUMBER'] = $result->{'r_body'}[$k]->{"POINTNUMBER"};
			$data[$k]['DQFY'] = $result->{'r_body'}[$k]->{"DQFY"};
			$data[$k]['DQXJ'] = $result->{'r_body'}[$k]->{"DQXJ"};
						$data[$k]['WYJ'] = $result->{'r_body'}[$k]->{"WYJ"};

			$data[$k]['QFZT'] = $result->{'r_body'}[$k]->{"QFZT"};
			$data[$k]['PRICE'] = $result->{'r_body'}[$k]->{"PRICE"};
			$data[$k]['ITEMCODE'] = $result->{'r_body'}[$k]->{"ITEMCODE"};
			$data[$k]['CLOCKNUMBER'] = $result->{'r_body'}[$k]->{"CLOCKNUMBER"};
		}

		//查询供热
		$resultGr = R('Api/getGrData', array($houseCode));
        
		for($k = 0; $k < count($resultGr->{'r_body'}); $k++){
			if($k == 0){
				$dataGr[$k]['payOn'] ='on';
			}else{
				$dataGr[$k]['payOn'] ='off';
			}
			if($yingyeDate >= 10 && $yingyeDate <= 16){
				$dataGr[$k]['hh24h'] ='on';
			}else{
				$dataGr[$k]['hh24h'] ='off';
			}
			
			$re = R('Api/getReStatus', array($historylist[$i]['HOUSECODE']));
			$dataGr[$k]['STATUS'] = $re[0]['COU'];
			$dataGr[$k]['HOUSECODE'] = $resultGr->{'r_body'}[$k]->{"HOUSECODE"};
			$dataGr[$k]['CHARGEMONTH'] = $resultGr->{'r_body'}[$k]->{"CHARGEMONTH"};
			$dataGr[$k]['ITEMNAME'] = $resultGr->{'r_body'}[$k]->{"ITEMNAME"};
			$dataGr[$k]['POINTNUMBER'] = $resultGr->{'r_body'}[$k]->{"POINTNUMBER"}; //采暖面积
			$dataGr[$k]['DQFY'] = $resultGr->{'r_body'}[$k]->{"DQFY"};
            $dataGr[$k]['WYJ'] = $resultGr->{'r_body'}[$k]->{"WYJ"};
			$dataGr[$k]['DNBS'] = $resultGr->{'r_body'}[$k]->{"DNBS"};
			$dataGr[$k]['DQXJ'] = $resultGr->{'r_body'}[$k]->{"DQXJ"};
			$dataGr[$k]['PRICE'] = $resultGr->{'r_body'}[$k]->{"PRICE"};//面积单价
			$dataGr[$k]['ITEMCODE'] = $resultGr->{'r_body'}[$k]->{"ITEMCODE"};
			$dataGr[$k]['CLOCKNUMBER'] = $resultGr->{'r_body'}[$k]->{"CLOCKNUMBER"};
			$dataGr[$k]['YHJM'] = $resultGr->{'r_body'}[$k]->{"YHJM"};  //优惠金额
			$dataGr[$k]['MINUSMONEY'] = $resultGr->{'r_body'}[$k]->{"MINUSMONEY"};//减免费用
			$dataGr[$k]['IMP'] = $resultGr->{'r_body'}[$k]->{"IMP"};//上年节余
			$dataGr[$k]['MON'] = $resultGr->{'r_body'}[$k]->{"MON"};
			
			$dataGr[$k]['PLS'] = $resultGr->{'r_body'}[$k]->{"PLS"};//面积热费
			$dataGr[$k]['XJ1'] = $dataGr[$k]['PLS']-$dataGr[$k]['YHJM'];  //费用小计
			$dataGr[$k]['XJ2'] = $dataGr[$k]['XJ1']-$dataGr[$k]['MINUSMONEY'];//费用小计
			$dataGr[$k]['HJ'] = $dataGr[$k]['XJ2']-$dataGr[$k]['IMP'];//合计
            $dataGr[$k]['STOP'] = $resultGr->{'r_body'}[$k]->{"STOPKEAPFLAG"};
		}

		/*for($k = 1; $k <= count($resultGr->{'r_body'}); $k++){
			$dataGr[$k]['HOUSECODE'] = $resultGr->{'r_body'}[0]->{"HOUSECODE"};
			$dataGr[$k]['CHARGEMONTH'] = $resultGr->{'r_body'}[0]->{"CHARGEMONTH"};
			$dataGr[$k]['ITEMNAME'] = $resultGr->{'r_body'}[0]->{"ITEMNAME"};
			$dataGr[$k]['POINTNUMBER'] = $resultGr->{'r_body'}[0]->{"POINTNUMBER"};
			$dataGr[$k]['DQFY'] = $resultGr->{'r_body'}[0]->{"DQFY"};
			$dataGr[$k]['PRICE'] = $resultGr->{'r_body'}[0]->{"PRICE"};
			$dataGr[$k]['ITEMCODE'] = $resultGr->{'r_body'}[0]->{"ITEMCODE"};
			$dataGr[$k]['CLOCKNUMBER'] = $resultGr->{'r_body'}[0]->{"CLOCKNUMBER"};
			$dataGr[$k]['YHJM'] = $resultGr->{'r_body'}[0]->{"YHJM"};
			$dataGr[$k]['MINUSMONEY'] = $resultGr->{'r_body'}[0]->{"MINUSMONEY"};
			$dataGr[$k]['IMP'] = $resultGr->{'r_body'}[0]->{"IMP"};
			$dataGr[$k]['MON'] = $resultGr->{'r_body'}[0]->{"MON"};
			$dataGr[$k]['PLS'] = $resultGr->{'r_body'}[0]->{"PLS"};
		}*/

		$result1 = R('Api/getPriceData', array($houseCode));

		$data1['ZLSYJ'] = number_format($result1->{'r_body'}[0]->{"ZLSYJ"},2);
		$data1['ZLSEJ'] = number_format($result1->{'r_body'}[0]->{"ZLSEJ"},2);
		$data1['ZLSSJ'] = number_format($result1->{'r_body'}[0]->{"ZLSSJ"},2);
		$data1['TRQYJ'] = number_format($result1->{'r_body'}[0]->{"TRQYJ"},2);
		$data1['TRQEJ'] = number_format($result1->{'r_body'}[0]->{"TRQEJ"},2);
		$data1['TRQSJ'] = number_format($result1->{'r_body'}[0]->{"TRQSJ"},2);
		$data1['ITEMCODE'] = $result1->{'r_body'}[0]->{"ITEMCODE"};
		$data1['POINTNUMBER_B'] = $result1->{'r_body'}[0]->{"POINTNUMBER_B"};
		$data1['POINTNUMBER_D'] = $result1->{'r_body'}[0]->{"POINTNUMBER_D"};
		//var_dump(array($houseCode));exit;
		$result2 = R('Api/getNum', array($houseCode));
		//var_dump($result2);exit;
		$aa=0;
		$bb=0;
		for($k = 0; $k < count($result2->{'r_body'}); $k++){

			$data2[$k]['ITEMCODE'] = $result2->{'r_body'}[$k]->{"ITEMCODE"};
			$data2[$k]['NOWPOINTNUMBER'] = $result2->{'r_body'}[$k]->{"NOWPOINTNUMBER"};
            //$newtu = R('Page/shuChuTu', array($data2[$k]['NOWPOINTNUMBER'],$data2[$k]['ITEMCODE']));
			$data2[$k]['CLOCKNUMBER'] = $result2->{'r_body'}[$k]->{"CLOCKNUMBER"};
			if($data2[$k]['ITEMCODE']=='B'){
			$aa=$aa+1;
			}
			if($data2[$k]['ITEMCODE']=='D'){
			$bb=$bb+1;
			}
		}

 


        
		 //取供热仪表盘数据 
		 $rybdata = R('Api/GetHeatMeteringDashboardServlet',array($houseCode,1));		 
     		for($n = 0; $n < count($rybdata->{'r_body'}); $n++)
		{
			 $grdata[$n]['cssyear']=$rybdata ->{'r_body'}[$n]->{"cssyear"};//年度，热表初始读数行的年度
			 $grdata[$n]['riqi']=$rybdata ->{'r_body'}[$n]->{"riqi"};//日期，根据当前日期判断取得显示的日期
			 $grdata[$n]['metettime']=$rybdata ->{'r_body'}[$n]->{"metettime"};//日期
			
			
             $this->percent=$rybdata ->{'r_body'}[$n]->{"percent"};//百分比
			
			 $this->meteraccount=$rybdata ->{'r_body'}[$n]->{"meteraccount"};//当年累计用量
			 $grdata[$n]['nownum']=$rybdata ->{'r_body'}[$n]->{"nownum"};//当月热表读数
			 $grdata[$n]['startnum']=$rybdata ->{'r_body'}[$n]->{"startnum"};//初始读数
			 $grdata[$n]['ifcutoff']=$rybdata ->{'r_body'}[$n]->{"ifcutoff"};//是否是采暖期最后一个月

		}
		
		 
		
		

/*

		//查询供热百分比图表
		$nowdate = date("Y-m-d");
		$jiudate = date('Y').'-09-30';
		$shiyidate = date('Y').'-11-15';
		$shierdate = date('Y').'-12-15';
		if($nowdate > strtotime($jiudate) && $nowdate <= strtotime($shiyidate)){
			
			$grData['allTotal'] = '--';
			$grData['startNum'] = '--';
			$grData['nianfen'] = date('Y');
			$this->bfb = 0;
		}else if($nowdate > strtotime($shiyidate) && $nowdate <= strtotime($shierdate)){
			
		 
			$grData['allTotal'] = '--';
			$grData['startNum'] = '--';
			$grData['nianfen'] = date('Y');
			$this->bfb = 30;
			echo 11;
		}else{
		
			$grMap['housecode']=$houseCode;
			//当前年度
			$nowyear=date('Y');
			$year['year']=$nowyear;
			$grData = D('EnergyMeter')->limit(2)->where(array_merge($grMap, $year))->order('id desc')->select();
			//echo array_merge($grMap, $year); //将两个数据合并成一个数组
			$dddd = D('EnergyMeter')->where($grMap)->order('id desc')->find();//取出单个的百分比和当年累计用量
			$resultb = R('Api/getBFB', array($houseCode,$grData['allTotal']));
			$this->bfb = $resultb->{'r_body'}[0]->{"PROPORTION"};
            //dump(D('EnergyMeter')->getLastSql());
			 
			//var_dump($grData);
			
			
		}
*/
		

		$this->numlist = $data2;
		$this->pricelist = $data1;
		$this->grlist = $dataGr;
		$this->nowlist = $data;
		$this->assign('aa',$aa);
		$this->assign('bb',$bb);
        $this->houselist = $houselist;
		$this->code = $houseCode;
		$this->dd=$dddd;
		$this->grData = $grdata;

        $this->topid = $p[0]['id']; //顶部导航选中效果
			

		
        $this->display();
    }


	 //在线查询——历史费用
    public function lsfy1()
    {
        $this->check();
		$houselist = R('User/fjbd', array($_SESSION['uid']));//用户绑定房间
		if(empty($houselist)) {
			$this->error('您没有绑定房间，不能进行在线查询!', '/index.php?s=/user/bind.html');
		}
       /* $this->id = intval($this->_param('id'));
        $this->pid = D('Cate')->where('id=' . $this->id)->getField('pid'); //左侧导航
        $this->nav = $this->nav($this->cate, $this->id, ''); //当前位置
        $p = get_parents($this->cate, $this->id);
        if (empty($p)) {
            $this->redirect("Public/404");
        }*/
		$houseCode = $_REQUEST['houseCode'];
        if($_REQUEST['houseCode'] == '' || $_REQUEST['houseCode'] == NULL){
			$houseCode = $houselist[0]['houseCode'];
		}

        $this->houselist = $houselist;
		$this->code = $houseCode;

        $this->topid = $p[0]['id']; //顶部导航选中效果
        $this->display();
    }





	//在线查询——历史账单
    public function lszd()
    {
		
        $this->check();
		$houselist = R('User/fjbd', array($_SESSION['uid']));//用户绑定房间
		if(empty($houselist)) {
			$this->error('您没有绑定房间，不能进行在线查询!', '/index.php?s=/user/bind.html');
		}
        $this->id = intval($this->_param('id'));
        $this->pid = D('Cate')->where('id=' . $this->id)->getField('pid'); //左侧导航
        $this->nav = $this->nav($this->cate, $this->id, ''); //当前位置
        $p = get_parents($this->cate, $this->id);
        if (empty($p)) {
            $this->redirect("Public/404");
        }

        $this->cid = $this->_get('cid');

        $this->urre = $_SESSION['cardnum'];

        /* 能源类型 */
        $this->powertype = R('Api/getPowerType');
        $this->now = time();

		//历史账单
		$zlslist = array();//自来水
		$trqlist = array();//天然气
		$relist = array();//供暖
		$relistto=array();//新加的热量费用
		$jllist = array();//供暖计量
		$zsslist = array();//再生水
		$houseCode = $_POST['houseCode'];

        if($_POST['houseCode'] == '' || $_POST['houseCode'] == NULL){
			$houseCode=$_POST['hcode'];
            //$houseCode = $houselist[0]['houseCode'];
        }
$itemCode = $_POST['itemCode'];
        if (empty($itemCode)) {
            $itemCode = $_GET['itemCode'];
            if (empty($itemCode)) {
                $itemCode = "B";
            }
        }
		 if($itemCode == "A"){
			 $chargeMonthSe = R('Api/GetHouseChargemonthAServlet', array($houseCode));
				for ($i = 0; $i < count($chargeMonthSe->{'r_body'}); $i++) {
            $monthSe[$i]['CHARGEMONTH'] = $chargeMonthSe->{'r_body'}[$i]->{'CHARGEMONTH'};
			$monthSe[$i]['CHARGEMONTHSHOW'] = $monthSe[$i]['CHARGEMONTH'].'-'.($monthSe[$i]['CHARGEMONTH']+1);
				}
		}else{
        $chargeMonthSe = R('Api/GetHouseChargemonthServlet', array($houseCode,$itemCode));//print_r($chargeMonthSe);
        for ($i = 0; $i < count($chargeMonthSe->{'r_body'}); $i++) {
            $monthSe[$i]['CHARGEMONTH'] = $chargeMonthSe->{'r_body'}[$i]->{'CHARGEMONTH'};
            $monthSe[$i]['CHARGEMONTHSHOW'] = $chargeMonthSe->{'r_body'}[$i]->{'CHARGEMONTH'}.'年';
            }
        }
        $age = $_POST['year'];
        if (empty($age)) {
            $age = $monthSe[0]['CHARGEMONTH'];
        }

		$this->itemCode = $itemCode;
//print_r($houseCode);
		for($j=1;$j<5;$j++){
			if(null!=$houseCode && ""!=$houseCode) {
				if($j==1){
					$itemCode = "B";
				}
				if($j==2){
					$itemCode = "D";
				}
				if($j==3){
					$itemCode = "S";
				}
				if($j==4){
					$itemCode = "A";
				}
				//$historylist = R('Api/getLastData', array($houseCode, $itemCode, $age));
				$historylist = R('Api/getHistoryData', array($houseCode, $itemCode, $age));
               
			
               

				for ($i = 0; $i < count($historylist->{'r_body'}); $i++) {
					if($j==1){
						$zlslist[$i]['ADDRESS'] = $historylist->{'r_body'}[$i]->{'ADDRESS'};
						$zlslist[$i]['ITEMCODE'] = $historylist->{'r_body'}[$i]->{'ITEMCODE'};
						$zlslist[$i]['CHARGEMONTH'] = $historylist->{'r_body'}[$i]->{'CHARGEMONTH'};
						$zlslist[$i]['QF'] = $historylist->{'r_body'}[$i]->{'QF'};
						$zlslist[$i]['YL'] = $historylist->{'r_body'}[$i]->{'YL'};
						$zlslist[$i]['NOWCASH'] = $historylist->{'r_body'}[$i]->{'NOWCASH'};
						$zlsylz+=$zlslist[$i]['YL'];
						$zlsfyz+=$zlslist[$i]['NOWCASH'];
						$zlslist[$i]['REALACCOUNT'] = $historylist->{'r_body'}[$i]->{'REALACCOUNT'};
					}
					if($j==2){
						$trqlist[$i]['ADDRESS'] = $historylist->{'r_body'}[$i]->{'ADDRESS'};
						$trqlist[$i]['ITEMCODE'] = $historylist->{'r_body'}[$i]->{'ITEMCODE'};
						$trqlist[$i]['CHARGEMONTH'] = $historylist->{'r_body'}[$i]->{'CHARGEMONTH'};
						$trqlist[$i]['QF'] = $historylist->{'r_body'}[$i]->{'QF'};
						$trqlist[$i]['YL'] = $historylist->{'r_body'}[$i]->{'YL'};
						$trqlist[$i]['NOWCASH'] = $historylist->{'r_body'}[$i]->{'NOWCASH'};
						$trqylz+=$trqlist[$i]['YL'];
						$trqfyz+=$trqlist[$i]['NOWCASH'];
						$trqlist[$i]['REALACCOUNT'] = $historylist->{'r_body'}[$i]->{'REALACCOUNT'};
					}

					if($j==3){
						$zsslist[$i]['ADDRESS'] = $historylist->{'r_body'}[$i]->{'ADDRESS'};
						$zsslist[$i]['ITEMCODE'] = $historylist->{'r_body'}[$i]->{'ITEMCODE'};
						$zsslist[$i]['CHARGEMONTH'] = $historylist->{'r_body'}[$i]->{'CHARGEMONTH'};
						$zsslist[$i]['QF'] = $historylist->{'r_body'}[$i]->{'QF'};
						$zsslist[$i]['YL'] = $historylist->{'r_body'}[$i]->{'YL'};
						$zsslist[$i]['NOWCASH'] = $historylist->{'r_body'}[$i]->{'NOWCASH'};
						$zssylz+=$zsslist[$i]['YL'];
						$zssfyz+=$zsslist[$i]['NOWCASH'];
						$zsslist[$i]['REALACCOUNT'] = $historylist->{'r_body'}[$i]->{'REALACCOUNT'};
					}

					if($j==4){
						$relist[$i]['ADDRESS'] = $historylist->{'r_body'}[$i]->{'ADDRESS'};
						$relist[$i]['ITEMCODE'] = $historylist->{'r_body'}[$i]->{'ITEMCODE'};
						$relist[$i]['CHARGEMONTH'] = $historylist->{'r_body'}[$i]->{'CHARGEMONTH'};
						$relist[$i]['QF'] = $historylist->{'r_body'}[$i]->{'QF'};
						$relist[$i]['YL'] = $historylist->{'r_body'}[$i]->{'YL'};
						$relist[$i]['NOWCASH'] = $historylist->{'r_body'}[$i]->{'NOWCASH'};
						$relist[$i]['REALACCOUNT'] = $historylist->{'r_body'}[$i]->{'REALACCOUNT'};
					}

				}
				
              
			}
		}

		//$sj = array(0=>($age-1)."-11-15",1=>($age-1)."-12-15",2=>$age."-01-15",3=>$age."-02-15",4=>$age."-03-15");
		$sj = array(0=>$age."-11-15",1=>$age."-12-15",2=>($age+1)."-01-15",3=>($age+1)."-02-15",4=>($age+1)."-03-15");
		$map['metetTime'] = array('in', $sj);
		$map['housecode'] = $houseCode;
		$con['housecode'] = $houseCode;
    
$jljljllist=R('Api/GetHeatMeteringMonthlyDosageServlet',array($houseCode,$age,1));

			for ($i = 0; $i < count($jljljllist->{'r_body'}); $i++) {
				$jljllist[$i]['metetTime'] = $jljljllist->{'r_body'}[$i]->{'metetTime'};//日期
				$jljllist[$i]['nownum'] = $jljljllist->{'r_body'}[$i]->{'nownum'};//热表读数
				$jljllist[$i]['alltotal'] = $jljljllist->{'r_body'}[$i]->{'alltotal'};//当月用量
				$jljllist[$i]['meteraccount'] = $jljljllist->{'r_body'}[$i]->{'meteraccount'};//累计用量
				$jljllist[$i]['percent'] = $jljljllist->{'r_body'}[$i]->{'percent'};//百分比

			}
		
    $reslisthistory=R('Api/GetHistoryBill_AServlet',array($houseCode,$age,$age+1));
       $relistto[0]['MJRF_OLD'] = $reslisthistory->{'r_body'}[0]->{'MJRF_OLD'};
	          $relistto[0]['RJLFY'] = $reslisthistory->{'r_body'}[0]->{'RJLFY'};
			  //结余/补缴只能有一个>0
			  $pdjie=$reslisthistory->{'r_body'}[0]->{'JIEYU'};
			  $pdbu=$reslisthistory->{'r_body'}[0]->{'BUJIAO'};
			  if($pdjie>0)
		{ 
				
$relistto[0]['JIEYUhuo'] = $pdjie;
			  }
			  else if($pdbu>0)
		{
			 
$relistto[0]['JIEYUhuo'] = $pdbu;
			  }
			  else if($pdjie==0&&$pdbu==0)
		{
				
				  $relistto[0]['JIEYUhuo'] =0;
			  }
		
			      
					       
				

 


        $this->houselist = R('User/fjbd', array($_SESSION['uid']));
		$this->zlslist = $zlslist;
		$this->zlsylz = $zlsylz;
		$this->zlsfyz = $zlsfyz;
		$this->trqlist = $trqlist;
		$this->trqylz = $trqylz;
		$this->trqfyz = $trqfyz;
		$this->relist = $relist;
	    $this->relistto = $relistto;
		$this->zsslist = $zsslist;
		$this->zssylz = $zssylz;
		$this->zssfyz = $zssfyz;
		//$this->jllist = $jllist;
		$this->jllist = $jljllist;
		$this->code = $houseCode;
        $this->monthSe = $monthSe;
        
		$this->age = $age;
		$this->year = date('Y', time());
		/*end update by wangshipeng 2014.10.28*/

        /*-- 增加显示当期账号号*/
        //$this-> current = $accountTime;
        /* --*/
        $this->topid = $p[0]['id']; //顶部导航选中效果


        $this->display();
    }

	//ajax输出积分
    function ajax_getJF() {
		$jfCode = I('jfCode');
		if($jfCode == ''){
			$ret['iRet'] = -3; //未输入积分
			echo json_encode($ret);die();
		}

        //获取token
        $hbToken = R('Api/getJfToken', array('stcapiusr','134679','192.168.2.127'));
		if($hbToken['Code'] != 0){
			$ret['iRet'] = -1; //无法获取token
			echo json_encode($ret);die();
		}

		$jfToken = $hbToken['Data'];

		//查询积分
		$hbJF = R('Api/getJf', array($jfCode,$jfToken,'192.168.2.127'));
		if($hbJF['Code'] != 0){
			$ret['iRet'] = -2; //获取积分时出错
			echo json_encode($ret);die();
		}

		//查询积分历史
		$hbJFHis = R('Api/getJfHistory', array($jfCode));
		if($hbJF['Code'] != 0){
			$ret['iRet'] = -4; //获取积分时出错
			echo json_encode($ret);die();
		}

		$ret['iRet'] = 0; //无法获取token
		$ret['TotalBonus'] = $hbJF['Data']['TotalBonus'];
		$ret['Bonus'] = $hbJF['Data']['Bonus'];
		$ret['history'] = $hbJFHis['Data'];

        echo json_encode($ret);
    }

	//ajax输出历史账单信息(自来水、天然气)
    function ajax_getHistoryData() {
        $this->check();

        $list = R('Api/getLastData', array($_POST['houseCode'], $_POST['lx'], $_POST['sj']));

        echo json_encode($list);
    }

	//ajax输出历史账单信息(再生水)
    function ajax_getZssHistoryData() {
        $this->check();
		$$zsslist = array();
        $list = R('Api/getHistoryData', array($_POST['houseCode'], $_POST['lx'], $_POST['sj']));
		if(count($list->{'r_body'})!=0){
			for ($i = 0; $i < count($list->{'r_body'}); $i++) {
				$zsslist[$i]['ADDRESS'] = $list->{'r_body'}[$i]->{'ADDRESS'};
				$zsslist[$i]['ITEMCODE'] = $list->{'r_body'}[$i]->{'ITEMCODE'};
				$zsslist[$i]['CHARGEMONTH'] = $list->{'r_body'}[$i]->{'CHARGEMONTH'};
				$zsslist[$i]['QF'] = $list->{'r_body'}[$i]->{'QF'};
				$zsslist[$i]['YL'] = $list->{'r_body'}[$i]->{'YL'};
				$zsslist[$i]['NOWCASH'] = $list->{'r_body'}[$i]->{'NOWCASH'};
				$zsslist[$i]['REALACCOUNT'] = $list->{'r_body'}[$i]->{'REALACCOUNT'};
			}
		}
		else{
			$zsslist[0]['CHARGEMONTH'] = "";
			$zsslist[0]['YL'] = "";
			$zsslist[0]['NOWCASH'] = "";
		}

        echo json_encode($zsslist);
    }



	//ajax输出历史账单信息(供热)
    function ajax_getHistoryReData() {
        $this->check();
		$list = array();
		$age = $_POST['sj'];
		$sj = array(0=>$age."-11-15",1=>$age."-12-15",2=>($age+1)."-01-15",3=>($age+1)."-02-15",4=>($age+1)."-03-15");
		$map['metetTime'] = array('in', $sj);
		$map['housecode'] = $_POST['houseCode'];
		$list = D("EnergyMeter")->where($map)->select();
		for($l=0;$l<count($list);$l++){

			$list[$l]['allTotal'] = $list[$l]['allTotal'];

		}

        echo json_encode($list);
    }

    //在线查询——历史用能
    public function lsyn()
    {
        $this->check();
		$houselist = R('User/fjbd', array($_SESSION['uid']));//用户绑定房间
		if(empty($houselist)) {
			$this->error('您没有绑定房间，不能进行历史用量查询!', '/index.php?s=/user/bind.html');
		}
        $this->id = intval($this->_param('id'));
        $this->pid = D('Cate')->where('id=' . $this->id)->getField('pid'); //左侧导航
        $this->nav = $this->nav($this->cate, $this->id, ''); //当前位置
        $p = get_parents($this->cate, $this->id);
        if (empty($p)) {
            $this->redirect("Public/404");
        }
        //$this->topid = $p[0]['id']; //顶部导航选中效果
        /* 能源类型 */
        $this->powertype = R('Api/getPowerType');
        $this->now = time();
        $houselist = R('User/fjbd', array($_SESSION['uid']));//查询已绑定房间
        foreach ($houselist as $v) {
            $hlist[] = $v['houseCode'];
        }
        $houseCode = json_encode($hlist);
		$char = array();
		$charlist = array();
		$powerlist = array();
		$arr1 = array();
		$houseCode = $_POST['houseCode'];
		$itemCode = $_POST['itemCode'];
		$age = $_POST['year'];

		if (empty($age)) {
			$age = date('Y', time())-1;
			$itemCode = "A";
		}
		if($_POST['houseCode'] == '' || $_POST['houseCode'] == NULL){
			$houseCode = $houselist[0]['houseCode'];
		}

		$j = 0;
        if (!empty($_POST)) {

			if(null!=$houseCode && ""!=$houseCode) {
				$historylist = R('Api/getLastData', array($houseCode, $itemCode, $age));

				for ($i = 0; $i < count($historylist); $i++) {
					$arr1[$i]['ADDRESS'] = $historylist[$i]['ADDRESS'];
					$arr1[$i]['ITEMCODE'] = $historylist[$i]['ITEMCODE'];
					$arr1[$i]['CHARGEMONTH'] = $historylist[$i]['CHARGEMONTH'];
					$arr1[$i]['QF'] = $historylist[$i]['QF'];
					$arr1[$i]['NOWCASH'] = $historylist[$i]['NOWCASH'];
					$arr1[$i]['REALACCOUNT'] = $historylist[$i]['REALACCOUNT'];
					$arr1[$i]['YL']=$historylist[$i]['YL'];
					$this->power_count += $historylist[$i]['REALACCOUNT'];
				}
			}
			$this->powerlist = $arr1;
        $this->ispost = true;
        } else {
            $this->ispost = false;
        }

		$this->houselist = R('User/fjbd', array($_SESSION['uid']));
		$this->code = $houseCode;
		$this->itemCode = $itemCode;
		$this->age = $age;
		$this->year = date('Y', time());

		$this->topid = $p[0]['id']; //顶部导航选中效果
        $this->display();
    }

	//在线查询——积分查询
    public function jfcx()
    {
        $this->redirect("Public/notile");
    }


    //我的5890
    // public function my5890()
    // {
    //     $this->redirect('User/index');
    // }

    public function view()
    {
        $id = intval($_GET['id']);
        if ($id <= 0) {
            $this->redirect("Public/404");
        }

        $data = D('Page')->where(" id = $id")->find();
        if (empty($data)) {
            $this->redirect("Public/404");
        }
        $this->assign($data);
        $this->topid = -1; //顶部导航选中效果
        $this->submenu = 'show';
        $this->display();
    }

    public function views()
    {
        $id = intval($_GET['id']);
        $this->pid = D('Cate')->where('id=' . $id)->getField('pid'); //左侧导航
        if ($id <= 0) {
            $this->redirect("Public/404");
        }

        $data = D('Page')->where(" id = $id")->find();
        if (empty($data)) {
            $this->redirect("Public/404");
        }
        $this->assign($data);
        $this->topid = -1; //顶部导航选中效果

        $this->display();
    }

    public function viewss()
    {
        $id = intval($_GET['id']);
		$this->status = $this->_get('status');
		$pid = D('Cate')->where('id=' . $id)->getField('pid'); //左侧导航
		if($pid == 148){
			$this->pid = 142;
		}
		else{
			$this->pid = $pid;
		}
		$this->nav = $this->nav($this->cate, $id, ''); //当前位置
		if ($id <= 0) {
			$this->redirect("Public/404");
		}

		$data = D('Page')->where(" id = $id")->find();
		if (empty($data)) {
			$this->redirect("Public/404");
		}
		$this->assign($id);
		$this->assign($data);
        $this->topid = -1; //顶部导航选中效果

        $this->display();
    }

    public function map()
    {
        $this->topid = -1; //顶部导航选中效果
        $contents = D("Map")->select();
        if (is_null($contents)) {
            $contents = array(array('contents' => ""));
        }
        $this->assign('contents', $contents[0]['contents']);



        $this->display();
    }

    public function getLsfyData() {
        if (empty($_POST)) {
            // TODO: return no data
        }

        $houseCode = $_POST['houseCode'];
        if (empty($houseCode)) {
            // TODO: return no data
        }

        $itemCode = $_POST['itemCode'];

        $chargeMonth = $_POST['year'] . $_POST['month'];
        if (empty($chargeMonth)) {
            $chargeMonth = date('Y', time()) . date('m', time());
        }

        if (is_null($itemCode)) {
            $data = R('Api/getChargeInforsByHouse', array($houseCode, $chargeMonth));
        } else {
            $data = R('Api/getLineChartDatasByHouse', array($houseCode, $itemCode));
        }
        if (is_array($data)) {
            $array = $data;
        } else {
            $array = json_decode($data->return, false);
        }
		$data='[{"Name":"a1","Number":"123","Contno":"000","QQNo":""},{"Name":"a1","Number":"234","Contno":"000","QQNo":""},{"Name":"a1","Number":"456","Contno":"000","QQNo":""}]';
        $array = json_decode($data,true);
		//echo  json_encode($array,true);
		echo $array;
        exit();
    }

	//获取账单明细-柱状图数据
	public function getZdmxData() {
		//账单日期
		$accountDate = $_POST['accountDate'];
		//房间地址
		$houseCode = $_POST['houseCode'];
		//收费项目
		$itemCode = $_POST['itemCode'];
		//查询类型
		$category = $_POST['category'];
		//返回数据
		$data = R('Api/getZdmxZztCharge', array($houseCode, $itemCode, $accountDate));
		echo $this->ajaxReturn($data,'JSON');
        exit();

	}


	 //自助服务内容页
   public function content()
    {

        $id = intval($_GET['id']);
		$this->cat = get_catarray($this->cate, 'child', $id);
        $this->nav = $this->nav($this->cate, $id, ''); //当前位置

        if ($id <= 0) {
            $this->redirect("Public/404");
        }

        $data = D('Page')->where(" id = $id")->find();
        if (empty($data)) {
            $this->redirect("Public/404");
        }
		$this->pid = D('Cate')->where('id='. $data['id'])->getField('pid'); //左侧导航
        $this->assign($data);
        $this->topid = -1; //顶部导航选中效果
        $this->display();
    }

    public function stjg() {
        $this->topid = -1;
        $this->display();
    }

    public function trash() {
        $this->topid = -1;
        $this->cid = $_REQUEST['cid'];
        $this->display();
    }

    public function station() {
        $this->topid = -1;
        $this->display();
    }


	//基础信息提交方法
    public function save() {

		//数据获取
		$houseCode = $_POST['housecode'];
		$LinkMan = trim($_POST['lxr']);
		$OldLinkMan = trim($_POST['oldlianxiren']);
		$LinkTel = trim($_POST['lxdh']);
		$OldLinkTel = trim($_POST['oldlianxidianhua']);
		$WorkUnit = trim($_POST['gzdw']);
		$OldWorkUnit = trim($_POST['oldgongzuodanwei']);
		$MailingAddress = trim($_POST['yjdz']);
		$OldMailingAddress = trim($_POST['oldyoujidizhi']);
		/*if(empty($LinkMan)){
			echo_json('0', '编辑失败', '联系人不可为空', '', '3');
		}
		if(empty($LinkTel)){
			echo_json('0', '编辑失败', '联系电话不可为空', '', '3');
		}
		if(empty($WorkUnit)){
			echo_json('0', '编辑失败', '工作单位不可为空', '', '3');
		}
		if(empty($MailingAddress)){
			echo_json('0', '编辑失败', '邮寄地址不可为空', '', '3');
		}*/
		R('Api/saveNewHouse', array($houseCode, $LinkMan, $OldLinkMan, $LinkTel, $OldLinkTel, $WorkUnit, $OldWorkUnit, $MailingAddress, $OldMailingAddress));
		echo_json('1', '编辑成功', '编辑成功', U('User/index'), '2');

    }

	//2015-2016供热优惠申请
	 public function htyy()
    {
		$_SESSION['userurl'] = $_SERVER['REQUEST_URI'];
		$this->check();
		$houselist = R('User/fjbd', array($_SESSION['uid']));//用户绑定房间
		if(empty($houselist)) {
			$this->error('您没有绑定房间，不能进行2015-2016供热优惠申请', '/index.php?s=/user/bind.html');
		}
        $id = intval($_GET['id']);
        $this->pid = D('Cate')->where('id=' . $id)->getField('pid'); //左侧导航
        if ($id <= 0) {
            $this->redirect("Public/404");
        }

        $data = D('Page')->where(" id = $id")->find();
        if (empty($data)) {
            $this->redirect("Public/404");
        }
		$this->assign('houselist', $houselist);
        $this->assign($data);
        $this->topid = -1; //顶部导航选中效果

        $this->display();
    }

	//2015-2016供热优惠申请保存
    public function save1() {
		//服务协议勾选
		$xieyi = $_POST['xieyi'];
		//身份证号
		$cardnumber = $_POST['cardnumber'];
		//能源卡号
		$allbanknumber = $_POST['allbanknumber'];
		//房间编号
		$housecode = $_POST['address'];
		//判断房间是否有资格进行申请
		$str = substr($housecode,0,4);
		$ifstr = D('interim')->where('code=' . '"'.$str.'"')->find();
		$string = substr($housecode,0,8);
		$ifstring = D('interim')->where('code=' . '"'.$string.'"')->find();
		if(empty($ifstr) && empty($ifstring)){
			echo_json('0', '提示', '此房间不在此业务的申请范围，敬请谅解！', '', '10');
		}
		//判断能源卡号是否有效
		if($cardnumber == ''){
			echo_json('0', '提示', '请填写能源卡号！', '', '10');
		}
		if($allbanknumber == '' || $allbanknumber == null){
			echo_json('0', '提示', '“由于您的能源卡信息缺失，请房主本人拨打66885890公用事业服务热线进行能源卡号信息登记，感谢您的关注”。', '', '10');
		}
		if($allbanknumber != $cardnumber){
			echo_json('0', '提示', '“能源卡号”与系统记录卡号不一致！', '', '10');
		}
		//判断验证码是否有效
		if (empty($_POST['phoneActiveCode'])) {
			echo_json('0', '提示', '请填写手机验证码！', '', '10');
		}
		if (time() > session('token_getpass_time')) {
			echo_json('0', '提示', '手机验证码过期！', '', '10');
		}
		if (session('phoneActiveCode') != $_POST['phoneActiveCode']) {
			echo_json('0', '提示', '手机验证码错误，请核对！', '', '10');
		}
		//判断协议是否勾选
		if(empty($xieyi)){
			echo_json('0', '操作失败', '请同意并勾选<<说明条款>>，才能进行申请','', '10');
		}
		//判断验证码是否有效
		/*$code = D('Survey')->where('code=' . $_POST['ide_code'])->find();
		if(empty($code)){
			echo_json('0', '操作失败', '您提交的验证码错误，请重新输入','', '2');
		}*/
		//判断房间是否已经提交过此申请
		$house = D('Apply')->where('housecode=' . '"'.$_POST['address'].'"')->find();
		if(!empty($house)){
			echo_json('0', '操作失败', '此房间已提交过申请，请勿反复提交','', '10');
		}
		$data = D('apply')->create();
		//数据获取
		$data['housecode'] = $_POST['address'];
		$data['username'] = $_POST['username'];
		$data['idcard'] = $_POST['allpapernumber'];
		$data['cardnumber'] = $_POST['allbanknumber'];
		$data['area'] = $_POST['area'];
		$data['heattime'] = $_POST['heattime'];
		$data['phone'] = trim($_POST['telephone']);
		$data['payable'] = trim($_POST['payable']);
		$data['discount'] = trim($_POST['discount']);
		$data['left'] = trim($_POST['left']);
		$data['pay'] = trim($_POST['pay']);
		$data['code'] = $_POST['phoneActiveCode'];
		$data['createtime'] = time();
		$data['uid'] = $_SESSION['uid'];
		//获取房间地址
		$housename = D('user_room')->where('houseCode=' . '"'.$_POST['address'].'"')->find();
		$data['housename'] = $housename['houseName'];
		//保存
		$re = D('apply')->add($data);
		if($re){
			echo_json('1', '申请成功', '您的申请已受理，请确保您能源卡有足够金额，以保证您享受相应的优惠', '', '2');
		}else{
			echo_json('1', '申请失败', '申请失败', '', '2');
		}
    }


	//供能合同申请
	 public function gnht()
    {
		$this->check();
        $id = intval($_GET['id']);
        $this->pid = D('Cate')->where('id=' . $id)->getField('pid'); //左侧导航
        if ($id <= 0) {
            $this->redirect("Public/404");
        }

        $data = D('Page')->where(" id = $id")->find();
        if (empty($data)) {
            $this->redirect("Public/404");
        }
		//小区名称
		$this->villagelist = R('Api/getVillage');
        $this->assign($data);
        $this->topid = -1; //顶部导航选中效果

        $this->display();
    }


	//功能合同预约登记列表
    public function htlist() {
        $this->check();
        $this->id = $this->_get('id');
		$pid = $this->_get('pid');
		if (empty($pid)) {
			$this->pid = D('Cate')->where('id=' . $this->id)->getField('pid'); //控制左侧子菜单
			$this->nav = $this->nav($this->cate, $this->id, ''); //当前位置
			$p = get_parents($this->cate, $this->id);
		}
		else{
			$this->pid = $this->_get('pid');
			$this->nav = $this->nav($this->cate, $this->pid, ''); //当前位置
			$p = get_parents($this->cate, $this->pid);
		}

        $this->topid = $p[0]['id']; //顶部导航选中效果

        if (is_numeric($this->id)) {
            $c = get_childsid($this->cate, $this->id);
            $cids = $this->id;
            foreach ($c as $v) {
                $cids.="," . $v;
            }
            $map['cat_id'] = array('in', $cids);
        }
        $userid = $_SESSION['uid'];
        $cat_id = intval($_GET['id']);
        $map['ht_uid'] = array('eq', $userid);
		$map['housecode'] = array('neq', '');
        import("ORG.Util.Page");
        $count = D('Ht')->where($map)->count();
        $page = new Page($count, C('PAGE_SIZE'));
        $page->setConfig('first', '首页');
        $page->setConfig('last', '末页');
        $page->setConfig('theme', ' %upPage%  %first%  %prePage%  %linkPage%  %nextPage% %end%%downPage%<span>%totalRow%条</span> <span>%nowPage%/%totalPage% 页</span>');
        $list = D('Ht')->where($map)->order('ht_id desc')->limit($page->firstRow . ',' . $page->listRows)->select();

        $this->assign('list', $list);
        $show = $page->show();
        $this->assign('page', $show);
        $this->display();
    }

	//供能合同明细
    function htview() {
        $id = intval($this->_param('id'));
        $content = D('Ht')->where('ht_id=' . $id)->find();
		$map['ht_id'] = $content['ht_id'];
		$remark = D('Ht_jd')->where($map)->order('operatedate desc')->find();

		if(empty($content)){
			$this->redirect("Public/404");
		}
        $this->assign($content);
		$this->assign('remark', $remark['remark']);
        $this->display();
    }




	//供能合同申请修正
	 public function htviews()
    {
		$this->check();
        $id = intval($_GET['id']);
        $this->pid = D('Cate')->where('id=201')->getField('pid'); //左侧导航
        if ($id <= 0) {
            $this->redirect("Public/404");
        }

        $data = D('Page')->where(" id = 201")->find();
        if (empty($data)) {
            $this->redirect("Public/404");
        }
		//小区名称
		$this->villagelist = R('Api/getVillage');
        $this->assign($data);
		//合同明细
		$ht = D('Ht')->where('ht_id=' . $id)->find();
		$code = intval($ht['communitycode']);
		//小区编号
		$this->assign('code',$code);
		$this->assign('ht', $ht);
		$this->assign('upload', $upload);
		$this->assign('id', $id);
        $this->topid = -1; //顶部导航选中效果

        $this->display();
    }


	//供能合同申请附件上传
	public function uploadify() {

	 import('ORG.Net.HtUploadFile');
	 $upload = new UploadFile();// 实例化上传类
	 $upload->maxSize = 2097152;// 设置附件上传大小
	 $upload->saveRule = 'getrand';
	 $upload->allowExts = array('jpg', 'gif', 'png');// 设置附件上传类型
	 $upload->savePath = './Uploads/htfile/';// 设置附件上传目录

	 if(!$upload->upload()){// 上传错误提示错误信息
		//echo_json('0', '提示', $upload->getErrorMsg(), '', '10');
		//$this->error($upload->getErrorMsg());
		echo $upload->getErrorMsg();
	  }else{// 上传成功 获取上传文件信息
		$info = $upload->getUploadFileInfo();
		echo $info[0]['savename'];
	  }

	}

	public function db_img($info,$ht_id,$flag){
		 $ph=M('Ht_upload');
		 $account = count($info);
		 if($flag == "update"){
			D('Ht_upload')->where('ht_id='.$ht_id)->delete();
		 }
		 for($i=0;$i<count($info);$i++){

			  $pic = explode("+",$info[$i]);

			  /*if(($info[$i]['key'] == 'sfz1') || ($info[$i]['key'] == 'sfz0')){
				$data['type'] = 1;
			  }
			  else{
				$data['type'] = 2;
			  }*/

			  $data['type'] = $pic[3];

			  $data['file_name'] = $pic[0];

			  $data['file_url'] = $pic[2];

			  //$data['file_name'] = $info[$i]['name'];

			  //$data['file_url']=$info[$i]['savename'];

			  $data['ht_id']=$ht_id;

			 /* if($flag == "update"){
				  //$co = substr($info[$i]['key'],3,1);
				  $upload = D('Ht_upload')->where('ht_id='.$ht_id)->select();
				  foreach($upload as $key=>$val) {
						if($co == $key){
							$map['id']=$val['id'];
							D('Ht_upload')->where($map)->save($data);
						}
				  }

				$arr['O_M'] = 1;
				D('Ht_upload')->where('ht_id='.$ht_id)->save($arr);
				D('Ht_upload')->where('ht_id='.$ht_id)->delete();
			  }*/
			  /*else{
				$rs=$ph->add($data);
			  }*/
			  $rs=$ph->add($data);

		  }
	}

	//供能合同申请申请保存
    public function save2() {

		//身份证
		$sfz = $_POST['sfz'];
		//购房合同或房产证
		$fcz = $_POST['fcz'];
		//服务协议勾选
		$xieyi = $_POST['xieyi'];
		//合同类型
		$ht_ytes = $_POST['ht_ytes'];
		//小区名称
		$arr = explode("_",$_POST['communitcode']);
		$communitcode = $arr[0];
		//使用方
		$syf = $_POST['syf'];
		//楼号
		$arr1 = explode("_",$_POST['buildcode']);
		$buildcode = $arr1[0];
		//门
		$cellcode = $_POST['cellcode'];
		//号
		$doorplatecode = $_POST['doorplatecode'];
		//房间编号
		$housecode = $buildcode."-".$cellcode."-".substr($doorplatecode,0,2)."-".substr($doorplatecode,2,2);
		//房屋类别
		$fwlx = $_POST['fwlx'];
		//采暖形式
		$cnxs = $_POST['cnxs'];

		if($ht_ytes == 1){
			//开户银行
			$khyh = $_POST['khyh'];
			//开户姓名
			$khm = $_POST['khm'];
			//使用关系
			$syfgx = $_POST['syfgx'];
			//身份证号
			$khrsfz = $_POST['khrsfz'];
			//能源卡号
			$nykh = $_POST['nykh'];
			//交费方式
			$fjm_jffs = $_POST['fjm_jffs'];
			//开户银行
			$fjm_khyh = $_POST['fjm_khyh'];
			//开户账号
			$fjm_zh = $_POST['fjm_zh'];
			//其它约定
			$sfydqtsx = $_POST['sfydqtsx'];
			if(empty($_POST ['phoneActiveCode'])){
				echo_json('0', '操作失败', '手机验证码不能为空', '', '10');
			}
			if (session('phoneActiveCode') != $_POST ['phoneActiveCode']) {
                echo_json('0', '操作失败', '手机验证码错误，请核对！', '', '60');
            }
		}
		//乙方签字
		$yfqz = $_POST['yfqz'];


		//业主姓名
		$yhdj_a_yzxm = $_POST['yhdj_a_yzxm'];
		//证件类型
		$yhdj_a_zjlx = $_POST['yhdj_a_zjlx'];
		//证件号码
		$yhdj_a_zjhm = $_POST['yhdj_a_zjhm'];
		//移动电话
		$yhdj_a_yddh = $_POST['yhdj_a_yddh'];
		//固定电话
		$yhdj_a_gddh = $_POST['yhdj_a_gddh'];
		//邮寄地址
		$yhdj_a_yjdz = $_POST['yhdj_a_yjdz'];
		//邮政编号
		$yhdj_a_yb = $_POST['yhdj_a_yb'];
		//单位名称
		$yhdj_a_dwmc = $_POST['yhdj_a_dwmc'];
		//单位地址
		$yhdj_a_dwdzhdh = $_POST['yhdj_a_dwdzhdh'];
		//成员姓名
		$yhdj_a_zyjtcyxm = $_POST['yhdj_a_zyjtcyxm'];
		//联系电话
		$yhdj_a_zyjtcydh = $_POST['yhdj_a_zyjtcydh'];

		//业主姓名
		$yhdj_b_yzxm = $_POST['yhdj_b_yzxm'];
		//证件类型
		$yhdj_b_zjlx = $_POST['yhdj_b_zjlx'];
		//证件号码
		$yhdj_b_zjhm = $_POST['yhdj_b_zjhm'];
		//移动电话
		$yhdj_b_yddh = $_POST['yhdj_b_yddh'];
		//固定电话
		$yhdj_b_gddh = $_POST['yhdj_b_gddh'];
		//邮寄地址
		$yhdj_b_yjdz = $_POST['yhdj_b_yjdz'];
		//邮政编号
		$yhdj_b_yb = $_POST['yhdj_b_yb'];
		//单位名称
		$yhdj_b_dwmc = $_POST['yhdj_b_dwmc'];
		//单位地址
		$yhdj_b_dwdzhdh = $_POST['yhdj_b_dwdzhdh'];

		//判断协议是否勾选
		if(empty($xieyi)){
			echo_json('0', '操作', '请预览并勾选<<合同条款>>，才能进行申请!', '', '10');
		}

		//判断是否填写
		if($communitcode == '' || $syf == '' || $buildcode == '' || $cellcode == '' || $doorplatecode == '' || $fwlx == '' || $cnxs == ''  || $yfqz == ''){
			echo_json('0', '操作', '带*为必填项，请填写!', '', '10');
		}
		if($ht_ytes == 1){
			if($khyh == '' || $khm == '' || $syfgx == '' || $khrsfz == '' || $yhdj_a_yzxm == '' || $yhdj_a_zjlx == '' || $yhdj_a_zjhm == '' || $yhdj_a_yddh == '' || $yhdj_a_yjdz == '' || $yhdj_a_zyjtcyxm == '' || $yhdj_a_zyjtcydh == ''){
				echo_json('0', '操作', '带*为必填项，请填写!', '', '10');
			}
			if($yhdj_b_zjlx == '其它' && empty($yhdj_b_zjhm)){
				echo_json('0', '操作', '请填写第二房主信息的证件号码!', '', '10');
			}
		}

		//if(empty($_POST['id'])){
		/*if(empty($_POST['sfz']) || empty($_POST['fcz'])){
			echo_json('0', '操作', '请按要求上传文件!', '', '10');
		 }*/
		//}


		//判断房间是否已经提交过此申请
		$house = D('ht')->where('housecode=' . '"'.$housecode.'" and status !=1')->find();
		if(!empty($house)){
			echo_json('0', '操作', '此房间已提交过申请，请勿反复提交!', '', '10');
		}

		$flag = "add";

		if(!empty($_POST['id'])){
			$flag = "update";
			$last_id = $_POST['id'];
		}
		else{
			$last =  D('ht')->max('ht_id');
			if(empty($last)){
				$data2['ht_uid'] = $_SESSION['uid'];
				D('ht')->add($data2);
				$last =  D('ht')->max('ht_id');
			}
			$last_id = $last+1;
		}

		//附件处理
		if((!empty($sfz)) && (!empty($fcz))){

			//$info = $this->uploadify();
			$sfz1 = explode(",",$sfz);
			$fcz1 = explode(",",$fcz);
			$info = array_merge($sfz1,$fcz1);
			for($i=0;$i<count($info);$i++){
				$pic = explode("+",$info[$i]);
				$pic_type = explode(".",$pic[0])[1];

				if($pic_type !='png' && $pic_type !='jpg' && $pic_type !='gif'){
					echo_json('0', '操作', '上传文件格式只能为jpg,gif,png' , '', '10');
				}
				$pic_size = $pic[1];
				if($pic_size > 2097152){
					echo_json('0', '操作', '上传文件大小不能超过2M' , '', '10');
				}
			}
			$this->db_img($info,$last_id,$flag);
		}

		$data = D('ht')->create();
		$data1 = D('ht_jd')->create();
		//数据获取
		$data['ht_ytes'] = $ht_ytes;
		$data['housecode'] = $housecode;
		$data['communitycode'] = $communitcode;
		$data['syf'] = $syf;
		$data['buildingcode'] = $buildcode;
		$data['cellcode'] = $cellcode;
		$data['doorplatecode'] = $doorplatecode;
		$data['fwlx'] = $fwlx;
		$data['cnxs'] = $cnxs;
		//缴费面积
		$arr2 = R('Api/getJfmj',array($housecode));
		$jfmj = $arr2[0]['CHARGEAREA'];
		$data['jfmj'] = $jfmj;
		$data['khyh'] = $khyh;
		$data['khm'] = $khm;
		$data['syfgx'] = $syfgx;
		$data['khrsfz'] = $khrsfz;
		$data['nykh'] = $nykh;
		$data['fjm_jffs'] = $fjm_jffs;
		$data['fjm_khyh'] = $fjm_khyh;
		$data['fjm_zh'] = $fjm_zh;
		$data['sfydqtsx'] = $sfydqtsx;
		$data['yfqz'] = $yfqz;

		$data['yhdj_a_yzxm'] = $yhdj_a_yzxm;
		$data['yhdj_a_zjlx'] = $yhdj_a_zjlx;
		$data['yhdj_a_zjhm'] = $yhdj_a_zjhm;
		$data['yhdj_a_yddh'] = $yhdj_a_yddh;
		$data['yhdj_a_gddh'] = $yhdj_a_gddh;
		$data['yhdj_a_yjdz'] = $yhdj_a_yjdz;
		$data['yhdj_a_yb'] = $yhdj_a_yb;
		$data['yhdj_a_dwmc'] = $yhdj_a_dwmc;
		$data['yhdj_a_dwdzhdh'] = $yhdj_a_dwdzhdh;
		$data['yhdj_a_zyjtcyxm'] = $yhdj_a_zyjtcyxm;
		$data['yhdj_a_zyjtcydh'] = $yhdj_a_zyjtcydh;

		$data['yhdj_b_yzxm'] = $yhdj_b_yzxm;
		$data['yhdj_b_zjlx'] = $yhdj_b_zjlx;
		$data['yhdj_b_zjhm'] = $yhdj_b_zjhm;
		$data['yhdj_b_yddh'] = $yhdj_b_yddh;
		$data['yhdj_b_gddh'] = $yhdj_b_gddh;
		$data['yhdj_b_yjdz'] = $yhdj_b_yjdz;
		$data['yhdj_b_yb'] = $yhdj_b_yb;
		$data['yhdj_b_dwmc'] = $yhdj_b_dwmc;
		$data['yhdj_b_dwdzhdh'] = $yhdj_b_dwdzhdh;
		$data['ht_cjsj'] = time();
		$data['ht_uid'] = $_SESSION['uid'];

		//保存
		if(!empty($_POST['id'])){
			$data['status'] = 2;
			$re = D('ht')->where('ht_id='.$_POST['id'])->save($data);
			$data1['ht_id'] = $_POST['id'];
		}
		else{

			$re = D('ht')->add($data);

			$data1['ht_id'] = $re;

		}

		$user = D('User')->where('id=' . '"'.$_SESSION['uid'].'"')->find();
		$data1['status'] = '网站审核';
		$data1['operator'] = $user['username'];
		$data1['operatedate'] = date("Y-m-d H:i:s",time());
		$r = D('ht_jd')->add($data1);

		if($re){
			echo_json('1', '申请成功', '您的申请已受理，请耐心等待审核结果！', U('Page/htlist','id=201'), '1000');
		}else{
			echo_json('0', '申请失败', '申请失败，请拨打客服热线022-66885890进行咨询！', '', '1000');
		}
    }

	//获取楼号
	function getBuilding() {
		$arr = explode("_",$_POST['parameter']);
        $parameter = $arr[0];
		$data = R('Api/getBuild',array($parameter));
        echo $this->ajaxReturn($data,'JSON');
        exit();
    }

	//获取门
	function getUite() {
		$arr = explode("_",$_POST['parameter']);
        $buildingcode = $arr[0];
        $data = R('Api/getUnitNum',array($buildingcode));
        echo $this->ajaxReturn($data,'JSON');
        exit();
    }

	//获取号
	function getFloor() {
        $cellcode = $_POST['parameter'];
		$arr = explode("_",$_POST['parameter_2']);
		$buildingcode = $arr[0];
        $data = R('Api/getFloorNum',array($buildingcode,$cellcode));
        echo $this->ajaxReturn($data,'JSON');
        exit();
    }

	//供能合同申请预览(面积)
	function mjyl() {
		$arr = array();
		$ht_uid = $_SESSION['uid'];
		$ht_ytes = $_POST['ht_ytes'];
		//小区名称
		$arr1 = explode("_",$_POST['communitcode']);
        $arr[0]['communitcode'] = $arr1[1];
		//使用方
		$arr[0]['syf'] = $_POST['syf'];
		//楼号
		$arr2 = explode("_",$_POST['buildcode']);
        $arr[0]['buildcode'] = $arr2[1];
		//门
		$arr[0]['cellcode'] = $_POST['cellcode'];
		//号
		$arr[0]['doorplatecode'] = $_POST['doorplatecode'];
		//房屋类别
		$arr[0]['fwlx'] = $_POST['fwlx'];
		//采暖形式
		$arr[0]['cnxs'] = $_POST['cnxs'];
		//房间编号
		$housecode = $arr2[0]."-".$_POST['cellcode']."-".substr($_POST['doorplatecode'],0,2)."-".substr($_POST['doorplatecode'],2,2);
		//缴费面积
		$jfmj = R('Api/getJfmj',array($housecode));
		$arr[0]['jfmj'] = $jfmj[0]['CHARGEAREA'];
		//房间编号
		$arr[0]['housecode'] = $housecode;
		//开户银行
		$arr[0]['khyh'] = $_POST['khyh'];
		//开户姓名
		$arr[0]['khm'] = $_POST['khm'];
		//使用关系
		$arr[0]['syfgx'] = $_POST['syfgx'];
		//身份证号
		$arr[0]['khrsfz'] = $_POST['khrsfz'];
		//能源卡号
		$arr[0]['nykh'] = $_POST['nykh'];
		//交费方式
		$arr[0]['fjm_jffs'] = $_POST['fjm_jffs'];
		//开户银行
		$arr[0]['fjm_khyh'] = $_POST['fjm_khyh'];
		//开户账号
		$arr[0]['fjm_zh'] = $_POST['fjm_zh'];
		//其它事项
		$arr[0]['sfydqtsx'] = $_POST['sfydqtsx'];
		//乙方签字
		$arr[0]['yfqz'] = $_POST['yfqz'];


		//业主姓名
		$arr[0]['yhdj_a_yzxm'] = $_POST['yhdj_a_yzxm'];
		//证件类型
		$arr[0]['yhdj_a_zjlx'] = $_POST['yhdj_a_zjlx'];
		//证件号码
		$arr[0]['yhdj_a_zjhm'] = $_POST['yhdj_a_zjhm'];
		//移动电话
		$arr[0]['yhdj_a_yddh'] = $_POST['yhdj_a_yddh'];
		//固定电话
		$arr[0]['yhdj_a_gddh'] = $_POST['yhdj_a_gddh'];
		//邮寄地址
		$arr[0]['yhdj_a_yjdz'] = $_POST['yhdj_a_yjdz'];
		//邮政编号
		$arr[0]['yhdj_a_yb'] = $_POST['yhdj_a_yb'];
		//单位名称
		$arr[0]['yhdj_a_dwmc'] = $_POST['yhdj_a_dwmc'];
		//单位地址
		$arr[0]['yhdj_a_dwdzhdh'] = $_POST['yhdj_a_dwdzhdh'];
		//成员姓名
		$arr[0]['yhdj_a_zyjtcyxm'] = $_POST['yhdj_a_zyjtcyxm'];
		//联系电话
		$arr[0]['yhdj_a_zyjtcydh'] = $_POST['yhdj_a_zyjtcydh'];

		//业主姓名
		$arr[0]['yhdj_b_yzxm'] = $_POST['yhdj_b_yzxm'];
		//证件类型
		$arr[0]['yhdj_b_zjlx'] = $_POST['yhdj_b_zjlx'];
		//证件号码
		$arr[0]['yhdj_b_zjhm'] = $_POST['yhdj_b_zjhm'];
		//移动电话
		$arr[0]['yhdj_b_yddh'] = $_POST['yhdj_b_yddh'];
		//固定电话
		$arr[0]['yhdj_b_gddh'] = $_POST['yhdj_b_gddh'];
		//邮寄地址
		$arr[0]['yhdj_b_yjdz'] = $_POST['yhdj_b_yjdz'];
		//邮政编号
		$arr[0]['yhdj_b_yb'] = $_POST['yhdj_b_yb'];
		//单位名称
		$arr[0]['yhdj_b_dwmc'] = $_POST['yhdj_b_dwmc'];
		//单位地址
		$arr[0]['yhdj_b_dwdzhdh'] = $_POST['yhdj_b_dwdzhdh'];
		$this->mjlist = $arr;
        $this->display();
    }

	//列表页供能合同申请查看(面积)
	function mjck(){
		$id = intval($this->_param('id'));
		$list = D('Ht')->where('ht_id=' . $id)->find();
		$villagename = D('Survey_heating_address')->where('COMMUNITYCODE=' . $list['communitycode'])->find();
		$this->assign('address',$villagename['COMMUNITYNAME']);
		$this->assign($list);
		$this->display();
	}

	//列表页供能合同申请查看(计量)
	function jlck(){
		$id = intval($this->_param('id'));
		$list = D('Ht')->where('ht_id=' . $id)->find();
		$villagename = D('Survey_heating_address')->where('COMMUNITYCODE=' . $list['communitycode'])->find();
		$this->assign('address',$villagename['COMMUNITYNAME']);
		$this->assign($list);
		$this->display();
	}

	//修改页供能合同图片查看
	function tpck(){
		$id = intval($this->_param('id'));
		$key = intval($_GET['key']);
		$list = D('Ht_upload')->where('ht_id=' . $id)->limit($key,1)->select();
		$this->assign('list', $list);
		$this->display();
	}

	//供能合同申请预览(计量)
	function jlyl() {
		//小区名称
		$arr1 = explode("_",$_POST['communitcode']);
        $arr[0]['communitcode'] = $arr1[1];
		//使用方
		$arr[0]['syf'] = $_POST['syf'];
		//楼号
		$arr2 = explode("_",$_POST['buildcode']);
		if(empty($arr2[1])){
			$arr[0]['buildcode'] = $arr2[0];
		}
		else{
			$arr[0]['buildcode'] = $arr2[1];
		}
		//门
		$arr[0]['cellcode'] = $_POST['cellcode'];
		//号
		$arr[0]['doorplatecode'] = $_POST['doorplatecode'];
		//房屋类别
		$arr[0]['fwlx'] = $_POST['fwlx'];
		//采暖形式
		$arr[0]['cnxs'] = $_POST['cnxs'];
		//房间编号
		$housecode = $arr2[0]."-".$_POST['cellcode']."-".substr($_POST['doorplatecode'],0,2)."-".substr($_POST['doorplatecode'],2,2);
		//缴费面积
		$jfmj = R('Api/getJfmj',array($housecode));
		$arr[0]['jfmj'] = $jfmj[0]['CHARGEAREA'];
		//乙方签字
		$arr[0]['yfqz'] = $_POST['yfqz'];

		//房间编号
		$arr[0]['housecode'] = $housecode;
		//业主姓名
		$arr[0]['yhdj_a_yzxm'] = $_POST['yhdj_a_yzxm'];
		//证件类型
		$arr[0]['yhdj_a_zjlx'] = $_POST['yhdj_a_zjlx'];
		//证件号码
		$arr[0]['yhdj_a_zjhm'] = $_POST['yhdj_a_zjhm'];
		//移动电话
		$arr[0]['yhdj_a_yddh'] = $_POST['yhdj_a_yddh'];
		//固定电话
		$arr[0]['yhdj_a_gddh'] = $_POST['yhdj_a_gddh'];
		//邮寄地址
		$arr[0]['yhdj_a_yjdz'] = $_POST['yhdj_a_yjdz'];
		//邮政编号
		$arr[0]['yhdj_a_yb'] = $_POST['yhdj_a_yb'];
		//单位名称
		$arr[0]['yhdj_a_dwmc'] = $_POST['yhdj_a_dwmc'];
		//单位地址
		$arr[0]['yhdj_a_dwdzhdh'] = $_POST['yhdj_a_dwdzhdh'];
		//成员姓名
		$arr[0]['yhdj_a_zyjtcyxm'] = $_POST['yhdj_a_zyjtcyxm'];
		//联系电话
		$arr[0]['yhdj_a_zyjtcydh'] = $_POST['yhdj_a_zyjtcydh'];

		//业主姓名
		$arr[0]['yhdj_b_yzxm'] = $_POST['yhdj_b_yzxm'];
		//证件类型
		$arr[0]['yhdj_b_zjlx'] = $_POST['yhdj_b_zjlx'];
		//证件号码
		$arr[0]['yhdj_b_zjhm'] = $_POST['yhdj_b_zjhm'];
		//移动电话
		$arr[0]['yhdj_b_yddh'] = $_POST['yhdj_b_yddh'];
		//固定电话
		$arr[0]['yhdj_b_gddh'] = $_POST['yhdj_b_gddh'];
		//邮寄地址
		$arr[0]['yhdj_b_yjdz'] = $_POST['yhdj_b_yjdz'];
		//邮政编号
		$arr[0]['yhdj_b_yb'] = $_POST['yhdj_b_yb'];
		//单位名称
		$arr[0]['yhdj_b_dwmc'] = $_POST['yhdj_b_dwmc'];
		//单位地址
		$arr[0]['yhdj_b_dwdzhdh'] = $_POST['yhdj_b_dwdzhdh'];
		$this->mjlist = $arr;
        $this->display();
    }



	function microtime_float(){
		   list($usec, $sec) = explode(" ", microtime());
		   return ((float)$usec + (float)$sec);
	}

	//在线查询——历史费用
	public function lsfya()
	{
		$this->check();
		//dump( "第一组开始时间".date('y-m-d H:i:s.ms',time()));
		$houselist = R('User/fjbd', array($_SESSION['uid']));//用户绑定房间
		//dump( "第一组开始时间".date('y-m-d H:i:s.ms',time()));
		if(empty($houselist)) {
			$this->error('您没有绑定房间，不能进行在线查询!', '/index.php?s=/user/bind.html');
		}
		$this->id = intval($this->_param('id'));
		$this->pid = D('Cate')->where('id=' . $this->id)->getField('pid'); //左侧导航
		$this->nav = $this->nav($this->cate, $this->id, ''); //当前位置
		$p = get_parents($this->cate, $this->id);
		if (empty($p)) {
			$this->redirect("Public/404");
		}

		/* 能源类型 */
		//$this->powertype = R('Api/getPowerType');
		$this->now = time();
		/*start update by wangshipeng 2014.10.28*/
		//当月明细
		//dump( "第2组开始时间".date('y-m-d H:i:s.ms',time()));
		$houselist = R('User/fjbd', array($_SESSION['uid']));
		//dump( "第2组开始时间".date('y-m-d H:i:s.ms',time()));
		$count = count($houselist);//统计数组长度
		$arr = array();
		$accountTime = null ;
		foreach($houselist as $key=>$val) {
			//当月明细
			/* trunc(SUM(dncnf), 2) dncnf, --当期采暖费本金
                 trunc(SUM(zqndcnf), 2) zqndcnf, --之前采暖费欠费本金
                 trunc(SUM(zqndcnfznj), 2) zqndcnfznj, --之前采暖费欠费滞纳金
                 trunc(SUM(zqndcnfzj), 2) zqndcnfzj, --采暖费应缴金额
                 SUM(dysf) dysf,--当期水费本金
                 SUM(zqysf) zqysf,--之前水费欠费本金
                 SUM(zqysfznj) zqysfznj,--之前水费欠费滞纳金
                 SUM(zqysfzj) zqysfzj,--水费应缴金额
                 SUM(dyrqf) dyrqf,--当期燃气费本金
                 SUM(zqyrqf) zqyrqf,--之前燃气费欠费本金
                 SUM(zqyrqfznj) zqyrqfznj,--之前燃气费欠费滞纳金
                 SUM(zqyrqfzj) zqyrqfzj,--燃气费应缴金额
                 SUM(rqyl) rqyl,--燃气用量
                 SUM(sfyl) sfyl,--水用量
                 SUM(cnfyl) cnfyl,--采暖面积
                 sum(dyrjlfy)dyrjlfy,--当期热计量费用
                 sum(zqyrjlfy)zqyrjlfy,--之前热计量欠费本金
                 sum(zqyrjlznj)zqyrjlznj,--之前热计量滞纳金
                 sum(zqyrjlzj)zqyrjlzj,--热计量应缴金额
                 sum(rjlyl) rjlyl,--热计量用量*/
			//dump( "第3组开始时间".date('y-m-d H:i:s.ms',time()));
			$list = R('Api/getOweCharge', array($val['houseCode']));
			//dump( "第3组开始时间".date('y-m-d H:i:s.ms',time()));
			$arr[$key]['DNCNF'] = $list[0]['DNCNF'];
			$arr[$key]['CNFYL'] = $list[0]['CNFYL'];
			$arr[$key]['ZQNDCNFZJ'] =  $list[0]['ZQNDCNFZJ'];
			$arr[$key]['ZQNDCNF'] = $list[0]['ZQNDCNF'];
			$arr[$key]['ZQNDCNFZNJ'] = $list[0]['ZQNDCNFZNJ'];
			$arr[$key]['DYSF'] = $list[0]['DYSF'];
			$arr[$key]['SFYL'] = $list[0]['SFYL'];
			$arr[$key]['ZQYSFZJ'] = $list[0]['ZQYSFZJ'] ;
			$arr[$key]['ZQYSF'] = $list[0]['ZQYSF'];
			$arr[$key]['ZQYSFZNJ'] = $list[0]['ZQYSFZNJ'];
			$arr[$key]['DYRQF'] = $list[0]['DYRQF'];
			$arr[$key]['RQYL'] = $list[0]['RQYL'];
			$arr[$key]['ZQYRQFZJ'] = $list[0]['ZQYRQFZJ'] ;
			$arr[$key]['ZQYRQF'] = $list[0]['ZQYRQF'];
			$arr[$key]['ZQYRQFZNJ'] = $list[0]['ZQYRQFZNJ'];
			$arr[$key]['DQSUM'] = $list[0]['DYSF']+$list[0]['DYRQF']+$list[0]['DNCNF']+$list[0]['DYRJLFY'];
			$arr[$key]['QFBJSUM'] = $list[0]['ZQYSF']+$list[0]['ZQYRQF']+$list[0]['ZQNDCNF'];
			$arr[$key]['ZNJSUM'] = $list[0]['ZQYSFZNJ']+$list[0]['ZQYRQFZNJ']+$list[0]['ZQNDCNFZNJ'];

			//$arr[$key]['HOUSENAME'] = $val['houseName'];
			/*-- update wangshipeng 2015.-3.12   start*/
			//dump( "第4组开始时间".date('y-m-d H:i:s.ms',time()));
			$value = R('Api/getAddress', array($list[0]['HOUSECODE']));
			//dump( "第4组开始时间".date('y-m-d H:i:s.ms',time()));
			$arr[$key]['HOUSENAME'] = $value[0]['ADDRESS'];
			/*-- update wangshipeng 2015.-3.12   end*/
			$arr[$key]['CHARGEMONTH'] = $list[0]['CHARGEMONTH'];
			$accountTime = $list[0]['CHARGEMONTH'];

			/*-- 供热分开显示   */
			$arr[$key]['GRMJDJ'] = $list[0]['GRMJDJ'];
			$arr[$key]['ZLSDJ'] = $list[0]['ZLSDJ'];
			$arr[$key]['TRQDJ'] = $list[0]['TRQDJ'];
			$arr[$key]['RJLDJ'] = number_format($list[0]['RJLDJ'],4);
			$arr[$key]['RJLYL'] = $list[0]['RJLYL'];
			$arr[$key]['ZQYRJLFY'] = $list[0]['ZQYRJLFY'];
			$arr[$key]['ZQYRJLZNJ'] = $list[0]['ZQYRJLZNJ'];

			$arr[$key]['DYRJLFY'] = $list[0]['DYRJLFY'];
			$arr[$key]['ZQYRJLZJ'] = $list[0]['ZQYRJLZJ'];
			//$arr[$key]['RJLSUM'] = $list[0]['ZQYRJLFY']+$list[0]['ZQYRJLZNJ']+$list[0]['ZQYRJLFY']+$list[0]['DYRJLFY'];
			$arr[$key]['YJJESUM'] = $arr[$key]['ZQNDCNFZJ']+$arr[$key]['ZQYSFZJ']+$arr[$key]['ZQYRQFZJ']+$arr[$key]['ZQYRJLZJ'];
			/*  --*/

		}

		/*-- 修改显示日期   */
		$tempaa = $accountTime."01";
		$startTime =date('Y年m',strtotime("$tempaa -1 month "))."月14日";
		$accountYear = substr($accountTime,0,4);
		$accountMonth = substr($accountTime,4,2);
		$endTime = $accountYear . '年' . $accountMonth . '月' .'20日';
		/* 修改显示日期   --*/
		foreach ($houselist as $v) {
			$hlist[] = $v['houseCode'];
			$hlist[] = $v['houseName'];
		}
		//历史账单
		$arr1 = array();
		$houseCode = $_POST['houseCode'];
		$itemCode = $_POST['itemCode'];
		$age = $_POST['year'];
		$moon = $_POST['month'];
		$chargeMonth = $_POST['year'] . $_POST['month'];
		if (empty($chargeMonth)) {
			$chargeMonth = date('Y', time()) . date('m', time());
		}
		if($_POST['houseCode'] == '' || $_POST['houseCode'] == NULL){
			$houseCode = $houselist[0]['houseCode'];
			$moon = date('Y',time());
		}
		/*$backYear = substr($chargeMonth,0,4)-1;
		$backMonth = substr($chargeMonth,4,2);
		$backTime = $backYear . $backMonth;

		dump( "第5组开始时间".date('y-m-d H:i:s',time()));
		$historylist = R('Api/getLineChartDatasByHouse', array($houseCode,$itemCode));
		dump( "第5组开始时间".date('y-m-d H:i:s',time()));
		for($i=0;$i<count($historylist);$i++){
			$hisyear = substr($historylist[$i]['CHARGEMONTH'],0,4);
			$hismonth = substr($historylist[$i]['CHARGEMONTH'],4,2);
			if((($itemCode == 'A') && ($age == $hisyear)) || (($itemCode != 'A') && ($age == $hisyear) && (empty($moon) || ($moon == $hismonth)))){
				$arr1[$i]['ADDRESS'] = $historylist[$i]['ADDRESS'];
				$arr1[$i]['ITEMCODE'] = $historylist[$i]['ITEMCODE'];
				$arr1[$i]['CHARGEMONTH'] = $historylist[$i]['CHARGEMONTH'];
				$arr1[$i]['QF'] = $historylist[$i]['QF'];
				$arr1[$i]['NOWCASH'] = $historylist[$i]['NOWCASH'];
				$arr1[$i]['REALACCOUNT'] = $historylist[$i]['REALACCOUNT'];
				$this->qf_count += $historylist[$i]['QF'];
				$this->nowcash_count += $historylist[$i]['NOWCASH'];
				$this->realaccount_count += $historylist[$i]['REALACCOUNT'];
			}
		}
		*/
		//$houseCode = json_encode($hlist);
		$this->notice = '(结算周期' . $startTime ."-". $endTime . ')';
		$this->houselist = $houselist;
		$this->historylist = $arr1;
		$this->feelist = $arr;
		$this->code = $houseCode;
		$this->itemCode = $itemCode;
		$this->age = $age;
		$this->moon = $moon;
		$this->year = date('Y', time());
		$this->month = date('m', time());
		$this->monthList = array("01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12");
		/*end update by wangshipeng 2014.10.28*/

		/*-- 增加显示当期账号号*/
		$this-> current = $accountTime;
		/* --*/

		$this->topid = $p[0]['id']; //顶部导航选中效果
		$this->display();
	}

	public function zsjf(){
		$this->check();
		$houselist = R('User/fjbd', array($_SESSION['uid']));//用户绑定房间
		if(empty($houselist)) {
			$this->error('您没有绑定房间，不能进行在线查询!', '/index.php?s=/user/bind.html');
		}
		$this->id = intval($this->_param('id'));
		$this->pid = D('Cate')->where('id=' . $this->id)->getField('pid'); //左侧导航
		$this->nav = $this->nav($this->cate, $this->id, ''); //当前位置
		$p = get_parents($this->cate, $this->id);
		if (empty($p)) {
			$this->redirect("Public/404");
		}
		/* 能源类型 */
		$this->powertype = R('Api/getPowerType');

		for($j=0;$j<count($houselist);$j++){
			//历史账单
			$arr1 = array();
			$temNum = 0;
			$historylist = R('Api/getQfxxShui', array($houselist[$j]['houseCode']));

			for ($i = 0; $i < count($historylist); $i++) {
				if($i==0){
					$arr1[$temNum]['payOn']='on';
				}else{
					$arr1[$temNum]['payOn']='off';
				}
				$arr1[$temNum]['YONG_LIANG'] = $historylist[$i]['ZLS_YL'];
				if(strpos($historylist[$i]['ZLS_QF'],'.')==0){
					$arr1[$temNum]['QIAN'] = '0'.$historylist[$i]['ZLS_QF'];
				}else{
					$arr1[$temNum]['QIAN'] = $historylist[$i]['ZLS_QF'];
				}
				$shui = R('Api/getShuiStatus', array($historylist[$i]['HOUSECODE']));

				$arr1[$temNum]['SHOW'] = 0;
				$arr1[$temNum]['HOUSECODE'] = $historylist[$i]['HOUSECODE'];
				$arr1[$temNum]['CHARGEMONTH'] = $historylist[$i]['CHARGEMONTH'];
				$arr1[$temNum]['CLOCKNUMBER'] = $historylist[$i]['CLOCKNUMBER'];
				$arr1[$temNum]['STATUS'] = $shui[0]['COU'];
				$arr1[$temNum]['TYPE'] = 'B';
				$arr1[$temNum]['TYPENMAE'] = '水费';
				$arr1[$temNum]['aa'] = $temNum%2;
				if($historylist[$i]['HH24H']>='10' && $historylist[$i]['HH24H']<='17'){
					$arr1[$temNum]['hh24h'] = "on";
				}else{
					$arr1[$temNum]['hh24h'] = "off";
				}
				$temNum++;
			}
			$historylist = R('Api/getQfxxQi', array($houselist[$j]['houseCode']));
			for ($i = 0; $i < count($historylist); $i++) {
				if($i==0){
					$arr1[$temNum]['payOn']='on';
				}else{
					$arr1[$temNum]['payOn']='off';
				}
				$arr1[$temNum]['YONG_LIANG'] = $historylist[$i]['TRQ_YL'];
				if(strpos($historylist[$i]['TRQ_QF'],'.')==0){
					$arr1[$temNum]['QIAN'] = '0'.$historylist[$i]['TRQ_QF'];
				}else{
					$arr1[$temNum]['QIAN'] = $historylist[$i]['TRQ_QF'];
				}
				$qi = R('Api/getQiStatus', array($historylist[$i]['HOUSECODE']));

				$arr1[$temNum]['SHOW'] = 0;
				$arr1[$temNum]['HOUSECODE'] = $historylist[$i]['HOUSECODE'];
				$arr1[$temNum]['CHARGEMONTH'] = $historylist[$i]['CHARGEMONTH'];
				$arr1[$temNum]['CLOCKNUMBER'] = $historylist[$i]['CLOCKNUMBER'];
				$arr1[$temNum]['STATUS'] = $qi[0]['COU'];
				$arr1[$temNum]['TYPE'] = 'D';
				$arr1[$temNum]['TYPENMAE'] = '天然气';
				$arr1[$temNum]['aa'] = $temNum%2;
				if($historylist[$i]['HH24H']>='10' && $historylist[$i]['HH24H']<='17'){
					$arr1[$temNum]['hh24h'] = "on";
				}else{
					$arr1[$temNum]['hh24h'] = "off";
				}
				$temNum++;
			}
			$historylist = R('Api/getQfxxRe', array($houselist[$j]['houseCode']));

			for ($i = 0; $i < count($historylist); $i++) {
				if($i==0){
					$arr1[$temNum]['payOn']='on';
				}else{
					$arr1[$temNum]['payOn']='off';
				}
				$arr1[$temNum]['YONG_LIANG'] = $historylist[$i]['GN_MJ'];
				if(strpos($historylist[$i]['GN_QF'],'.')==0){
					$arr1[$temNum]['QIAN'] = '0'.$historylist[$i]['GN_QF'];
				}else{
					$arr1[$temNum]['QIAN'] = $historylist[$i]['GN_QF'];
				}
				$re = R('Api/getReStatus', array($historylist[$i]['HOUSECODE']));

				if($historylist[$i]['CHARGEMONTH'] == '2018' && (strtotime(date('Y-m-d H:i:s'))<=strtotime("2018-6-1 00:00:00"))){
					$arr1[$temNum]['SHOW'] = 1;
				}
				else{
					$arr1[$temNum]['SHOW'] = 0;
				}
				$arr1[$temNum]['HOUSECODE'] = $historylist[$i]['HOUSECODE'];
				$arr1[$temNum]['CHARGEMONTH'] = $historylist[$i]['CHARGEMONTH'];
				$arr1[$temNum]['CLOCKNUMBER'] = $historylist[$i]['CLOCKNUMBER'];
				$arr1[$temNum]['STATUS'] = $re[0]['COU'];
				$arr1[$temNum]['TYPE'] = 'A';
				$arr1[$temNum]['TYPENMAE'] = '采暖';
				if($historylist[$i]['HH24H']>='10' && $historylist[$i]['HH24H']<='17'){
					$arr1[$temNum]['hh24h'] = "on";
				}else{
					$arr1[$temNum]['hh24h'] = "off";
				}
				$arr1[$temNum]['aa'] = $temNum%2;
				$temNum++;
			}

			$houselist[$j]['feiyong']=$arr1;
		}
		//dump($houselist);
		$this->houselist = $houselist;
		$this->topid = $p[0]['id']; //顶部导航选中效果
		$this->display();
	}




	/*public function zsjf1(){

		$this->check();
		$housecode = $_POST['houseCode'];
		$houselist = R('User/fjbd', array($_SESSION['uid']));//用户绑定房间
		$list = $houselist;
		if(empty($houselist)) {
			$this->error('您没有绑定房间，不能进行在线查询!', '/index.php?s=/user/bind.html');
		}
		if(empty($housecode)){
			$housecode = $houselist[0]['houseCode'];
		}
		$houselist = R('User/zdfj', array($_SESSION['uid'],$housecode));//用户绑定房间

		$this->id = intval(I('get.id'));
		$this->pid = D('Cate')->where('id=' . $this->id)->getField('pid'); //左侧导航
		$this->nav = $this->nav($this->cate, $this->id, ''); //当前位置
		$p = get_parents($this->cate, $this->id);

		//能源类型
		$this->powertype = R('Api/getPowerType');

		for($j=0;$j<count($houselist);$j++){

				//历史账单
				$arr1 = array();
				$temNum = 0;
				$historylist = R('Api/getQfxxShui', array($houselist[$j]['houseCode']));


				for ($i = 0; $i < count($historylist); $i++) {
					if($i==0){
						$arr1[$temNum]['payOn']='on';
					}else{
						$arr1[$temNum]['payOn']='off';
					}
					$arr1[$temNum]['YONG_LIANG'] = $historylist[$i]['ZLS_YL'];
					if(strpos($historylist[$i]['ZLS_QF'],'.')==0){
						$arr1[$temNum]['QIAN'] = '0'.$historylist[$i]['ZLS_QF'];
					}else{
						$arr1[$temNum]['QIAN'] = $historylist[$i]['ZLS_QF'];
					}
					$shui = R('Api/getShuiStatus', array($historylist[$i]['HOUSECODE']));

					$arr1[$temNum]['SHOW'] = 0;
					$arr1[$temNum]['HOUSECODE'] = $historylist[$i]['HOUSECODE'];
					$arr1[$temNum]['CHARGEMONTH'] = $historylist[$i]['CHARGEMONTH'];
					$arr1[$temNum]['CLOCKNUMBER'] = $historylist[$i]['CLOCKNUMBER'];
					$arr1[$temNum]['STATUS'] = $shui[0]['COU'];
					$arr1[$temNum]['TYPE'] = 'B';
					$arr1[$temNum]['TYPENMAE'] = '水费';
					$arr1[$temNum]['aa'] = $temNum%2;
					if($historylist[$i]['HH24H']>='10' && $historylist[$i]['HH24H']<='16'){
						$arr1[$temNum]['hh24h'] = "on";
					}else{
						$arr1[$temNum]['hh24h'] = "off";
					}
					$temNum++;
				}
				$historylist = R('Api/getQfxxQi', array($houselist[$j]['houseCode']));
				for ($i = 0; $i < count($historylist); $i++) {
					if($i==0){
						$arr1[$temNum]['payOn']='on';
					}else{
						$arr1[$temNum]['payOn']='off';
					}
					$arr1[$temNum]['YONG_LIANG'] = $historylist[$i]['TRQ_YL'];
					if(strpos($historylist[$i]['TRQ_QF'],'.')==0){
						$arr1[$temNum]['QIAN'] = '0'.$historylist[$i]['TRQ_QF'];
					}else{
						$arr1[$temNum]['QIAN'] = $historylist[$i]['TRQ_QF'];
					}
					$qi = R('Api/getQiStatus', array($historylist[$i]['HOUSECODE']));

					$arr1[$temNum]['SHOW'] = 0;
					$arr1[$temNum]['HOUSECODE'] = $historylist[$i]['HOUSECODE'];
					$arr1[$temNum]['CHARGEMONTH'] = $historylist[$i]['CHARGEMONTH'];
					$arr1[$temNum]['CLOCKNUMBER'] = $historylist[$i]['CLOCKNUMBER'];
					$arr1[$temNum]['STATUS'] = $qi[0]['COU'];
					$arr1[$temNum]['TYPE'] = 'D';
					$arr1[$temNum]['TYPENMAE'] = '天然气';
					$arr1[$temNum]['aa'] = $temNum%2;
					if($historylist[$i]['HH24H']>='10' && $historylist[$i]['HH24H']<='16'){
						$arr1[$temNum]['hh24h'] = "on";
					}else{
						$arr1[$temNum]['hh24h'] = "off";
					}
					$temNum++;
				}
				$historylist = R('Api/getQfxxRe', array($houselist[$j]['houseCode']));

				for ($i = 0; $i < count($historylist); $i++) {
					if($i==0){
						$arr1[$temNum]['payOn']='on';
					}else{
						$arr1[$temNum]['payOn']='off';
					}
					$arr1[$temNum]['YONG_LIANG'] = $historylist[$i]['GN_MJ'];
					if(strpos($historylist[$i]['GN_QF'],'.')==0){
						$arr1[$temNum]['QIAN'] = '0'.$historylist[$i]['GN_QF'];
					}else{
						$arr1[$temNum]['QIAN'] = $historylist[$i]['GN_QF'];
					}
					$re = R('Api/getReStatus', array($historylist[$i]['HOUSECODE']));
					$arr1[$temNum]['HOUSECODE'] = $historylist[$i]['HOUSECODE'];
					$arr1[$temNum]['CHARGEMONTH'] = $historylist[$i]['CHARGEMONTH'];
					$arr1[$temNum]['CLOCKNUMBER'] = $historylist[$i]['CLOCKNUMBER'];
					$arr1[$temNum]['STATUS'] = $re[0]['COU'];
					$arr1[$temNum]['TYPE'] = 'A';
					$arr1[$temNum]['TYPENMAE'] = '供热';
					if($historylist[$i]['CHARGEMONTH'] == '2018' && (strtotime(date('Y-m-d H:i:s'))<=strtotime("2018-6-1 00:00:00"))){
						$arr1[$temNum]['SHOW'] = 1;
					}
					else{
						$arr1[$temNum]['SHOW'] = 0;
					}
					if($historylist[$i]['HH24H']>='10' && $historylist[$i]['HH24H']<='16'){
						$arr1[$temNum]['hh24h'] = "on";
					}else{
						$arr1[$temNum]['hh24h'] = "off";
					}
					$arr1[$temNum]['aa'] = $temNum%2;
					$temNum++;
				}

				$houselist[$j]['feiyong']=$arr1;
		}

		$this->housecode = $housecode;
		$this->list = $list;
		$this->houselist = $houselist;
		//$this->topid = $p[0]['id']; //顶部导航选中效果
		$this->display();
	}*/

	public function zsjf1(){
		$this->check();
		$data = array();
		$data1 = array();
		$data2 = array();
		$houselist = R('User/fjbd', array($_SESSION['uid']));//用户绑定房间
		if(empty($houselist)) {
			$this->error('您没有绑定房间，不能进行在线查询!', '/index.php?s=/user/bind.html');
		}

        $this->id = intval($this->_param('id'));
        $this->pid = D('Cate')->where('id=' . $this->id)->getField('pid'); //左侧导航
        $this->nav = $this->nav($this->cate, $this->id, ''); //当前位置
        $p = get_parents($this->cate, $this->id);
        /*if (empty($p)) {
            $this->redirect("Public/404");
        }*/
		$houseCode = $_REQUEST['houseCode'];
		$hcode=$_POST['hcode'];
		
        if($_REQUEST['houseCode'] == '' || $_REQUEST['houseCode'] == NULL){
			if($hcode!="")
			//$houseCode = $houselist[0]['houseCode'];
			{$houseCode=$hcode;}
			else
			{$houseCode = $houselist[0]['houseCode'];}

		}

		$shuiHis=$qiHis=$zaiHis=0;
		$yingyeDate=date('H');
		$result = R('Api/getNowData', array($houseCode));
		for($k = 0; $k < count($result->{'r_body'}); $k++){
			if($yingyeDate >= 10 && $yingyeDate <= 17){
				$data[$k]['hh24h'] ='on';
			}else{
				$data[$k]['hh24h'] ='off';
			}
			$data[$k]['HOUSECODE'] = $result->{'r_body'}[$k]->{"HOUSECODE"};
			$data[$k]['CHARGEMONTH'] = $result->{'r_body'}[$k]->{"CHARGEMONTH"};
			$data[$k]['ITEMNAME'] = $result->{'r_body'}[$k]->{"ITEMNAME"};
			if($data[$k]['ITEMNAME'] == '自来水'){
				if($shuiHis == 0){
					//判断是否历史欠费
					$data[$k]['payOn'] ='on';
					$shuiHis = 1;
				}else{
					$data[$k]['payOn'] ='off';
				}
				//判断是否锁定
				$shui = R('Api/getShuiStatus', array($data[$k]['HOUSECODE']));
				$data[$k]['STATUS'] = $shui[0]['COU'];
			}
			if($data[$k]['ITEMNAME'] == '天然气'){
				if($qiHis == 0){
					//判断是否历史欠费
					$data[$k]['payOn'] ='on';
					$qiHis = 1;
				}else{
					$data[$k]['payOn'] ='off';
				}
				//判断是否锁定
				$qi = R('Api/getQiStatus', array($data[$k]['HOUSECODE']));
				$data[$k]['STATUS'] = $qi[0]['COU'];
			}
			if($data[$k]['ITEMNAME'] == '再生水'){
				if($zaiHis == 0){
					//判断是否历史欠费
					$data[$k]['payOn'] ='on';
					$zaiHis = 1;
				}else{
					$data[$k]['payOn'] ='off';
				}
				$data[$k]['STATUS'] = 0;
			}
			$data[$k]['POINTNUMBER'] = $result->{'r_body'}[$k]->{"POINTNUMBER"};
			$data[$k]['DQFY'] = $result->{'r_body'}[$k]->{"DQFY"};
			$data[$k]['WYJ'] = $result->{'r_body'}[$k]->{"WYJ"};
			$data[$k]['QFZT'] = $result->{'r_body'}[$k]->{"QFZT"};
			$data[$k]['PRICE'] = $result->{'r_body'}[$k]->{"PRICE"};
			$data[$k]['ITEMCODE'] = $result->{'r_body'}[$k]->{"ITEMCODE"};
			$data[$k]['CLOCKNUMBER'] = $result->{'r_body'}[$k]->{"CLOCKNUMBER"};
			$data[$k]['DQXJ'] = $result->{'r_body'}[$k]->{"DQXJ"};
		}

		//查询供热
		$resultGr = R('Api/getGrData', array($houseCode));

		for($k = 0; $k < count($resultGr->{'r_body'}); $k++){
			if($k == 0){
				$dataGr[$k]['payOn'] ='on';
			}else{
				$dataGr[$k]['payOn'] ='off';
			}
			if($yingyeDate >= 10 && $yingyeDate <= 17){
				$dataGr[$k]['hh24h'] ='on';
			}else{
				$dataGr[$k]['hh24h'] ='off';
			}
			$re = R('Api/getReStatus', array($historylist[$i]['HOUSECODE']));
			$dataGr[$k]['STATUS'] = $re[0]['COU'];
			$dataGr[$k]['HOUSECODE'] = $resultGr->{'r_body'}[$k]->{"HOUSECODE"};
			$dataGr[$k]['CHARGEMONTH'] = $resultGr->{'r_body'}[$k]->{"CHARGEMONTH"};
			$dataGr[$k]['ITEMNAME'] = $resultGr->{'r_body'}[$k]->{"ITEMNAME"};
			$dataGr[$k]['POINTNUMBER'] = $resultGr->{'r_body'}[$k]->{"POINTNUMBER"};
			$dataGr[$k]['QFZT'] = $resultGr->{'r_body'}[$k]->{"QFZT"};
			$dataGr[$k]['DQFY'] = $resultGr->{'r_body'}[$k]->{"DQFY"};
			$dataGr[$k]['PRICE'] = $resultGr->{'r_body'}[$k]->{"PRICE"};
			$dataGr[$k]['ITEMCODE'] = $resultGr->{'r_body'}[$k]->{"ITEMCODE"};
			$dataGr[$k]['CLOCKNUMBER'] = $resultGr->{'r_body'}[$k]->{"CLOCKNUMBER"};
			$dataGr[$k]['YHJM'] = $resultGr->{'r_body'}[$k]->{"YHJM"};
			$dataGr[$k]['MINUSMONEY'] = $resultGr->{'r_body'}[$k]->{"MINUSMONEY"};
			$dataGr[$k]['IMP'] = $resultGr->{'r_body'}[$k]->{"IMP"};
			$dataGr[$k]['MON'] = $resultGr->{'r_body'}[$k]->{"MON"};
			$dataGr[$k]['PLS'] = $resultGr->{'r_body'}[$k]->{"PLS"};
			$dataGr[$k]['WYJ'] = $resultGr->{'r_body'}[$k]->{"WYJ"};
			$dataGr[$k]['DNBS'] = $resultGr->{'r_body'}[$k]->{"DNBS"};
			$dataGr[$k]['DQXJ'] = $resultGr->{'r_body'}[$k]->{"DQXJ"};
			$dataGr[$k]['XJ1'] = $dataGr[$k]['PLS']-$dataGr[$k]['YHJM'];
			$dataGr[$k]['XJ2'] = $dataGr[$k]['XJ1']-$dataGr[$k]['MINUSMONEY'];
			$dataGr[$k]['HJ'] = $dataGr[$k]['XJ2']-$dataGr[$k]['IMP'];

		}
		/*for($k = 1; $k <= count($resultGr->{'r_body'}); $k++){
			$dataGr[$k]['HOUSECODE'] = $resultGr->{'r_body'}[0]->{"HOUSECODE"};
			$dataGr[$k]['CHARGEMONTH'] = $resultGr->{'r_body'}[0]->{"CHARGEMONTH"};
			$dataGr[$k]['ITEMNAME'] = $resultGr->{'r_body'}[0]->{"ITEMNAME"};
			$dataGr[$k]['POINTNUMBER'] = $resultGr->{'r_body'}[0]->{"POINTNUMBER"};
			$dataGr[$k]['DQFY'] = $resultGr->{'r_body'}[0]->{"DQFY"};
			$dataGr[$k]['PRICE'] = $resultGr->{'r_body'}[0]->{"PRICE"};
			$dataGr[$k]['ITEMCODE'] = $resultGr->{'r_body'}[0]->{"ITEMCODE"};
			$dataGr[$k]['CLOCKNUMBER'] = $resultGr->{'r_body'}[0]->{"CLOCKNUMBER"};
			$dataGr[$k]['YHJM'] = $resultGr->{'r_body'}[0]->{"YHJM"};
			$dataGr[$k]['MINUSMONEY'] = $resultGr->{'r_body'}[0]->{"MINUSMONEY"};
			$dataGr[$k]['IMP'] = $resultGr->{'r_body'}[0]->{"IMP"};
			$dataGr[$k]['MON'] = $resultGr->{'r_body'}[0]->{"MON"};
			$dataGr[$k]['PLS'] = $resultGr->{'r_body'}[0]->{"PLS"};
		}*/

		$result1 = R('Api/getPriceData', array($houseCode));

		$data1['ZLSYJ'] = number_format($result1->{'r_body'}[0]->{"ZLSYJ"},2);
		$data1['ZLSEJ'] = number_format($result1->{'r_body'}[0]->{"ZLSEJ"},2);
		$data1['ZLSSJ'] = number_format($result1->{'r_body'}[0]->{"ZLSSJ"},2);
		$data1['TRQYJ'] = number_format($result1->{'r_body'}[0]->{"TRQYJ"},2);
		$data1['TRQEJ'] = number_format($result1->{'r_body'}[0]->{"TRQEJ"},2);
		$data1['TRQSJ'] = number_format($result1->{'r_body'}[0]->{"TRQSJ"},2);
		$data1['ITEMCODE'] = $result1->{'r_body'}[0]->{"ITEMCODE"};
		$data1['POINTNUMBER_B'] = $result1->{'r_body'}[0]->{"POINTNUMBER_B"};
		$data1['POINTNUMBER_D'] = $result1->{'r_body'}[0]->{"POINTNUMBER_D"};

		$result2 = R('Api/getNum', array($houseCode));

		for($k = 0; $k < count($result2->{'r_body'}); $k++){
			$data2[$k]['ITEMCODE'] = $result2->{'r_body'}[$k]->{"ITEMCODE"};
			$data2[$k]['NOWPOINTNUMBER'] = $result2->{'r_body'}[$k]->{"NOWPOINTNUMBER"};
			$data2[$k]['CLOCKNUMBER'] = $result2->{'r_body'}[$k]->{"CLOCKNUMBER"};
		}

		//查询供热百分比图表
		$nowdate = time();
		$jiudate = date('Y').'-09-15';
		$shiyidate = date('Y').'-11-15';
		if($nowdate > strtotime($jiudate) && $nowdate <= strtotime($shiyidate)){
			$grData['allTotal'] = '--';
			$grData['startNum'] = '--';
			$this->bfb = 0;
		
		}
		else{
			$grMap['housecode']=$houseCode;
			$grData = D('EnergyMeter')->where($grMap)->order('id desc')->find();
			$resultb = R('Api/getBFB', array($houseCode,$grData['allTotal']));
			$this->bfb = $resultb->{'r_body'}[0]->{"PROPORTION"};
		}

		$this->numlist = $data2;
		$this->pricelist = $data1;
		$this->grlist = $dataGr;
		$this->nowlist = $data;
        $this->houselist = $houselist;
		$this->code = $houseCode;
		$this->grData = $grData;

        $this->topid = $p[0]['id']; //顶部导航选中效果
        $this->display();

	}

	public function jjPage(){
		$this->check();
		$this->id = $_REQUEST['id'];
		$this->pid = D('Cate')->where('id=' . $this->id)->getField('pid'); //左侧导航
		$this->nav = $this->nav($this->cate, $this->id, ''); //当前位置
		$p = get_parents($this->cate, $this->id);
		if (empty($p)) {
			$this->redirect("Public/404");
		}
		$housecode=$_POST['housecode'];
		$type=$_POST['type'];
		$clocknumber=$_POST['qian'];
		$chargemonth=$_POST['chargemonth'];
		$info = D('UserRoom')->where("houseCode='$housecode'")->find();
		$typeName = '天然气';
		if($type=='B'){
			$typeName = '水费';
		}else if($type=='A'){
			$typeName = '采暖';
		}
		$this->info=$info;
		$this->type=$type;
		$this->typeName=$typeName;
		$this->dateInfo=date("Y-m-d",time());
		$this->clocknumber=$clocknumber;
		$this->chargemonth=$chargemonth;
		$this->display();
	}


	public function jjPage1(){
		$this->check();
		$this->id = $_REQUEST['id'];
		$this->pid = D('Cate')->where('id=' . $this->id)->getField('pid'); //左侧导航
		$this->nav = $this->nav($this->cate, $this->id, ''); //当前位置
		$p = get_parents($this->cate, $this->id);
		if (empty($p)) {
			$this->redirect("Public/404");
		}
		$housecode=$_POST['housecode'];
		$type=$_POST['type'];
		$clocknumber=$_POST['qian'];
		$chargemonth=$_POST['chargemonth'];
		$bh=$_POST['clocknumber'];
		$info = D('UserRoom')->where("houseCode='$housecode'")->find();
		$typeName = '天然气';
		if($type=='B'){
			$typeName = '水费';
		}else if($type=='A'){
			$typeName = '采暖';
		}

		$url = 'http://10.105.15.2/tjsck_impl/QuertQiangfeiYlServlet';


        $post_data['vhousecode'] = $housecode;
        $post_data['vchargemonth'] = $chargemonth;
        $post_data['vitemcode'] = $type;
		$post_data['vclocknumber'] = $bh;
		//print_r($bh);
		$map['houseCode'] = $housecode;
        $map['chargeMonth'] = $chargemonth;
        $map['type'] = $type;
		$map['clockNumber'] = $bh;

		$infor = M('pay_yl_info')->where($map)->find();

		if(!empty($infor)){
			$this->error('当前时间的费用已缴纳,请勿重复缴费!', '/index.php?s=/page/zsjf1/id/210.html');
		}

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
		$fjbh = $housecode;
		$sj = $chargemonth;
		/*$fjbh = substr($housecode,0,4).substr($housecode,5,3).substr($housecode,9,2).substr($housecode,12,2).substr($housecode,15,2);
		$sj = str_pad($chargemonth,6,0,STR_PAD_RIGHT);

		$str = $fjbh.$type.$sj.$bh;
		$cd = strlen($str)-20;
		$houseCode = substr($str,0,13);
		$type = substr($str,13,1);
		$chargeMonth = substr($str,14,6);
		$clockNumber = substr($str,20,$cd);
		print_r($houseCode.'@'.$type.'@'.$chargeMonth.'@'.$clockNumber);*/
		$orderId = $res->{'r_body'}[0]->{"LSH"};
		$this->orderid=$orderId;
		$this->info=$info;
		$this->housecode=$fjbh;
		$this->type=$type;
		$this->typeName=$typeName;
		$this->dateInfo=date("Y-m-d",time());
		$this->clocknumber=$clocknumber;
		$this->txnAmt=$totalmoney*100;
		$this->chargemonth=$sj;
		$this->bh=$bh;
		$this->display();
	}
 public function ceshilszd()
    {
        $this->check();
		$houselist = R('User/fjbd', array($_SESSION['uid']));//用户绑定房间
		if(empty($houselist)) {
			$this->error('您没有绑定房间，不能进行在线查询!', '/index.php?s=/user/bind.html');
		}
        $this->id = intval($this->_param('id'));
        $this->pid = D('Cate')->where('id=' . $this->id)->getField('pid'); //左侧导航
        $this->nav = $this->nav($this->cate, $this->id, ''); //当前位置
        $p = get_parents($this->cate, $this->id);
        if (empty($p)) {
            $this->redirect("Public/404");
        }

        $this->cid = $this->_get('cid');

        $this->urre = $_SESSION['cardnum'];

        /* 能源类型 */
        $this->powertype = R('Api/getPowerType');
        $this->now = time();

		//历史账单
		$zlslist = array();//自来水
		$trqlist = array();//天然气
		$relist = array();//供暖
		$relistto=array();//新加的热量费用
		$jllist = array();//供暖计量
		$zsslist = array();//再生水
		$houseCode = $_POST['houseCode'];

        if($_POST['houseCode'] == '' || $_POST['houseCode'] == NULL){
			
            $houseCode = $houselist[0]['houseCode'];
        }
$itemCode = $_POST['itemCode'];
        if (empty($itemCode)) {
            $itemCode = $_GET['itemCode'];
            if (empty($itemCode)) {
                $itemCode = "B";
            }
        }
        $chargeMonthSe = R('Api/GetHouseChargemonthServlet', array($houseCode, $itemCode));//print_r($chargeMonthSe);
        for ($i = 0; $i < count($chargeMonthSe->{'r_body'}); $i++) {
            $monthSe[$i]['CHARGEMONTH'] = $chargeMonthSe->{'r_body'}[$i]->{'CHARGEMONTH'};
            if($itemCode == "A"){
                $monthSe[$i]['CHARGEMONTHSHOW'] = $monthSe[$i]['CHARGEMONTH'].'-'.($monthSe[$i]['CHARGEMONTH']+1);
            }else{
                $monthSe[$i]['CHARGEMONTHSHOW'] = $chargeMonthSe->{'r_body'}[$i]->{'CHARGEMONTH'}.'年';
            }
        }
        $age = $_POST['year'];
        if (empty($age)) {
            $age = $monthSe[0]['CHARGEMONTH'];
        }
		
		$this->itemCode = $itemCode;
//print_r($houseCode);
		for($j=1;$j<5;$j++){
			if(null!=$houseCode && ""!=$houseCode) {
				if($j==1){
					$itemCode = "B";
				}
				if($j==2){
					$itemCode = "D";
				}
				if($j==3){
					$itemCode = "S";
				}
				if($j==4){
					$itemCode = "A";
				}
				//$historylist = R('Api/getLastData', array($houseCode, $itemCode, $age));
				$historylist = R('Api/getHistoryData', array($houseCode, $itemCode, $age));
               
			
               

				for ($i = 0; $i < count($historylist->{'r_body'}); $i++) {
					if($j==1){
						$zlslist[$i]['ADDRESS'] = $historylist->{'r_body'}[$i]->{'ADDRESS'};
						$zlslist[$i]['ITEMCODE'] = $historylist->{'r_body'}[$i]->{'ITEMCODE'};
						$zlslist[$i]['CHARGEMONTH'] = $historylist->{'r_body'}[$i]->{'CHARGEMONTH'};
						$zlslist[$i]['QF'] = $historylist->{'r_body'}[$i]->{'QF'};
						$zlslist[$i]['YL'] = $historylist->{'r_body'}[$i]->{'YL'};
						$zlslist[$i]['NOWCASH'] = $historylist->{'r_body'}[$i]->{'NOWCASH'};
						$zlslist[$i]['REALACCOUNT'] = $historylist->{'r_body'}[$i]->{'REALACCOUNT'};
					}
					if($j==2){
						$trqlist[$i]['ADDRESS'] = $historylist->{'r_body'}[$i]->{'ADDRESS'};
						$trqlist[$i]['ITEMCODE'] = $historylist->{'r_body'}[$i]->{'ITEMCODE'};
						$trqlist[$i]['CHARGEMONTH'] = $historylist->{'r_body'}[$i]->{'CHARGEMONTH'};
						$trqlist[$i]['QF'] = $historylist->{'r_body'}[$i]->{'QF'};
						$trqlist[$i]['YL'] = $historylist->{'r_body'}[$i]->{'YL'};
						$trqlist[$i]['NOWCASH'] = $historylist->{'r_body'}[$i]->{'NOWCASH'};
						$trqlist[$i]['REALACCOUNT'] = $historylist->{'r_body'}[$i]->{'REALACCOUNT'};
					}

					if($j==3){
						$zsslist[$i]['ADDRESS'] = $historylist->{'r_body'}[$i]->{'ADDRESS'};
						$zsslist[$i]['ITEMCODE'] = $historylist->{'r_body'}[$i]->{'ITEMCODE'};
						$zsslist[$i]['CHARGEMONTH'] = $historylist->{'r_body'}[$i]->{'CHARGEMONTH'};
						$zsslist[$i]['QF'] = $historylist->{'r_body'}[$i]->{'QF'};
						$zsslist[$i]['YL'] = $historylist->{'r_body'}[$i]->{'YL'};
						$zsslist[$i]['NOWCASH'] = $historylist->{'r_body'}[$i]->{'NOWCASH'};
						$zsslist[$i]['REALACCOUNT'] = $historylist->{'r_body'}[$i]->{'REALACCOUNT'};
					}

					if($j==4){
						$relist[$i]['ADDRESS'] = $historylist->{'r_body'}[$i]->{'ADDRESS'};
						$relist[$i]['ITEMCODE'] = $historylist->{'r_body'}[$i]->{'ITEMCODE'};
						$relist[$i]['CHARGEMONTH'] = $historylist->{'r_body'}[$i]->{'CHARGEMONTH'};
						$relist[$i]['QF'] = $historylist->{'r_body'}[$i]->{'QF'};
						$relist[$i]['YL'] = $historylist->{'r_body'}[$i]->{'YL'};
						$relist[$i]['NOWCASH'] = $historylist->{'r_body'}[$i]->{'NOWCASH'};
						$relist[$i]['REALACCOUNT'] = $historylist->{'r_body'}[$i]->{'REALACCOUNT'};
					}

				}
				
              
			}
		}

		//$sj = array(0=>($age-1)."-11-15",1=>($age-1)."-12-15",2=>$age."-01-15",3=>$age."-02-15",4=>$age."-03-15");
		$sj = array(0=>$age."-11-15",1=>$age."-12-15",2=>($age+1)."-01-15",3=>($age+1)."-02-15",4=>($age+1)."-03-15");
		$map['metetTime'] = array('in', $sj);
		$map['housecode'] = $houseCode;
		$con['housecode'] = $houseCode;
    
$jljljllist=R('Api/GetHeatMeteringMonthlyDosageServlet',array($houseCode,$age,0));

			for ($i = 0; $i < count($jljljllist->{'r_body'}); $i++) {
				$jljllist[$i]['metetTime'] = $jljljllist->{'r_body'}[$i]->{'metetTime'};//日期
				$jljllist[$i]['nownum'] = $jljljllist->{'r_body'}[$i]->{'nownum'};//热表读数
				$jljllist[$i]['alltotal'] = $jljljllist->{'r_body'}[$i]->{'alltotal'};//当月用量
				$jljllist[$i]['meteraccount'] = $jljljllist->{'r_body'}[$i]->{'meteraccount'};//累计用量
				$jljllist[$i]['percent'] = $jljljllist->{'r_body'}[$i]->{'percent'};//百分比

			}
		
    $reslisthistory=R('Api/GetHistoryBill_AServlet',array($houseCode,$age,$age+1));
       $relistto[0]['MJRF_OLD'] = $reslisthistory->{'r_body'}[0]->{'MJRF_OLD'};
	          $relistto[0]['RJLFY'] = $reslisthistory->{'r_body'}[0]->{'RJLFY'};
			  //结余/补缴只能有一个>0
			  $pdjie=$reslisthistory->{'r_body'}[0]->{'JIEYU'};
			  $pdbu=$reslisthistory->{'r_body'}[0]->{'BUJIAO'};
			  if($pdjie>0)
		{ 
				
$relistto[0]['JIEYUhuo'] = $pdjie;
			  }
			  else if($pdbu>0)
		{
			 
$relistto[0]['JIEYUhuo'] = $pdbu;
			  }
			  else if($pdjie==0&&$pdbu==0)
		{
				
				  $relistto[0]['JIEYUhuo'] =0;
			  }
		
			      
					       
				

 


        $this->houselist = R('User/fjbd', array($_SESSION['uid']));
		$this->zlslist = $zlslist;
		$this->trqlist = $trqlist;
		$this->relist = $relist;
	    $this->relistto = $relistto;
		$this->zsslist = $zsslist;
		//$this->jllist = $jllist;
		$this->jllist = $jljllist;
		$this->code = $houseCode;
        $this->monthSe = $monthSe;
        
		$this->age = $age;
		
		$this->year = date('Y', time());
		/*end update by wangshipeng 2014.10.28*/

        /*-- 增加显示当期账号号*/
        //$this-> current = $accountTime;
        /* --*/
        $this->topid = $p[0]['id']; //顶部导航选中效果


        $this->display();
    }

   
    //在线查询——当期账单 这是测试页  
    public function ceshidqzd()
    {
	
	
        $this->check();

		$data = array();
		$data1 = array();
		$data2 = array();
		//$hcode=$_GET['hcode'];
	  
		$houselist = R('User/fjbd', array($_SESSION['uid']));//用户绑定房间
		if(empty($houselist)) {
			$this->error('您没有绑定房间，不能进行在线查询!', '/index.php?s=/user/bind.html');
		}

        $this->id = intval($this->_param('id'));
        $this->pid = D('Cate')->where('id=' . $this->id)->getField('pid'); //左侧导航
        $this->nav = $this->nav($this->cate, $this->id, ''); //当前位置
        $p = get_parents($this->cate, $this->id);
        /*if (empty($p)) {
            $this->redirect("Public/404");
        }*/
		
	
		$houseCode = $_REQUEST['houseCode'];
        if($_REQUEST['houseCode'] == '' || $_REQUEST['houseCode'] == NULL){
			$houseCode = $houselist[0]['houseCode'];
		}



		$shuiHis=$qiHis=$zaiHis=0;
		$yingyeDate=date('H');
		$result = R('Api/getNowData', array($houseCode));//print_r($result);
		for($k = 0; $k < count($result->{'r_body'}); $k++){
			if($yingyeDate >= 10 && $yingyeDate <= 16){
				$data[$k]['hh24h'] ='on';
			}else{
				$data[$k]['hh24h'] ='off';
			}
			$data[$k]['HOUSECODE'] = $result->{'r_body'}[$k]->{"HOUSECODE"};
			$data[$k]['CHARGEMONTH'] = $result->{'r_body'}[$k]->{"CHARGEMONTH"};
			$data[$k]['ITEMNAME'] = $result->{'r_body'}[$k]->{"ITEMNAME"};
			if($data[$k]['ITEMNAME'] == '自来水'){
				if($shuiHis == 0){
					//判断是否历史欠费
					$data[$k]['payOn'] ='on';
					$shuiHis = 1;
				}else{
					$data[$k]['payOn'] ='off';
				}
				//判断是否锁定
				$shui = R('Api/getShuiStatus', array($data[$k]['HOUSECODE']));
				$data[$k]['STATUS'] = $shui[0]['COU'];
			}
			if($data[$k]['ITEMNAME'] == '天然气'){
				if($qiHis == 0){
					//判断是否历史欠费
					$data[$k]['payOn'] ='on';
					$qiHis = 1;
				}else{
					$data[$k]['payOn'] ='off';
				}
				//判断是否锁定
				$qi = R('Api/getQiStatus', array($data[$k]['HOUSECODE']));
				$data[$k]['STATUS'] = $qi[0]['COU'];
			}
			if($data[$k]['ITEMNAME'] == '再生水'){
				if($zaiHis == 0){
					//判断是否历史欠费
					$data[$k]['payOn'] ='on';
					$zaiHis = 1;
				}else{
					$data[$k]['payOn'] ='off';
				}
				$data[$k]['STATUS'] = 0;
			}
			$data[$k]['POINTNUMBER'] = $result->{'r_body'}[$k]->{"POINTNUMBER"};
			$data[$k]['DQFY'] = $result->{'r_body'}[$k]->{"DQFY"};
						$data[$k]['WYJ'] = $result->{'r_body'}[$k]->{"WYJ"};
			$data[$k]['QFZT'] = $result->{'r_body'}[$k]->{"QFZT"};
			$data[$k]['PRICE'] = $result->{'r_body'}[$k]->{"PRICE"};
			$data[$k]['ITEMCODE'] = $result->{'r_body'}[$k]->{"ITEMCODE"};
			$data[$k]['CLOCKNUMBER'] = $result->{'r_body'}[$k]->{"CLOCKNUMBER"};
		}

		//查询供热
		$resultGr = R('Api/getGrData', array($houseCode));
        
		for($k = 0; $k < count($resultGr->{'r_body'}); $k++){
			if($k == 0){
				$dataGr[$k]['payOn'] ='on';
			}else{
				$dataGr[$k]['payOn'] ='off';
			}
			if($yingyeDate >= 10 && $yingyeDate <= 16){
				$dataGr[$k]['hh24h'] ='on';
			}else{
				$dataGr[$k]['hh24h'] ='off';
			}
			
			$re = R('Api/getReStatus', array($historylist[$i]['HOUSECODE']));
			$dataGr[$k]['STATUS'] = $re[0]['COU'];
			$dataGr[$k]['HOUSECODE'] = $resultGr->{'r_body'}[$k]->{"HOUSECODE"};
			$dataGr[$k]['CHARGEMONTH'] = $resultGr->{'r_body'}[$k]->{"CHARGEMONTH"};
			$dataGr[$k]['ITEMNAME'] = $resultGr->{'r_body'}[$k]->{"ITEMNAME"};
			$dataGr[$k]['POINTNUMBER'] = $resultGr->{'r_body'}[$k]->{"POINTNUMBER"}; //采暖面积
			$dataGr[$k]['DQFY'] = $resultGr->{'r_body'}[$k]->{"DQFY"};
            $dataGr[$k]['wyj'] = $resultGr->{'r_body'}[$k]->{"WYJ"};
			$dataGr[$k]['PRICE'] = $resultGr->{'r_body'}[$k]->{"PRICE"};//面积单价
			$dataGr[$k]['ITEMCODE'] = $resultGr->{'r_body'}[$k]->{"ITEMCODE"};
			$dataGr[$k]['CLOCKNUMBER'] = $resultGr->{'r_body'}[$k]->{"CLOCKNUMBER"};
			$dataGr[$k]['YHJM'] = $resultGr->{'r_body'}[$k]->{"YHJM"};  //优惠金额
			$dataGr[$k]['MINUSMONEY'] = $resultGr->{'r_body'}[$k]->{"MINUSMONEY"};//减免费用
			$dataGr[$k]['IMP'] = $resultGr->{'r_body'}[$k]->{"IMP"};//上年节余
			$dataGr[$k]['MON'] = $resultGr->{'r_body'}[$k]->{"MON"};
			
			$dataGr[$k]['PLS'] = $resultGr->{'r_body'}[$k]->{"PLS"};//面积热费
			$dataGr[$k]['XJ1'] = $dataGr[$k]['PLS']-$dataGr[$k]['YHJM'];  //费用小计
			$dataGr[$k]['XJ2'] = $dataGr[$k]['XJ1']-$dataGr[$k]['MINUSMONEY'];//费用小计
			$dataGr[$k]['HJ'] = $dataGr[$k]['XJ2']-$dataGr[$k]['IMP'];//合计
            $dataGr[$k]['STOP'] = $resultGr->{'r_body'}[$k]->{"STOPKEAPFLAG"};
		}

		/*for($k = 1; $k <= count($resultGr->{'r_body'}); $k++){
			$dataGr[$k]['HOUSECODE'] = $resultGr->{'r_body'}[0]->{"HOUSECODE"};
			$dataGr[$k]['CHARGEMONTH'] = $resultGr->{'r_body'}[0]->{"CHARGEMONTH"};
			$dataGr[$k]['ITEMNAME'] = $resultGr->{'r_body'}[0]->{"ITEMNAME"};
			$dataGr[$k]['POINTNUMBER'] = $resultGr->{'r_body'}[0]->{"POINTNUMBER"};
			$dataGr[$k]['DQFY'] = $resultGr->{'r_body'}[0]->{"DQFY"};
			$dataGr[$k]['PRICE'] = $resultGr->{'r_body'}[0]->{"PRICE"};
			$dataGr[$k]['ITEMCODE'] = $resultGr->{'r_body'}[0]->{"ITEMCODE"};
			$dataGr[$k]['CLOCKNUMBER'] = $resultGr->{'r_body'}[0]->{"CLOCKNUMBER"};
			$dataGr[$k]['YHJM'] = $resultGr->{'r_body'}[0]->{"YHJM"};
			$dataGr[$k]['MINUSMONEY'] = $resultGr->{'r_body'}[0]->{"MINUSMONEY"};
			$dataGr[$k]['IMP'] = $resultGr->{'r_body'}[0]->{"IMP"};
			$dataGr[$k]['MON'] = $resultGr->{'r_body'}[0]->{"MON"};
			$dataGr[$k]['PLS'] = $resultGr->{'r_body'}[0]->{"PLS"};
		}*/

		$result1 = R('Api/getPriceData', array($houseCode));

		$data1['ZLSYJ'] = number_format($result1->{'r_body'}[0]->{"ZLSYJ"},2);
		$data1['ZLSEJ'] = number_format($result1->{'r_body'}[0]->{"ZLSEJ"},2);
		$data1['ZLSSJ'] = number_format($result1->{'r_body'}[0]->{"ZLSSJ"},2);
		$data1['TRQYJ'] = number_format($result1->{'r_body'}[0]->{"TRQYJ"},2);
		$data1['TRQEJ'] = number_format($result1->{'r_body'}[0]->{"TRQEJ"},2);
		$data1['TRQSJ'] = number_format($result1->{'r_body'}[0]->{"TRQSJ"},2);
		$data1['ITEMCODE'] = $result1->{'r_body'}[0]->{"ITEMCODE"};
		$data1['POINTNUMBER_B'] = $result1->{'r_body'}[0]->{"POINTNUMBER_B"};
		$data1['POINTNUMBER_D'] = $result1->{'r_body'}[0]->{"POINTNUMBER_D"};

		$result2 = R('Api/getNum', array($houseCode));

		for($k = 0; $k < count($result2->{'r_body'}); $k++){
			$data2[$k]['ITEMCODE'] = $result2->{'r_body'}[$k]->{"ITEMCODE"};
			$data2[$k]['NOWPOINTNUMBER'] = $result2->{'r_body'}[$k]->{"NOWPOINTNUMBER"};
            //$newtu = R('Page/shuChuTu', array($data2[$k]['NOWPOINTNUMBER'],$data2[$k]['ITEMCODE']));
			$data2[$k]['CLOCKNUMBER'] = $result2->{'r_body'}[$k]->{"CLOCKNUMBER"};
		}

 


        
		 //取供热仪表盘数据 
		 $rybdata = R('Api/GetHeatMeteringDashboardServlet',array($houseCode,0));		 
     		for($n = 0; $n < count($rybdata->{'r_body'}); $n++)
		{
			 $grdata[$n]['cssyear']=$rybdata ->{'r_body'}[$n]->{"cssyear"};//年度，热表初始读数行的年度
			 $grdata[$n]['riqi']=$rybdata ->{'r_body'}[$n]->{"riqi"};//日期，根据当前日期判断取得显示的日期
			 $grdata[$n]['metettime']=$rybdata ->{'r_body'}[$n]->{"metettime"};//日期
			
			
             $this->percent=$rybdata ->{'r_body'}[$n]->{"percent"};//百分比
			
			 $this->meteraccount=$rybdata ->{'r_body'}[$n]->{"meteraccount"};//当年累计用量
			 $grdata[$n]['nownum']=$rybdata ->{'r_body'}[$n]->{"nownum"};//当月热表读数
			 $grdata[$n]['startnum']=$rybdata ->{'r_body'}[$n]->{"startnum"};//初始读数
		}
		
		 
		
		

/*

		//查询供热百分比图表
		$nowdate = date("Y-m-d");
		$jiudate = date('Y').'-09-30';
		$shiyidate = date('Y').'-11-15';
		$shierdate = date('Y').'-12-15';
		if($nowdate > strtotime($jiudate) && $nowdate <= strtotime($shiyidate)){
			
			$grData['allTotal'] = '--';
			$grData['startNum'] = '--';
			$grData['nianfen'] = date('Y');
			$this->bfb = 0;
		}else if($nowdate > strtotime($shiyidate) && $nowdate <= strtotime($shierdate)){
			
		 
			$grData['allTotal'] = '--';
			$grData['startNum'] = '--';
			$grData['nianfen'] = date('Y');
			$this->bfb = 30;
			echo 11;
		}else{
		
			$grMap['housecode']=$houseCode;
			//当前年度
			$nowyear=date('Y');
			$year['year']=$nowyear;
			$grData = D('EnergyMeter')->limit(2)->where(array_merge($grMap, $year))->order('id desc')->select();
			//echo array_merge($grMap, $year); //将两个数据合并成一个数组
			$dddd = D('EnergyMeter')->where($grMap)->order('id desc')->find();//取出单个的百分比和当年累计用量
			$resultb = R('Api/getBFB', array($houseCode,$grData['allTotal']));
			$this->bfb = $resultb->{'r_body'}[0]->{"PROPORTION"};
            //dump(D('EnergyMeter')->getLastSql());
			 
			//var_dump($grData);
			
			
		}
*/
		

		$this->numlist = $data2;
		$this->pricelist = $data1;
		$this->grlist = $dataGr;
		$this->nowlist = $data;
        $this->houselist = $houselist;
		$this->code = $houseCode;
		$this->dd=$dddd;
		$this->grData = $grdata;

        $this->topid = $p[0]['id']; //顶部导航选中效果
			

		
        $this->display();
    }









}

?>
