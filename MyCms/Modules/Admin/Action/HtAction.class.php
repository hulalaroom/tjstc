<?php

class HtAction extends AdminAction {

    public function index() {
       
        //栏目类别
        $cate = D('Cate')->order('sort Asc')->relation(true)->select();
        $this->list = get_cate($cate, '&nbsp;&nbsp;├&nbsp;&nbsp;');
        $m_guest = D('Ht');
         //栏目查询
        if (is_numeric($_GET['cat_id'])) {
           $c = get_childsid($cate, $_GET['cat_id']);
            $cids = $_GET['cat_id'];
            foreach ($c as $v) {
                $cids.="," . $v;
            }
            $map['cat_id'] = array('in', $cids);
            $index['cat_id'] = $_GET['cat_id'];
        }
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
		
        import("ORG.Util.Page");
		/*start update by wangshipeng 2014.06.06*/
		/*if (true === $_SESSION['is_supper_admin']) {
			$count = $m_guest->where('status = 1')->count();
		}
		else{
			$count = $m_guest->where($map)->count();
		}*/

		$count = $m_guest->where($map)->count();
		
        $page = new Page($count, $limit);
		
		//$contractlist = $m_guest->where($map)->limit($page->firstRow . ',' . $page->listRows)->relation(true)->order('id DESC')->select();
		$contractlist = $m_guest->limit($page->firstRow . ',' . $page->listRows)->relation(true)->order('ht_id DESC')->select();
		/*end update by wangshipeng 2014.06.06*/
		$this->assign('diff', $_SESSION['is_supper_admin']);
        $this->assign('contractlist', $contractlist);
        //分页显示及默认页数
         $show = $page->show();
        $this->assign('page', $show); // 赋值分页输出
        $this->assign('p', C('PAGE_SIZE'));
        //输出搜索的条件
        $this->assign($index);

        $this->display();
    }

    //合同预约处理
    public function edit() {
        $id = intval($_GET['id']);
        $m_guest = D('Ht');
		$m_jd = D('Ht_jd');
		$arr = array();
        $var = $m_guest->where("ht_id =$id")->relation(true)->find();
		//$list =  D('Ht_upload')->where("ht_id =$id")->select();
		/*$arr['pic1'] = $list[0]['file_url'];
		$arr['pic2'] = $list[1]['file_url'];
		$arr['pic3'] = $list[2]['file_url'];
		$arr['pic4'] = $list[3]['file_url'];
		$arr['pic5'] = $list[4]['file_url'];
		$arr['pic6'] = $list[5]['file_url'];
		$arr['pic7'] = $list[6]['file_url'];
		$arr['pic8'] = $list[7]['file_url'];*/
		//房间地址
		$villagename = D('Survey_heating_address')->where('COMMUNITYCODE=' . $var['communitycode'])->find();

		$address = $villagename['COMMUNITYNAME'].substr($var['buildingcode'],5)."号".$var['cellcode']."门".$var['doorplatecode']."号";
		

		$re = $m_jd->where("ht_id =$id")->order('operatedate desc')->find();
        $this->assign($var);
		//$this->assign($re);
		$this->assign('remark', $re['remark']);
		$this->assign($arr);
		$this->assign('address',$address);
		//$this->assign('piclist',$list);
		$this->assign('diff', $_SESSION['is_supper_admin']);
        $this->assign('type', 'edit');
        $this->display('add');
    }

	//合同证件照查看
    public function view() {

        $id = intval($_GET['id']);

		$type = intval($_GET['type']);

		$list =  D('Ht_upload')->where("ht_id = $id and type = $type")->select();
		
		$this->assign('piclist',$list);
		
        $this->display();
    }
	
	/*start update by wangshipeng 2016.05.26*/
    public function update() {
		$id =$_POST['id'];
		$status = $_POST['status'];
		$ifAgree = "不同意";
		
        if ($status == 1 && empty($_POST['content'])) {
            $this->error('请说明驳回原因!','');
			exit();
        }
		if($status == 3){
			$data['sh_status'] = '1';
			$map['sh_status'] = '1';
			$ifAgree = "同意";
		}
		
		$data['status'] = $ifAgree;
		$map['status'] = $status;
		
        $r = D('ht')->where("ht_id = ".$id)->setField($map);
		
		$admin = D('admin')->where('id=' . '"'.$_SESSION['is_supper_admin'].'"')->find();
		$data['ht_id'] = $id;
		$data['operator'] = $admin['adminname']; 
		$data['remark'] = $_POST['content'];
		$data['operatedate'] = date("Y-m-d H:i:s",time());
		//保存
		$re = D('ht_jd')->add($data);


		if ($r === FALSE || $re === FALSE) {
            $this->error('操作失败', '__URL__/index');
        } else {
            $this->success('操作成功', '__URL__/index');
        }
    }
	/*start update by wangshipeng 2016.05.26*/

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

        if (empty($ids)) {
            $this->error('没有选择记录！');
            exit();
        }
        $map['ht_id'] = array('in', $ids);
        switch ($command) {
            case 'delete':
                $re = D('Ht')->where($map)->delete();
                $type = "删除";
                break;
            case 'status':
                $re = D('Ht')->where($map)->setField('status', '1');
                $type = "用户签订";
                break;
            case 'unstatus':
                $re = D('Ht')->where($map)->setField('status', '3');
                $type = "同意";
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
