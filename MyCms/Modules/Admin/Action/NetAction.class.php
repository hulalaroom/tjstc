<?php

class NetAction extends AdminAction {

    public function index() {
        //用户组查询
        if (is_numeric($_GET['uid'])) {
            $map['uid'] = array('eq', $_GET['uid']);
            $index['uid'] = $_GET['uid'];
        }
        //关键词
        if (!empty($_GET['keyword'])) {
            $map[$_GET['types']] = array('like', '%' . $_GET['keyword'] . '%');
            $index['types'] = $_GET['types'];
            $index['keyword'] = $_GET['keyword'];
        }
        //每页条数
        if (!empty($_GET['limit'])) {
            $limit = $_GET['limit'];
            $index['limit'] = $_GET['limit'];
        } else {
            $limit = C('PAGE_SIZE');
            $index['limit'] = C('PAGE_SIZE');
        }
        //列表
        import("ORG.Util.Page");
        $count = D('Net')->where($map)->count();
        $page = new Page($count, $limit);
        $net_list = D('Net')->where($map)->limit($page->firstRow . ',' . $page->listRows)->order('id  DESC')->select();
//        dump($room_list);
        $this->assign('net_list', $net_list);
        //分页显示及默认页数
        $show = $page->show();
        $this->assign('page', $show); // 赋值分页输出
        $this->assign('p', C('PAGE_SIZE'));
        //输出搜索的条件
        $this->assign($index);
        $this->display();
    }

	public function edit() {
        $id = intval($this->_param('id'));
        if ($id <= 0) {
            $this->error('参数错误！');
        } else {
            $list = D('Net')->where('id=' . $id)->find();
            $this->assign($list);
            $this->assign('type', 'edit');
            $this->display();
        }
    }

	//网点录入
	public function add() {
        $this->display();
    }
	
	//网点insert
	public function insert() {
		//插入房间
		$net = D('Net');
		//获取网点基本信息
		$data['netName'] = $_POST['netName'];
		$data['workTime'] = $_POST['workTime'];
		$data['serviceContent'] = $_POST['serviceContent'];
		$data['netPhone'] = $_POST['netPhone'];
		$data['netAddress'] = $_POST['netAddress'];
		$data['netCoor'] = $_POST['netCoor'];
		$arr = explode(",",$_POST['netCoor']);
		$data['jd'] = $arr[0];
		$data['wd'] = $arr[1];
		$data['createTime'] = strtotime(date('Y-m-d H:i:s',time()));
		
        try {
			if(!empty($_POST['net_id'])){
				$data['id'] = $_POST['net_id'];
				$ret = $net->save($data);
			}
			else{
				$ret = $net->add($data);
			}
			
		} catch (Exception $ex) {
			$ret = false;
		}
		if ($ret === false) {
			$this->error('操作失败', U('Net/add'));
		} else {
			$this->success("操作成功", U('Net/index'));
		}
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
        $where['mid'] = array('in', $ids);
        switch ($command) {
            case 'delete':
                $re = D('Net')->where($map)->delete();
                $type = "删除";
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
