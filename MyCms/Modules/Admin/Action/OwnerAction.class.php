<?php

class OwnerAction extends AdminAction {

    public function index() {
       
        //栏目类别
        $cate = D('Cate')->order('sort Asc')->relation(true)->select();
        $this->list = get_cate($cate, '&nbsp;&nbsp;├&nbsp;&nbsp;');
        $m_guest = D('Owner');
        
        //每页条数
        if (!empty($_GET['limit'])) {
            $limit = $_GET['limit'];
            $index['limit'] = $_GET['limit'];
        } else {
            $limit = C('PAGE_SIZE');
            $index['limit'] = C('PAGE_SIZE');
        }
       
        //关键词
        if (!empty($_GET['keyword'])) {
            $map[$_GET['types']] = array('like', '%' . $_GET['keyword'] . '%');
            $index['types'] = $_GET['types'];
            $index['keyword'] = $_GET['keyword'];
        }
		
        import("ORG.Util.Page");
		/*start update by wangshipeng 2014.06.06*/
		$count = $m_guest->where($map)->count();
        $page = new Page($count, $limit);
		
		//$contractlist = $m_guest->where($map)->limit($page->firstRow . ',' . $page->listRows)->relation(true)->order('id DESC')->select();
		$ownerlist = $m_guest->limit($page->firstRow . ',' . $page->listRows)->relation(true)->order('id DESC')->select();
		/*end update by wangshipeng 2014.06.06*/
		$this->assign('diff', $_SESSION['is_supper_admin']);
        $this->assign('ownerlist', $ownerlist);
        //分页显示及默认页数
         $show = $page->show();
        $this->assign('page', $show); // 赋值分页输出
        $this->assign('p', C('PAGE_SIZE'));
        //输出搜索的条件
        $this->assign($index);

        $this->display();
    }

    //个人信息编辑
    public function edit() {
        $id = intval($_GET['id']);
        $m_guest = D('Owner');

        $var = $m_guest->where("id =$id")->relation(true)->find();
				$housecode = $var['housecode'];
				$housename = D('user_room')->where('houseCode='."'$housecode'")->field('address')->limit(1)->select();
				$var['Address'] = $housename[0]['address'];
        $this->assign($var);
				$this->assign('diff', $_SESSION['is_supper_admin']);
        $this->assign('type', 'edit');
        $this->display('add');
    }
	
	//个人信息更新
    public function update() {
	  $status = $_POST['status'];
      $data['status'] = $status;
      //$content = $_POST['content'];
      //$data['content'] = $content;
			$id = $_POST['id'];
			/*if($status==2){
				if (empty($content)){
					$this->error('操作失败，驳回原因不可为空');
				}
			}*/
      $r = D('Owner')->where("ID = ".$id)->setField($data);
      	
			if ($r === FALSE) {
            $this->error('操作失败', '__URL__/index');
        } else {
            $this->success('操作成功', '__URL__/index');
        }
    }
	/*start update by wangshipeng 2014.06.08*/

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
        $map['ID'] = array('in', $ids);
        switch ($command) {
            case 'delete':
                $re = D('Owner')->where($map)->delete();
                $type = "删除";
                break;
            case 'status':
                $re = D('Owner')->where($map)->setField('status', '0');
                $type = "审核中";
                break;
            case 'alstatus':
                $re = D('Owner')->where($map)->setField('status', '1');
                $type = "通过审核";
                break;
			case 'dlstatus':
                $re = D('Owner')->where($map)->setField('status', '2');
                $type = "未通过审核";
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
