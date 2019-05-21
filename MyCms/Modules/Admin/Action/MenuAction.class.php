<?php

class MenuAction extends AdminAction {

    public function index() {
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
		//dump($_GET['cat_id']);
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

		//查询时间
		if ((!empty($_GET['start_time'])) && (!empty($_GET['end_time']))) {
			 $map['create_time'] = array(between,array(strtotime($_GET['start_time']),strtotime($_GET['end_time'])));
        }
		if((!empty($_GET['start_time'])) && (empty($_GET['end_time']))){
			$map['create_time'] = array(between,array(strtotime($_GET['start_time']),time()));
		}
		if((empty($_GET['start_time'])) && (!empty($_GET['end_time']))){
			$map['create_time'] = array(between,array(strtotime("01 January 2012"),strtotime($_GET['end_time'])));
		}
		
		//区分管理员发布的文章
		if (false === $_SESSION['is_supper_admin']) {
			if($_SESSION['common_admin_id'] ==11){
				$map['cat_id'] = 161;
			}
			else{
				if(($_SESSION['common_admin_id'] == 5) or ($_SESSION['common_admin_id'] == 12)){
					$map['diff'] = 5;
				}
				else if(($_SESSION['common_admin_id'] == 6) or ($_SESSION['common_admin_id'] == 13) or ($_SESSION['common_admin_id'] == 14) or ($_SESSION['common_admin_id'] == 15)){
					$map['diff'] = 6;
				}
				else{
					$map['diff'] = $_SESSION['common_admin_id'];
				}
			}
		}	
		
		//区分管理员
		$this->assign('common_id', $_SESSION['common_admin_id']);
        //文章列表
        import("ORG.Util.Page");
        $count = D('Article')->where($map)->count();
        $page = new Page($count, 10);
        $art_list = D('Article')->where($map)->limit($page->firstRow . ',' . $page->listRows)->order('id DESC')->relation(true)->select();
        $this->assign('art_list', $art_list);

        //分页显示及默认页数
        $show = $page->show();
        $this->assign('page', $show); // 赋值分页输出
        $this->assign('p', C('PAGE_SIZE'));
		
		$this->assign('qishishijian', $_GET['start_time']);
		$this->assign('jieshushijian', $_GET['end_time']);
        //输出搜索的条件
        $this->assign($index);

        $this->display();
    }

    public function add() {
        $this->cat_id = intval($this->_param('cat_id'));
        $cate = D('Cate')->order('sort Asc')->relation(true)->select();
        $this->list = get_cate($cate, '&nbsp;&nbsp;├&nbsp;&nbsp;');
		//区分超级管理员与普通管理员
		$this->assign('common_id', $_SESSION['common_admin_id']);
        $this->assign('now', time());
        $this->assign('type', 'add');
        $this->display();
    }

    public function insert() {
        $data = D('Article')->create();
		//区分管理员发布的文章
		if (true === $_SESSION['is_supper_admin']) {
			$data['diff'] = 0;
		}
		else{
			if(($_SESSION['common_admin_id'] == 5) or ($_SESSION['common_admin_id'] == 12)){
				$data['diff'] = 5;
			}
			else if(($_SESSION['common_admin_id'] == 6) or ($_SESSION['common_admin_id'] == 13) or ($_SESSION['common_admin_id'] == 14) or ($_SESSION['common_admin_id'] == 15)){
				$data['diff'] = 6;
			}
			else{
				$data['diff'] = $_SESSION['common_admin_id'];
			}
		}
		
        $pics = $_POST['pics'];
        $ptitle = $_POST['pictitle'];
        if (is_array($pics)) {
            $data['pics'] = "";
            foreach ($pics as $k => $val) {

                $data['pics'].=$val . "||" . $ptitle[$k] . ":::";
            }
            $data['pics'] = substr($data['pics'], 0, -3);
        }
        $data['admin_id']=$_SESSION['myid'];
        $data['create_time'] = time();
        $data['start_time'] = strtotime($data['start_time']);
        if (!empty($data['end_time'])) {
            $data['end_time'] = strtotime($data['end_time']);
        }
 
        $r = D('Article')->add($data);
        if ($r == FALSE) {
            $this->error('发布失败', '__URL__/index');
        } else {
			/*if(($data['diff'] == 5) || ($data['diff'] == 6) || ($data['diff'] == 7) || ($data['diff'] == 8) || ($data['diff'] == 9)){
				import('ORG.Util.String');
				// 6位手机验证码
				$randval = String::randString(6, 1);
				$ret = sendPhoneVerifyCode('15566848148', $randval);
			}*/
            $this->success('发布成功', '__URL__/index');
        }
    }

    public function edit() {
        $id = intval($this->_param('id'));
        if ($id <= 0) {
            $this->error('参数错误！');
        } else {
            //栏目分类
            $cate = D('Cate')->order('sort Asc')->relation(true)->select();
            $this->list = get_cate($cate, '&nbsp;&nbsp;├&nbsp;&nbsp;');
            //根据id查找内容
            $list = D('Article')->where('id=' . $id)->relation(true)->find();
            if (!empty($list['pics'])) {
                $v = explode(":::", $list['pics']);
                foreach ($v as $a => $r) {
                    $t = explode('||', $r);
                    $s[$a]['pic'] = $t[0];
                    $s[$a]['title'] = $t[1];
                    //$data[]=$res;

                    $sname = explode('/', $s[$a]['pic']);
                    $s[$a]['savename'] = $sname[4];
                }
                $this->assign('piclist', $s);
            }
			//区分超级管理员与普通管理员
			$this->assign('common_id', $_SESSION['common_admin_id']);
            $this->assign($list);
            $this->assign('type', 'edit');
            $this->display('add');
        }
    }

    public function update() {
        $data = D('Article')->create();
        $pics = $_POST['pics'];
        $ptitle = $_POST['pictitle'];
        //存储数据
        if (is_array($pics)) {
            $data['pics'] = "";
            foreach ($pics as $k => $val) {
                $data['pics'].=$val . "||" . $ptitle[$k] . ":::";
            }
            $data['pics'] = substr($data['pics'], 0, -3);
        }
        $data['update_time'] = time();
        $data['start_time'] = strtotime($data['start_time']);
        if (!empty($data['end_time'])) {
            $data['end_time'] = strtotime($data['end_time']);
        }
        $r = D('Article')->save($data);
        if ($r == FALSE) {
            $this->error('修改失败', '__URL__/index');
        } else {
            $this->success('修改成功', '__URL__/index');
        }
    }
	
	//页面预览
	public function views() 
	{
		//获取页面提交数据
    $data['article_id'] = $_POST['id'];
		$data['title'] = $_POST['title'];
		$data['color'] = $_POST['color'];
		$data['cat_id'] = $_POST['cat_id'];
		$data['tpl'] = $_POST['tpl'];
		$data['url'] = $_POST['url'];
		$data['content'] = $_POST['content'];
		$data['tag'] = $_POST['tag'];
		$data['file'] = $_POST['file'];
		$data['status'] = $_POST['status'];
		$data['ishot'] = $_POST['ishot'];
		$data['istop'] = $_POST['istop'];
		$data['isre'] = $_POST['isre'];
		$data['keywords'] = $_POST['keywords'];
		$data['pic'] = $_POST['pic'];
		$map['article_id'] = $_POST['id'];
        $pics = $_POST['pics'];
        $ptitle = $_POST['pictitle'];
        //存储数据
        if (is_array($pics)) {
            $data['pics'] = "";
            foreach ($pics as $k => $val) {
                $data['pics'].=$val . "||" . $ptitle[$k] . ":::";
            }
            $data['pics'] = substr($_POST['pics'], 0, -3);
        }
        $data['update_time'] = time();
        $data['start_time'] = strtotime($_POST['start_time']);
        if (!empty($_POST['end_time'])) {
            $data['end_time'] = strtotime($_POST['end_time']);
        }
		//清空之前预览留下的旧数据
		$article = M('Article_back');
     	$article->where($map)->delete();
		//数据插入文章临时表
        $r = D('Article_back')->add($data);
		//进行预览
		if($data['cat_id'] == 55 || $data['cat_id'] == 56 || $data['cat_id'] == 66 || $data['cat_id'] == 67){
			 $p = A('Home/Index');
       		 $p->index();
		}
		else{
			$url = '/index.php?s=/article/preview/id/'.$data['article_id'].'.html';
			redirect($url);
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
        if (empty($ids)) {
            $this->error('没有选择记录！');
            exit();
        }
        $map['id'] = array('in', $ids);
        switch ($command) {
            case 'delete':
                $re = D('Article')->where($map)->delete();
                $type = "删除";
                break;
            case 'status':
                $re = D('Article')->where($map)->setField('status', '1');
                $type = "启用";
                break;
            case 'unstatus':
                $re = D('Article')->where($map)->setField('status', '0');
                $type = "停用";
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
