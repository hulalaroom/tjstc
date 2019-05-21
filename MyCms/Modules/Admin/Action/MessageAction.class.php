<?php

class MessageAction extends CommonAction {
    function _initialize(){
        $mapc['status']=0;
        $mapc['toid']=0;
       $this->unread = D('UserMessage')->where($mapc)->count();
    }

    public function index() {
        //用户列表
        $ulist = D('User')->field('id,username')->select();
        foreach ($ulist as $k => $v) {
            $user[$v['id']] = $v['username'];
        }
        $this->assign('ulist', $ulist);
        //每页条数
        if (!empty($_GET['limit'])) {
            $limit = $_GET['limit'];
            $index['limit'] = $_GET['limit'];
        } else {
            $limit = C('PAGE_SIZE');
            $index['limit'] = C('PAGE_SIZE');
        }
        //接收查询
        if (is_numeric($_GET['toid'])) {
            $toid = $_GET['toid'];
            $index['toid'] = $_GET['toid'];
        }
        $map['issys'] = 1; //是否后台系统发布
        import("ORG.Util.Page");
        $count = D('Message')->where($map)->count();
        $page = new Page($count, $limit);
        $messagelist = D('Message')->where($map)->limit($page->firstRow . ',' . $page->listRows)->order('id  DESC')->relation(true)->select();
        $data = array();
        foreach ($messagelist as $k => $m) {

            $username = '';
            $data[$k]['id'] = $m['id'];
            $data[$k]['title'] = $m['title'];
            $data[$k]['create_time'] = $m['create_time'];
            foreach ($m['UserMessage'] as $u) {
                if (!empty($user[$u['toid']])) {
                    $username.=$user[$u['toid']] . ',';
                    $data[$k]['toid'][] = $u['toid'];
                }
            }
            $data[$k]['username'] = substr($username, 0, -1);
            ;
        }
        if (!empty($toid)) {
            foreach ($data as $k => $d) {
                if (in_array($toid, $d['toid'])) {
                    $a[] = $d;
                }
            }
            $this->assign('messagelist', $a);
        } else {
            $this->assign('messagelist', $data);
        }



        //分页显示及默认页数
        $show = $page->show();
        $this->assign('page', $show); // 赋值分页输出
        $this->assign('p', C('PAGE_SIZE'));
        //输出搜索的条件
        $this->assign($index);
        $this->display();
    }

    public function add() {
		//用户组查询
        if (is_numeric($_GET['fromid'])) {
            $map['fromobj'] = array('eq',$_GET['fromid']);
            $index['fromid'] = $_GET['fromid'];
        }
		//活跃度
        if (is_numeric($_GET['order'])) {
            if($_GET['order']=='0'){
                 $order = 'login_count DESC';
            }  else {
                  $order = 'login_count ASC';
            }           
            $index['order'] = $_GET['order'];
        }else{
            $order ='id ASC';
        }
		//关键词
        if (!empty($_POST['keyword'])) {
            $map[$_POST['types']] = array('like', '%' . $_POST['keyword'] . '%');
            $index['types'] = $_POST['types'];
            $index['keyword'] = $_POST['keyword'];
        }
		$map['status'] = 1;
        $ulist = D('User')->where($map)->order($order)->select();
		$this->assign($index);
        $this->assign('ulist', $ulist);
        $this->assign('type', 'add');
        $this->display();
    }

    public function view() {
        //用户列表
        $ulist = D('User')->field('id,username')->select();
        foreach ($ulist as $k => $v) {
            $user[$v['id']] = $v['username'];
        }
        $mm = D('Message')->where('id=' . $_GET['id'])->relation(true)->find();
        $data = array();

        foreach ($mm['UserMessage'] as $u) {
            if (empty($_GET['type'])) {

                if (!empty($user[$u['toid']])) {
                    $username.=$user[$u['toid']] . ',';
                }
            } else {
                
                if (!empty($user[$u['fromid']])) {
                    $username.=$user[$u['fromid']] . ',';
                }
            }
        }
        $mm['username'] = substr($username, 0, -1);
        //标记已读
        $um['toid']=0;
                $um['mid']=$_GET['id'];
                $um['fromid']=$_GET['fromid'];
                $d['status']=1;
                 $status=D('UserMessage')->where($um)->getField('status');
                if($status==0){
                D('UserMessage')->where($um)->setField($d);
                }
//        dump($mm);
        $this->assign($mm);
        $this->assign('type', 'view');
        $this->display('add');
    }

    public function insert() {
        //标记已回复
        if(!empty($_POST['mid'])){
           $um['toid']=0;
                $um['mid']=$_POST['mid'];
                $um['fromid']=$_POST['toids'][0];
                $d['status']=2;
                D('UserMessage')->where($um)->setField($d);   
        }
        //插入
		        if (empty($_POST['toids'])) {
            $this->error('没有选择用户');
        }
        if (empty($_POST['title'])) {
            $this->error('标题不能为空');
        }
        if (empty($_POST['content'])) {
            $this->error('内容不能为空');
        }
        $data = D('Message')->create();
        $data['create_time'] = time();
        $data['issys'] = 1;
        $re = D('Message')->add($data);
        $row['mid'] = $re;
        $row['status'] = 0;
        $row['fromid'] = 0;

//        dump($_POST);
        $uids = $_POST['toids'];
//        $uids = D('User')->where('status=1')->select();
        foreach ($uids as $u) {
            $row['toid'] = $u;

            D('UserMessage')->add($row);
        }
        if (!$re == FALSE) {
            $this->success('发布成功', '__URL__/index');
        } else {
            $this->error('发布失败');
        }
////        
    }

//    public function edit() {
//        $id = intval($this->_param('id'));
//        if ($id <= 0) {
//            $this->error('参数错误！');
//        } else {
//
//            $list = D('Message')->where('id=' . $id)->find();
//            $this->assign($list);
//            $this->assign('type', 'edit');
//            $this->display('add');
//        }
//    }
//
//    public function update() {
//        D('Message')->create();
//        $re = D('Message')->save();
//        if ($re !== false) {
//            $this->success('修改成功！', '__URL__/index');
//        } else {
//            $this->success('修改失败！', '__URL__/index');
//        }
//    }
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
                $re = D('Message')->where($map)->relation(true)->delete();
                $type = "删除";
                break;
            case 'status':
                $re = D('Message')->where($map)->setField('status', '1');
                $type = "启用";
                break;
            case 'unstatus':
                $re = D('Message')->where($map)->setField('status', '0');
                $type = "停用";
                break;
        }
        if ($re !== false) {
            $this->success('成功' . $type);
        } else {
            $this->error('失败！');
        }
    }

//
    public function re() {
        //用户列表
        $ulist = D('User')->select();
        $this->assign('ulist', $ulist);
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
        //发送者查询
        if (is_numeric($_GET['fromid'])) {
            $map['fromid'] = $_GET['fromid'];
            $index['fromid'] = $_GET['fromid'];
        }
        /*    //关键词
          if (!empty($_GET['keyword'])) {
          $map[$_GET['types']] = array('like', '%' . $_GET['keyword'] . '%');
          $index['types'] = $_GET['types'];
          $index['keyword'] = $_GET['keyword'];
          } */

        $map['toid'] = 0;
        import("ORG.Util.Page");
        $count = D('UserMessage')->where($map)->relation(true)->count();
        $page = new Page($count, $limit);
        $messagelist = D('UserMessage')->where($map)->limit($page->firstRow . ',' . $page->listRows)->order('id  DESC')->relation(true)->select();




        $data = array();
        foreach ($messagelist as $k => $m) {
            $data[] = $m['Message'];
            $data[$k]['username'] = $m['User']['username'];
            $data[$k]['fromid']=$m['fromid'];
            $data[$k]['status']=$m['status'];
        }

//        dump($data);
        $this->assign('messagelist', $data);
        //分页显示及默认页数
        $show = $page->show();
        $this->assign('page', $show); // 赋值分页输出
        $this->assign('p', C('PAGE_SIZE'));
        //输出搜索的条件
        $this->assign($index);
        $this->display();
    }

}

?>
