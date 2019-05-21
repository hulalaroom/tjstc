<?php
header('content-type:text/html;charset=utf-8');
class BusinessEnquiryAction extends AdminAction {

    public function index() {
		//8888-001-01-02-03
		
		$url = 'http://10.105.15.2/TjstcWebImpl/GetAllBusinessListServlet';
		
		$post_data="";
		//var_dump($post_data);
		$housecode=$_REQUEST['housecode'];
		$aa='';
		$BUSINESSCOMPANY=$_REQUEST['BUSINESSCOMPANY'];
		$bb='';
		$OWNERNAME=$_REQUEST['OWNERNAME'];
		$cc='';
		$BUSINESSTYPE=$_REQUEST['BUSINESSTYPE'];
		$dd='';
		$EXAMINESTATUS=$_REQUEST['EXAMINESTATUS'];
		$ee='';
		$SFCS=$_REQUEST['SFCS'];
		$ff='';
		$index['housecode'] = $_REQUEST['housecode'];
		$index['OWNERNAME'] = $_REQUEST['OWNERNAME'];
		$index['EXAMINESTATUS'] = $_REQUEST['EXAMINESTATUS'];
		$index['SFCS'] = $_REQUEST['SFCS'];
		if($housecode!=''){
			$aa="and housecode='$housecode'";
		}
		if($BUSINESSCOMPANY!=''){
			$bb="and BUSINESSCOMPANY='$BUSINESSCOMPANY'";
		}
		if($OWNERNAME!=''){
			$cc="and OWNERNAME='$OWNERNAME'";
		}
		if($BUSINESSTYPE!=''){
			$dd="and BUSINESSTYPE='$BUSINESSTYPE'";
		}
		if($EXAMINESTATUS!=''){
			$ee="and EXAMINESTATUS='$EXAMINESTATUS'";
		}
		if($SFCS!=''){
			$ff="and SFCS='$SFCS'";
		}
		$post_data="vCONDITION=".$aa.$bb.$cc.$dd.$ee.$ff;
		/*$demo1=mb_substr($demo,0,3);
		if($demo1=="and"){
			$post_data=substr_replace($demo,'',0,4);
		}else{
			$post_data=$demo;
		}*/
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		$output = curl_exec($ch);
		$guestlist1=json_decode($output,true);
		$count = count($guestlist1['r_body']);
		$guestlist=$guestlist1['r_body'];
		//var_dump($guestlist);
		curl_close($ch);


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
		//echo $limit;
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
	
	



}

?>
