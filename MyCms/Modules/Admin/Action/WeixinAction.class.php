<?php

class WeixinAction extends Action {

    function _initialize() {
        import('@.ORG.Wechat');
        $options = array(
            'token' => C('token'), //填写你设定的key
            'appid' => C('appid'), //填写高级调用功能的app id
            'appsecret' => C('appsecret'), //填写高级调用功能的密钥
        );
        $this->weObj = new Wechat($options);
        // $this->weObj->valid();验证完就注释掉
        $this->weObj->checkAuth();
        //未读条数
        $this->unread = D('Wiki')->where('status=0')->count();
    }

    public function api() {
        $type = $this->weObj->getRev()->getRevType();
        $data = array();
        $data = $this->weObj->getRev()->getRevData();
        $user_data = $this->weObj->getUserInfo($data['FromUserName']);
        $isreply = C('isreply');
        $su_msg = C('su_msg');
        $reply_msg = C('reply_msg');
        switch ($type) {
            case Wechat::MSGTYPE_TEXT:
                $data['status'] = 0;
                D('Wiki')->add($data);
                if ($isreply == 1) {
                    $this->weObj->text($reply_msg)->reply();
                }
                exit;
                break;
            case Wechat::MSGTYPE_EVENT:
                $revEvent = array();
                $revEvent = $this->weObj->getRev()->getRevEvent();
                switch ($revEvent['event']) {
                    //关注订阅事件
                    case "subscribe":
                        D('WikiUser')->add($user_data);

                        $this->weObj->text($su_msg)->reply();
                        break;
                    //取消关注订阅事件
                    case "unsubscribe":
                        //做一些删除用户记录之类的事情
                        $where['openid'] = $data['FromUserName'];
                        D('WikiUser')->where($where)->delete();
                        $where['FromUserName'] = $data['FromUserName'];
                        D('Wiki')->where($where)->delete();
                        break;
                }


                break;
            case Wechat::MSGTYPE_IMAGE:
                break;
            default:
                $this->weObj->text("help info")->reply();
        }
    }

    public function index() {
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

        //文章列表
        import("ORG.Util.Page");
        $count = D('Wiki')->where($map)->count();
        $page = new Page($count, $limit);
        $wiki_list = D('Wiki')->where($map)->limit($page->firstRow . ',' . $page->listRows)->order('CreateTime  DESC')->relation(true)->select();
        //echo D('Wiki')->getLastSql();
        //dump($wiki_list);
        $this->assign('wiki_list', $wiki_list);
        //分页显示及默认页数
        $show = $page->show();
        $this->assign('page', $show); // 赋值分页输出
        $this->assign('p', C('PAGE_SIZE'));
        //输出搜索的条件
        $this->assign($index);
        //echo 'index';
        $this->display();
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
        $map['id'] = array('in', $ids);
        switch ($command) {
            case 'delete':
                $re = D('Wiki')->where($map)->delete();
                $type = "删除";
                break;
        }
        if ($re !== false) {
            $this->success('成功' . $type);
        } else {
            $this->error('失败！');
        }
    }

    function aa() {
        $list = D('Wiki')->relation(true)->select();
        dump($list);
    }

    function re() {
        $map['id'] = $this->_get('id');
        if ($map['id'] <= 0) {
            $this->error('参数错误');
        } else {
           
           
            $list = D('Wiki')->where($map)->relation(true)->find();
            if($list['status']==0){               
             D('Wiki')->where($map)->setField('status','1'); 
            }
            $this->assign($list);
            $this->display();
        }
    }

    function reok() {
        $map['id'] = $this->_post('id');
        if ($map['id'] <= 0) {
            $this->error('参数错误');
        } else {
            $CreateTime = D('Wiki')->where($map)->getField('CreateTime');
            $ct = (int) $CreateTime;
            $now = time();
            if ($now - $ct >= 86400) {
                $this->error('已过期！微信接口有效期24小时');
            } else {
						        if (empty($_POST['touser'])) {
            $this->error('没有选择用户');
        }
     
        if (empty($_POST['recontent'])) {
            $this->error('内容不能为空');
        }
				
                $arr['touser'] = $_POST['touser'];
                $arr['msgtype'] = 'text';
                $arr['text']['content'] = $_POST['recontent'];
                $ins = $this->weObj->sendCustomMessage($arr);
                if ($ins !== false) {
                    $data['status'] = 2;
                    $data['recontent'] = $_POST['recontent'];
                    $re = D('Wiki')->where($map)->setField($data);
                    $this->success('回复成功', '__URL__/index');
                } else {
                    $this->error('回复失败');
                }
            }
        }
    }

    function add() {
        $ulist = D('WikiUser')->where('subscribe=1')->select();
        $this->assign('ulist', $ulist);
        $this->display();
    }

    function insert() {
        $uids = $_POST['touser'];
        foreach ($uids as $u) {

            //发送微信
            $arr['touser'] = $u;
            $arr['msgtype'] = 'text';
            $arr['text']['content'] = $_POST['content'];
            //F('arr', $arr, CONF_PATH);
            $ins = $this->weObj->sendCustomMessage($arr);
        }
        $row['content'] = $_POST['content'];
        $row['msgtype'] = 'text';
        $row['createtime'] = time();
        D('UserWiki')->add($row);
        $this->success('发布成功', '__URL__/fa');
    }

    public function fa() {
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

        //文章列表
        import("ORG.Util.Page");
        $count = D('UserWiki')->where($map)->count();
        $page = new Page($count, $limit);
        $wiki_list = D('UserWiki')->where($map)->limit($page->firstRow . ',' . $page->listRows)->order('createtime  DESC')->select();
        //echo D('Wiki')->getLastSql();
        //dump($wiki_list);
        $this->assign('wiki_list', $wiki_list);
        //分页显示及默认页数
        $show = $page->show();
        $this->assign('page', $show); // 赋值分页输出
        $this->assign('p', C('PAGE_SIZE'));
        //输出搜索的条件
        $this->assign($index);
        //echo 'index';
        $this->display();
    }

    //批量操作
    public function bat_fa() {
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
        $map['id'] = array('in', $ids);
        switch ($command) {
            case 'delete':
                $re = D('UserWiki')->where($map)->delete();
                $type = "删除";
                break;
        }
        if ($re !== false) {
            $this->success('成功' . $type);
        } else {
            $this->error('失败！');
        }
    }

    public function view() {
        if (is_numeric($_GET['id'])) {
            $data = D('Wiki')->where('id=' . $_GET['id'])->find();
        $this->assign($data);
            $d['status'] = 1;
            $status = D('Wiki')->where('id=' . $_GET['id'])->getField('status');
            if ($status == 0) {
                D('Wiki')->where('id=' . $_GET['id'])->setField($d);
            }
             $this->assign('type','index');
        }
         if (is_numeric($_GET['fid'])) {
              $data = D('UserWiki')->where('id=' . $_GET['fid'])->find();
            $this->assign($data);
            $this->assign('type','fa');
         }
        $this->display();
    }

}

?>