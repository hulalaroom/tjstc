<?php

// 前台分组
// 文章模块
class ArticleAction extends HomeAction
{
    public function _empty() {
        $this->redirect("Public/404");
    }


    function lists()
    {
        $this->id = $this->_get('id');
        $pid = $this->_get('pid');
        if (empty($pid)) {
            $this->pid = D('Cate')->where('id=' . $this->id)->getField('pid'); //控制左侧子菜单
            $this->nav = $this->nav($this->cate, $this->id, ''); //当前位置
            $p = get_parents($this->cate, $this->id);
            $this->topid = $p[0]['id']; //顶部导航选中效果
            //文章搜索
            //栏目查询
            if (is_numeric($this->id)) {
                $c = get_childsid($this->cate, $this->id);
                $cids = $this->id;
                foreach ($c as $v) {
                    $cids .= "," . $v;
                }
                $map['cat_id'] = array('in', $cids);
            }
        } else {
            $this->pid = $this->_get('pid');
            $this->nav = $this->nav($this->cate, $this->pid, ''); //当前位置

            $p = get_parents($this->cate, $this->pid);
            $this->topid = $p[0]['id']; //顶部导航选中效果
            //文章搜索
            //栏目查询
            if (is_numeric($this->pid)) {
                $c = get_childsid($this->cate, $this->pid);
                $cids = $this->pid;
                foreach ($c as $v) {
                    $cids .= "," . $v;
                }
                $map['cat_id'] = array('in', $cids);
            }
        }


        //关键词搜索
        $keys = trim($this->_param('keys'));
        if (!empty($keys)) {
            $map['title'] = array('like', '%' . $keys . '%');
            $this->submenu = 'show';
        }
        $this->assign('keys', $keys);
        //有效期
        //$map['start_time'] = array('elt', time()); //发布时间小于现在时间
       // $map['_string'] = 'end_time = 0 OR end_time > ' . time(); //结束时间大于现在时间或者等于0
        //状态
        $map['status'] = 1;
        $map['isre'] = 1;
        //文章列表
        import("ORG.Util.Page");
        $count = D('Article')->where($map)->count();
        $page = new Page($count, C('PAGE_SIZE'));
        $page->setConfig('first', '首页');
        $page->setConfig('last', '末页');
        $page->setConfig('theme', ' %upPage%  %first%  %prePage%  %linkPage%  %nextPage% %end%%downPage%<span>%totalRow%条</span> <span>%nowPage%/%totalPage% 页</span>');
        $art_list = D('Article')->where($map)->limit($page->firstRow . ',' . $page->listRows)->order('id  DESC')->select();
        $this->assign('art_list', $art_list);
        $show = $page->show();
        $this->assign('page', $show);
        $this->display($tpl);
    }

    public function detail() {
        $id = $this->_get('id');
        if ($id <= 0) {
            $this->redirect("Public/404");
        }

        $where['id'] = $id;
        D('Article')->where($where)->setInc('click');
        $article = D('Article')->where($where)->find();
        if (empty($article)) {
            $this->redirect("Public/404");
        }

        $this->topid = -1;

        $this->nav = '<a href="/index.php?s=">首页</a><span>&gt;</span><span>焦点图</span>';

        $this->assign('article', $article);

        $this->display();
    }

    //文章内容页
    public function view()
    {

        $id = intval($this->_param('id'));

        if ($id <= 0) {
            $this->redirect("Public/404");
        }
        $where['id'] = $id;
        D('Article')->where($where)->setInc('click');
        $list = D('Article')->where($where)->find();

        if (empty($list)) {
            $this->redirect("Public/404");
        }
        $list['tags'] = explode(',', $list['tag']);

        $sestag = explode(',', $_SESSION['tags']);
        foreach ($list['tags'] as $key => $v) {
            $tag[$key]['tags'] = $v;
            if (in_array($tag[$key]['tags'], $sestag)) {
                $tag[$key]['has'] = 1;
            } else {
                $tag[$key]['has'] = 0;
            }

        }

        $list['tags'] = $tag;
        $nextview = D('Article')->where("status=1 and cat_id = " . $list['cat_id'] . " and id < " . $id)->limit(1)->order('id DESC')->find();

        $lastview = D('Article')->where("status=1 and cat_id = " . $list['cat_id'] . " and id > " . $id)->limit(1)->order('id ASC')->find();
        if($list['cat_id'] == 1){
            $this->pid = D('Cate')->where('id=2')->getField('pid'); //左侧导航
        }
        else{
            $this->pid = D('Cate')->where('id=' . $list['cat_id'])->getField('pid'); //左侧导航
        }

        $this->nav = $this->nav($this->cate, $list['cat_id'], $id); //当前位置
        $p = get_parents($this->cate, $list['cat_id']);


        $this->topid = $p[0]['id']; //顶部导航选中效果
        $this->assign($list);
        $this->assign('nextview', $nextview);
        $this->assign('lastview', $lastview);


        $this->display();

    }

    public function fuwu()
    {
        $this->id = $this->_get('id');
        $this->nav = $this->nav($this->cate, $this->id, ''); //位置导航
        $p = get_parents($this->cate, $this->id);
        $this->topid = $p[0]['id']; //顶部导航选中
        $this->display();
    }

	public function company()
    {
        $this->id = $this->_get('id');
		$status = $this->_get('status');
        $this->nav = $this->nav($this->cate, $this->id, ''); //位置导航

        $p = get_parents($this->cate, $this->id);
        if (empty($p)) {
            $this->redirect("Public/404");
        }
        $this->topid = $p[0]['id']; //顶部导航选中
		$this->assign('status', $status);
        $this->display();
    }


    public function energy()
    {
        $this->id = $this->_get('id');
        $this->nav = $this->nav($this->cate, $this->id, ''); //位置导航

        $p = get_parents($this->cate, $this->id);
        if (empty($p)) {
            $this->redirect("Public/404");
        }
        $this->topid = $p[0]['id']; //顶部导航选中
        $this->display();
    }

	public function cat()
    {
        $this->id = $this->_get('id');
        $this->nav = $this->nav($this->cate, $this->id, ''); //位置导航

        $p = get_parents($this->cate, $this->id);
        if (empty($p)) {
            $this->redirect("Public/404");
        }
        $this->topid = $p[0]['id']; //顶部导航选中
        $this->display();
    }

	public function cat1()
    {
        $this->id = $this->_get('id');
        $this->nav = $this->nav($this->cate, $this->id, ''); //位置导航

        $p = get_parents($this->cate, $this->id);
        if (empty($p)) {
            $this->redirect("Public/404");
        }
        $this->topid = $p[0]['id']; //顶部导航选中
        $this->display();
    }

    function index()
    {
        $this->id = $this->_get('id');
        $pid = $this->_get('pid');
        if (empty($pid)) {
            $this->pid = D('Cate')->where('id=' . $this->id)->getField('pid'); //控制左侧子菜单
            $this->nav = $this->nav($this->cate, $this->id, ''); //当前位置
            $p = get_parents($this->cate, $this->id);
            if (empty($p)) {
                $this->redirect("Public/404");
            }
            $this->topid = $p[0]['id']; //顶部导航选中效果
            //文章搜索
            //栏目查询
            if (is_numeric($this->id)) {
                $c = get_childsid($this->cate, $this->id);
                $cids = $this->id;
                foreach ($c as $v) {
                    $cids .= "," . $v;
                }
                $map['cat_id'] = array('in', $cids);
            }
        } else {
            $this->pid = $this->_get('pid');
            $this->nav = $this->nav($this->cate, $this->pid, ''); //当前位置
            $p = get_parents($this->cate, $this->pid);
            $this->topid = $p[0]['id']; //顶部导航选中效果
            //文章搜索
            //栏目查询
            if (is_numeric($this->pid)) {
                $c = get_childsid($this->cate, $this->pid);
                $cids = $this->pid;
                foreach ($c as $v) {
                    $cids .= "," . $v;
                }
                $map['cat_id'] = array('in', $cids);
            }
        }


        //关键词搜索
        $keys = trim($this->_param('keys'));
        if (!empty($keys)) {
            $map['title'] = array('like', '%' . $keys . '%');
            $this->submenu = 'show';
        }
        $this->assign('keys', $keys);
        //有效期
        $map['start_time'] = array('elt', time()); //发布时间小于现在时间
        $map['_string'] = 'end_time = 0 OR end_time > ' . time(); //结束时间大于现在时间或者等于0
        //状态
        $map['status'] = 1;
		$map['isre'] = 1;
        //文章列表
        import("ORG.Util.Page");
        $count = D('Article')->where($map)->count();
        $page = new Page($count, C('PAGE_SIZE'));
        $page->setConfig('first', '首页');
        $page->setConfig('last', '末页');
        $page->setConfig('theme', ' %upPage%  %first%  %prePage%  %linkPage%  %nextPage% %end%%downPage%<span>%totalRow%条</span> <span>%nowPage%/%totalPage% 页</span>');
        $art_list = D('Article')->where($map)->limit($page->firstRow . ',' . $page->listRows)->order('id  DESC')->select();

        $this->assign('art_list', $art_list);
        $show = $page->show();
        $this->assign('page', $show);
        $this->display($tpl);
    }

	 function index1()
    {
        $this->id = $this->_get('pid');
        $this->nav = $this->nav($this->cate, $this->id, ''); //位置导航
		$map['cat_id'] = $this->id;
        $p = get_parents($this->cate, $this->id);

        //有效期
        $map['start_time'] = array('elt', time()); //发布时间小于现在时间
        $map['_string'] = 'end_time = 0 OR end_time > ' . time(); //结束时间大于现在时间或者等于0
        //状态
        $map['status'] = 1;
		$map['isre'] = 1;
        //文章列表
        import("ORG.Util.Page");
        $count = D('Article')->where($map)->count();
        $page = new Page($count, C('PAGE_SIZE'));
        $page->setConfig('first', '首页');
        $page->setConfig('last', '末页');
        $page->setConfig('theme', ' %upPage%  %first%  %prePage%  %linkPage%  %nextPage% %end%%downPage%<span>%totalRow%条</span> <span>%nowPage%/%totalPage% 页</span>');
        $art_list = D('Article')->where($map)->limit($page->firstRow . ',' . $page->listRows)->order('id  DESC')->select();

        $this->assign('art_list', $art_list);
        $show = $page->show();
        $this->assign('page', $show);
        $this->display($tpl);
    }



	//焦点图和区域动态内容页
    public function view1()
    {
        $id = intval($this->_param('id'));
        if ($id <= 0) {
            $this->redirect("Public/404");
        }
        $where['id'] = $id;
        D('Article')->where($where)->setInc('click');
        $list = D('Article')->where($where)->find();

        if (empty($list)) {
            $this->redirect("Public/404");
        }
        $list['tags'] = explode(',', $list['tag']);

        $sestag = explode(',', $_SESSION['tags']);
        foreach ($list['tags'] as $key => $v) {
            $tag[$key]['tags'] = $v;
            if (in_array($tag[$key]['tags'], $sestag)) {
                $tag[$key]['has'] = 1;
            } else {
                $tag[$key]['has'] = 0;
            }

        }

        $list['tags'] = $tag;

        $nextview = D('Article')->where("cat_id = " . $list['cat_id'] . " and id < " . $id)->limit(1)->order('id DESC')->find();

        $lastview = D('Article')->where("cat_id = " . $list['cat_id'] . " and id > " . $id)->limit(1)->order('id ASC')->find();

        $this->pid = D('Cate')->where('id=' . $list['cat_id'])->getField('pid'); //左侧导航

        $this->nav = $this->nav($this->cate, $list['cat_id'], $id); //当前位置
        $p = get_parents($this->cate, $list['cat_id']);
        $this->topid = $p[0]['id']; //顶部导航选中效果
        $this->assign($list);
        $this->assign('nextview', $nextview);
        $this->assign('lastview', $lastview);
        $this->display();

    }

	//手机客户端展示页
    public function down()
    {
        $this->display();
    }

    //标签订阅列表页
    public function tag()
    {
        $this->id = $this->_get('id');
        $p = get_parents($this->cate, $this->id);
        $this->topid = $p[0]['id']; //顶部导航选中效果
        //订阅列表
        $tag = trim($_GET['tag']);
        if (!empty($tag)) {
            $map['tag'] = array('like', '%' . $tag . '%');
        }
        //有效期
        $map['start_time'] = array('elt', time()); //发布时间小于现在时间
        $map['_string'] = 'end_time = 0 OR end_time > ' . time(); //结束时间大于现在时间或者等于0
        //状态
        $map['status'] = 1;
        //文章列表
        import("ORG.Util.Page");
        $count = D('Article')->where($map)->count();
        $page = new Page($count, C('PAGE_SIZE'));
        $tag_list = D('Article')->where($map)->limit($page->firstRow . ',' . $page->listRows)->order('id  DESC')->select();

        $this->assign('tag_list', $tag_list);
        $show = $page->show();
        $this->assign('topid', '20');
        $this->assign('tag', $tag);
        $this->assign('page', $show);
        $this->display($tpl);
    }

    //搜索
    // function search()
    // {
        // //关键词搜索
        // if (array_key_exists('keys', $_POST)) {
            // $keys = trim($_POST['keys']);
        // } elseif (array_key_exists('keys', $_GET)) {
            // $keys = trim($_GET['keys']);
        // }
        // $keys = strip_tags($keys);
        // $map['title'] = array('like', '%' . $keys . '%');
        // $this->assign('keys', $keys);

        // //$this->submenu = 'show';

        // //有效期
        // $map['start_time'] = array('elt', time()); //发布时间小于现在时间
        // $map['_string'] = 'end_time = 0 OR end_time > ' . time(); //结束时间大于现在时间或者等于0
        // //状态
        // $map['status'] = 1;
		// $map['isre'] = 1;
        // //文章列表
        // import("ORG.Util.Page");
        // $count = D('Article')->where($map)->count();
		// $this->assign('artCount', $count);
        // $page = new Page($count, C('PAGE_SIZE'));
        // $page->setConfig('first', '首页');
        // $page->setConfig('last', '末页');
        // $page->setConfig('theme', ' %upPage%  %first%  %prePage%  %linkPage%  %nextPage% %end%%downPage%<span>%totalRow%条</span> <span>%nowPage%/%totalPage% 页</span>');
        // $art_list = D('Article')->where($map)->limit($page->firstRow . ',' . $page->listRows)->order('id  DESC')->select();
        // $this->assign('art_list', $art_list);
        // $show = $page->show();
        // $this->assign('page', $show);
        // //面包屑
        // $this->nav = '<span>关键词—[' . $keys . ']</span>';
        // $this->display();
    // }
	
	/**
     * 重写搜索
     * by sun
     */
    public function search() {
       //关键词搜索
        if (array_key_exists('keys', $_POST)) {
            $keys = trim($_POST['keys']);
        } elseif (array_key_exists('keys', $_GET)) {
            $keys = trim($_GET['keys']);
        }
        $keys = strip_tags($keys);
		$condition['title'] =array('like', '%' . $keys . '%');
		$condition['content'] = array('like', '%' . $keys . '%');
		$condition['_logic'] = 'or';
		$map['_complex'] = $condition;
        //$map['title'] = array('like', '%' . $keys . '%');
        $this->assign('keys', $keys);

        //$this->submenu = 'show';

        //有效期
        $map['start_time'] = array('elt', time()); //发布时间小于现在时间
        $map['_string'] = 'end_time = 0 OR end_time > ' . time(); //结束时间大于现在时间或者等于0
        //状态
        $map['status'] = 1;
		$map['isre'] = 1;

        $this->totalCount = D('Article')->where($map)->count();

        // *******************************************************
        $this->pageLength = 20;
        $this->pageCount = ceil($this->totalCount /  $this->pageLength);
        $paged = $this->_get('page');
        if (null == $paged || $paged <= 0) {
            $paged = 1;
        } else if ($paged > $this->pageCount) {
            $paged = $this->pageCount;
        }

        $this->paged = $paged;
        $this->prevPage = $paged > 1 ? $paged - 1 : 1;
        $this->nextPage = $paged < $this->pageCount ? $paged + 1 : $this->pageCount;
        // *******************************************************

        // ---------------------------------------------------------------------------------------------------->>>
        $firstRow = ($paged-1) * $this->pageLength;
        $this->tglist = D('Article')->where($map)->limit($firstRow . ',' . $this->pageLength)->order('start_time desc')->select();
		
        // ----------------------------------------------------------------------------------------------------<<<

        // *******************************************************
        $viewPaged = 9;
        if ($this->pageCount > $viewPaged) {
            if ($paged <= 5) {
                $this->pageStart = 1;
                $this->pageEnd = 9;
            } else {
                if ($paged + 4 <= $this->pageCount) {
                    $this->pageStart = $paged - 5 + 1;
                    $this->pageEnd = $paged + 4;
                } else {
                    $this->pageEnd = $this->pageCount;
                    $this->pageStart = $this->pageCount - $viewPaged + 1;
                }
            }
        } else {
            $this->pageStart = 1;
            $this->pageEnd = $this->pageCount;
        }
        // *******************************************************

        // 显示页面
        $this->display();
    }

	//自助服务搜索
    function service()
    {
		$id = intval($_POST['id']);
        //关键词搜索
        if (array_key_exists('searchinput', $_POST)) {
            $keys = trim($_POST['searchinput']);
        } elseif (array_key_exists('searchinput', $_GET)) {
            $keys = trim($_GET['searchinput']);
        }
        $keys = strip_tags($keys);

        $map['title'] = array('like', '%' . $keys . '%');
        $this->assign('searchinput', $keys);

        $this->submenu = 'show';

        //有效期
        $map['start_time'] = array('elt', time()); //发布时间小于现在时间
        $map['_string'] = 'end_time = 0 OR end_time > ' . time(); //结束时间大于现在时间或者等于0
        //状态
        $map['status'] = 1;
		$map['isre'] = 1;
		//服务信息栏目id
    	$map['cat_id']  = $id;
        //文章列表
        import("ORG.Util.Page");
        $count = D('Article')->where($map)->count();
        $page = new Page($count, C('PAGE_SIZE'));
        $page->setConfig('first', '首页');
        $page->setConfig('last', '末页');
        $page->setConfig('theme', ' %upPage%  %first%  %prePage%  %linkPage%  %nextPage% %end%%downPage%<span>%totalRow%条</span> <span>%nowPage%/%totalPage% 页</span>');
        $art_list = D('Article')->where($map)->limit($page->firstRow . ',' . $page->listRows)->order('id  DESC')->select();
        $this->assign('art_list', $art_list);
        $show = $page->show();
        $this->assign('page', $show);
        //面包屑
        $this->nav = '<span>关键词—[' . $keys . ']</span>';
        $this->display('service');
    }

	//预览文章内容页
    public function preview()
    {
        $article_id = intval($this->_param('id'));
        if ($article_id <= 0) {
            $this->redirect("Public/404");
        }
        $where['article_id'] = $article_id;
		D('Article_back')->where($where)->setInc('click');
        $list = D('Article_back')->where($where)->find();

        if (empty($list)) {
            $this->redirect("Public/404");
        }
        $list['tags'] = explode(',', $list['tag']);

        $sestag = explode(',', $_SESSION['tags']);
        foreach ($list['tags'] as $key => $v) {
            $tag[$key]['tags'] = $v;
            if (in_array($tag[$key]['tags'], $sestag)) {
                $tag[$key]['has'] = 1;
            } else {
                $tag[$key]['has'] = 0;
            }
        }

        $list['tags'] = $tag;
       /* $nextview = D('Article_back')->where("cat_id = " . $list['cat_id'] . " and id < " . $id)->limit(1)->order('id DESC')->find();

        $lastview = D('Article_back')->where("cat_id = " . $list['cat_id'] . " and id > " . $id)->limit(1)->order('id ASC')->find();

        $this->pid = D('Cate')->where('id=' . $list['cat_id'])->getField('pid'); //左侧导航
        $this->nav = $this->nav($this->cate, $list['cat_id'], $id); //当前位置
        $p = get_parents($this->cate, $list['cat_id']);
        $this->topid = $p[0]['id']; //顶部导航选中效果*/
        $this->assign($list);
       /* $this->assign('nextview', $nextview);
        $this->assign('lastview', $lastview);*/
        $this->display();

    }

	 public function download()
    {
        $this->display();
    }

	 public function downArea()
    {
        $this->display();
    }

}