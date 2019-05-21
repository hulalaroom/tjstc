<?php

class RepairAction extends AdminAction {

    public function index() {
       
        //栏目类别
        $cate = D('Cate')->order('sort Asc')->relation(true)->select();
        $this->list = get_cate($cate, '&nbsp;&nbsp;├&nbsp;&nbsp;');
        $m_guest = D('Repair');
         //栏目查询
        /*if (is_numeric($_GET['cat_id'])) {
           $c = get_childsid($cate, $_GET['cat_id']);
            $cids = $_GET['cat_id'];
            foreach ($c as $v) {
                $cids.="," . $v;
            }
            $map['cat_id'] = array('in', $cids);
            $index['cat_id'] = $_GET['cat_id'];
        }*/
        //每页条数
        if (!empty($_GET['limit'])) {
            $limit = $_GET['limit'];
            $index['limit'] = $_GET['limit'];
        } else {
            $limit = C('PAGE_SIZE');
            $index['limit'] = C('PAGE_SIZE');
        }
        //状态查询
        /*if (is_numeric($_GET['status'])) {
            $map['status'] = $_GET['status'];
            $index['status'] = $_GET['status'];
        }*/
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
		
		$repairlist = $m_guest->limit($page->firstRow . ',' . $page->listRows)->relation(true)->order('id DESC')->select();
		/*end update by wangshipeng 2014.06.06*/
		$this->assign('diff', $_SESSION['is_supper_admin']);
        $this->assign('repairlist', $repairlist);
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
        $id = intval($_GET['id']);
        $m_guest = D('Repair');

        $var = $m_guest->where("id =$id")->relation(true)->find();
		
        $this->assign($var);
		$this->assign('diff', $_SESSION['is_supper_admin']);
        $this->assign('type', 'edit');
        $this->display('add');
    }
	
    public function update() {
				$status = $_POST['status'];
        $data['status'] = $status;
        /*$content = $_POST['content'];
				$data['content'] = $content;*/
				$id =$_POST['id'];
				/*if($status==2){
					if (empty($content)){
						$this->error('操作失败，驳回原因不可为空');
					}
				}*/
        $r = D('Repair')->where("ID = ".$id)->setField($data);
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

        if (empty($ids)) {
            $this->error('没有选择记录！');
            exit();
        }
        $map['ID'] = array('in', $ids);
        switch ($command) {
             case 'delete':
                $re = D('Repair')->where($map)->delete();
                $type = "删除";
                break;
            case 'status':
                $re = D('Repair')->where($map)->setField('status', '0');
                $type = "未受理";
                break;
            case 'alstatus':
                $re = D('Repair')->where($map)->setField('status', '1');
                $type = "已受理";
                break;
			case 'dlstatus':
                $re = D('Repair')->where($map)->setField('status', '2');
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
