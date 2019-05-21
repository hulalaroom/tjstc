<?php

class BusinessAction extends AdminAction {

    //投票主页
    public function index() {
         $m_business = D('Business');
        //栏目类别
        $cate = D('Cate')->order('sort Asc')->relation(true)->select();
        $this->list = get_cate($cate, '&nbsp;&nbsp;├&nbsp;&nbsp;');

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
        //文章列表
        import("ORG.Util.Page");
        $count = $m_business->where($map)->count();
        $page = new Page($count, $limit);
        $blist = $m_business->where($map)->limit($page->firstRow . ',' . $page->listRows)->relation(true)->order('id  DESC')->select();

        $this->assign('blist', $blist);
          //分页显示及默认页数
        $show = $page->show();
        $this->assign('page', $show); // 赋值分页输出
        $this->assign('p', C('PAGE_SIZE'));
        //输出搜索的条件
        $this->assign($index);

        $this->display();
    }

    public function edit() {
        $m_business = D('business');
        $id = intval($_GET['id']);
        $data = $m_business->where("id = $id")->find();
        $this->assign($data);
        $this->display();
    }

    //更改
    public function update() {
    		
        $id = $_POST['id'];
        //$content = $_POST['content'];
      	$data['status'] = $_POST['status'];
      	/*if($_POST['status']==4){
					if (empty($content)){
						$this->error('操作失败，驳回原因不可为空');
					}
				}*/
      	$data['updatetime'] = time();
      	
      	$r = D('business')->where("id = ".$id)->setField($data);
      	
        if ($r == false) {
            $this->error("操作失败，请重试！");
        }
        $this->success('修改成功！', '__URL__/index');
    }

    //删除
    public function delete() {
        $m_business = D('business');
        $id = intval($_GET['id']);
        $r = $m_business->where(" id =$id")->delete();
        if ($r == false) {
            $this->error('删除失败');
        }
        $this->success('删除成功！', '__URL__/index');
    }


    //批量操作
    public function bat() {
        $command = $this->_param('command');
        $ids = $this->_param('id');
		if (empty($command)) {
            $this->error('没有选择操作！');
            exit();
        }
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
                $re = D('business')->where($map)->delete();
                $type = "删除";
                break;
            case 'status':
                $re = D('business')->where($map)->setField('status', '0');
                $type = "未受理";
                break;
            case 'alstatus':
                $re = D('business')->where($map)->setField('status', '2');
                $type = "已受理";
                break;
			 case 'dlstatus':
                $re = D('business')->where($map)->setField('status', '3');
                $type = "已办结";
                break;
        }
        if ($re !== false) {
            $this->success('成功' . $type);
        } else {
            $this->error('失败！');
        }
    }

}

?>