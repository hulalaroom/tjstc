<?php

class GuestbookAction extends AdminAction {

    public function index() {
       
        
        //每页条数
        if (!empty($_POST['limit'])) {
            $limit = $_POST['limit'];
            $index['limit'] = $_POST['limit'];
        } else {
            $limit = C('PAGE_SIZE');
            $index['limit'] = C('PAGE_SIZE');
        }
		//来源
        if (!empty($_POST['source'])) {
            $map['source'] = $_POST['source'];
			$index['source'] = $_POST['source'];
        } 
		// 类型
        if (!empty($_POST['type'])) {
            $map['type'] = $_POST['type'];
			$index['type'] = $_POST['type'];
        }else{
			$map['_string'] ="type='投诉' or type='建议'";
		}
        //联系电话
        if (!empty($_POST['contactnumber'])) {
            $map['contactnumber'] = $_POST['contactnumber'];
			$index['contactnumber'] = $_POST['contactnumber'];

        } 
		//编号
        if (!empty($_POST['id'])) {
            $map['id'] = $_POST['id'];
			$index['id'] = $_POST['id'];
        } 

        import("ORG.Util.Page");
        $count =  D('acceptancerecord')->where($map)->count();

        $page = new Page($count, $limit);
        $guestlist = D('acceptancerecord')->where($map)->limit($page->firstRow . ',' . $page->listRows)->order('id  DESC')->select();
        $this->assign('guestlist', $guestlist);
        //分页显示及默认页数
         $show = $page->show();
        $this->assign('page', $show); // 赋值分页输出
        $this->assign('p', C('PAGE_SIZE'));
        //输出搜索的条件
        $this->assign($index);

        $this->display();
    }
	 public function zxindex() {
		 $timeaa=strtotime("-2 day");
		 //echo $timeaa;
		 $time['type']='报修';
		 $timepd=D('acceptancerecord')->where($time)->select();
		 for($k = 0; $k < count($timepd); $k++){
			 $timeid=$timepd[$k]['id'];
			 $timest=$timepd[$k]['operatedate'];
			 if($timest<$timeaa){
				 $time['id']=$timeid;
				 $timedata['sfcs']='是';
				 $sql="UPDATE ad_acceptancerecord SET sfcs='是' WHERE type = '报修' AND id = $timeid";
				 $res = M()->execute($sql);
			 //$timeinfo = M('acceptancerecord')->where($time)->save($timedata);
			 //echo M("acceptancerecord")->getlastsql();exit;
			 }
		 }
		 //var_dump($timepd);exit;
       //查询涉及行业
		$url = 'http://10.105.15.2/TjstcWebImpl/GetInvolveIndustryHTServlet';
		$post_data ="";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		$output = curl_exec($ch);
		$involvearr=json_decode($output,true);

		$involvearr1=$involvearr['r_body'][0]['involveindustry'];
		for($k = 0; $k < count($involvearr['r_body']); $k++){
			$involveindustry[$k]['involveindustry']=$involvearr['r_body'][$k]['involveindustry'];
		}
		$this->involveindustry = $involveindustry;
        
		//$pp=$_POST;var_dump($pp);
        //每页条数
        if (!empty($_GET['limit'])) {
            $limit = $_GET['limit'];
            $index['limit'] = $_GET['limit'];
        } else {
            $limit = C('PAGE_SIZE');
            $index['limit'] = C('PAGE_SIZE');
        }
		$map['type']='报修';
        //联系电话
        if (!empty($_POST['contactnumber'])) {
            $map['contactnumber'] = $_POST['contactnumber'];
			$index['contactnumber'] = $_POST['contactnumber'];
        } 
		//编号
        if (!empty($_POST['id'])) {
            $map['a.id'] = $_POST['id'];
			$index['id'] = $_POST['id'];
        } 
		//办理状态
        if (!empty($_POST['state'])) {
            $map['state'] = $_POST['state'];
			$index['state'] = $_POST['state'];
        } 
		if (!empty($_POST['sjhy'])) {
            $map['involveindustry'] = $_POST['sjhy'];
			$index['sjhy'] = $_POST['sjhy'];
        } 
		if (!empty($_POST['sjzl'])) {
            $map['a.oid'] = $_POST['sjzl'];
			$index['sjzl'] = $_POST['sjzl'];
        } 
		if (!empty($_POST['bllb'])) {
            $map['a.tid'] = $_POST['bllb'];
			$index['bllb'] = $_POST['bllb'];
        } 
		if (!empty($_POST['sfcs'])) {
            $map['a.sfcs'] = $_POST['sfcs'];
			$index['sfcs'] = $_POST['sfcs'];
        } 


        import("ORG.Util.Page");
        $count =  D('acceptancerecord')->where($map)->count();

        $page = new Page($count, $limit);
        $guestlist = D('onlinerepairtype as c')->join('ad_onlinerepair as b on b.id = c.oid')->join('ad_acceptancerecord as a on a.tid = c.id ')->where($map)->limit($page->firstRow . ',' . $page->listRows)->order('a.id DESC')->select();
		//var_dump($guestlist);exit;
		//echo M("a")->getlastsql();exit;
        $this->assign('guestlist', $guestlist);
        //分页显示及默认页数
         $show = $page->show();
        $this->assign('page', $show); // 赋值分页输出
        $this->assign('p', C('PAGE_SIZE'));
        //输出搜索的条件
        $this->assign($index);

        $this->display();
    }

    //留言回复
    public function edit() {
        $aid = $_GET['id'];
        $url2 = 'http://10.105.15.2/TjstcWebImpl/GetAcceptanceRecordDetailServlet';
		$post_data2 ="vAID=$aid";
		$ch2 = curl_init();
		curl_setopt($ch2, CURLOPT_URL, $url2);
		curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch2, CURLOPT_POST, 1);
		curl_setopt($ch2, CURLOPT_POSTFIELDS, $post_data2);
		$output2 = curl_exec($ch2);
		$shouliarr=json_decode($output2,true);
		for($k = 0; $k < count($shouliarr['r_body']); $k++){
			$shouli[$k]['id']=$shouliarr['r_body'][$k]['id'];
			$shouli[$k]['proposer']=$shouliarr['r_body'][$k]['proposer'];
			$shouli[$k]['operatedate']=$shouliarr['r_body'][$k]['operatedate'];
			$shouli[$k]['Content']=$shouliarr['r_body'][$k]['Content'];
			$shouli[$k]['state']=$shouliarr['r_body'][$k]['state'];
			$shouli[$k]['contactnumber']=$shouliarr['r_body'][$k]['contactnumber'];
			$shouli[$k]['involveindustry']=$shouliarr['r_body'][$k]['involveindustry'];
			$shouli[$k]['involvetype']=$shouliarr['r_body'][$k]['involvetype'];
			$shouli[$k]['handletype']=$shouliarr['r_body'][$k]['handletype'];
			$pic=$shouliarr['r_body'][$k]['filename'];
			$picvar=explode(",",$pic);
			$shouli[$k]['pic1']=$picvar[0];
			$shouli[$k]['pic2']=$picvar[1];
			$shouli[$k]['pic3']=$picvar[2];
		}
		
		$this->shouli = $shouli;


        $this->display('add');
    }

    public function update() {
        $content = $_POST['content'];
        $cat_id = $_POST['cat_id'];
		
        /*if ($cat_id == 19) {
					if (!empty($content)){
						$data['status'] = 1;
						$data['content'] = $content;
						$data['update_time'] = time();
					}else{
						$this->error('回复失败，回复内容不可为空', '__URL__/index');
					}
			}	
		
			if ($cat_id == 18) {
				$data['status'] = $_POST['status'];
				$data['content'] = $content;
				$data['update_time'] = time();
			}*/
			$data['status'] = $_POST['status'];
		  $data['content'] = $content;
		  $data['update_time'] = time();
			$id =$_POST['id'];
      $r = D('Guestbook')->where("id = ".$id)->save($data);

      if ($r === FALSE) {
          $this->error('操作失败', '__URL__/index');
      } else {
          $this->success('操作成功', '__URL__/index');
      }
    }

    //批量操作
    public function bat() {
        $command = $this->_param('command');
			 if (empty($command)) {
            $this->error('没有选择操作！');
            exit();
        }
        $ids = $this->_param('id');

        if (is_array($ids)) {
            $ids = implode(',', $ids);
        }
//         dump($ids);
        if (empty($ids)) {
            $this->error('没有选择记录！');
            exit();
        }
        $map['id'] = array('in', $ids);
        switch ($command) {
            case 'delete':
                $re = D('Guestbook')->where($map)->delete();
                $type = "删除";
                break;
            case 'status':
                $re = D('Guestbook')->where($map)->setField('status', '0');
                $type = "未受理";
                break;
            case 'alstatus':
                $re = D('Guestbook')->where($map)->setField('status', '2');
                $type = "已受理";
                break;
			case 'dlstatus':
                $re = D('Guestbook')->where($map)->setField('status', '3');
                $type = "已办结";
                break;
        }
        if ($re) {
            $this->success('成功' . $type);
        } else {
            $this->error('失败！');
        }
    }


}

?>
