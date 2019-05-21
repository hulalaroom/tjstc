<?php

class NewsAction extends HomeAction
{
    // 空操作
    public function _empty()
    {
        $this->redirect("Public/404");
    }

    // 服务信息lists方法
    public function lists()
    {
        $this->check();
        $this->id = intval($this->_param('id'));
        $this->nav = $this->nav($this->cate, $this->id, ''); // 当前位置
        $p = get_parents($this->cate, $this->id);
        if (empty($p)) {
            $this->redirect("Public/404");
        }
        $this->topid = $p [0] ['id']; // 顶部导航选中效果

        if (is_numeric($this->id)) {
            $c = get_childsid($this->cate, $this->id);
            $cids = $this->id;
            foreach ($c as $v) {
                $cids .= "," . $v;
            }
            $map['cat_id'] = array('in', $cids);
        }

        $userid = $_SESSION ['uid'];
        $cat_id = intval($_GET ['id']);

        $map['userid'] = array('eq', $userid);
        import("ORG.Util.Page");
        $count = D('Business')->where($map)->count();
        $page = new Page($count, C('PAGE_SIZE'));
        $page->setConfig('first', '首页');
        $page->setConfig('last', '末页');
        $page->setConfig('theme', ' %upPage%  %first%  %prePage%  %linkPage%  %nextPage% %end%%downPage%<span>%totalRow%条</span> <span>%nowPage%/%totalPage% 页</span>');

        $list = D('Business')->where($map)->limit($page->firstRow . ',' . $page->listRows)->order('id  DESC')->select();
		
        $this->assign('businessList', $list);
        $show = $page->show();
        $this->assign('page', $show);

        $this->display();
    }

	function special()
    {
        $this->id = intval($this->_param('id'));
        $this->pid = D('Cate')->where('id=' . $this->id)->getField('pid'); //左侧导航
        $this->nav = $this->nav($this->cate, $this->id, ''); //当前位置
        $p = get_parents($this->cate, $this->id);
        if (empty($p)) {
            $this->redirect("Public/404");
        }

        $map['cat_id'] = $this->id;
        $map['status'] = 1;

        //文章列表
        import("ORG.Util.Page");
        $count = D('Article')->where($map)->count();
        $sql = D('Article')->getLastSql();
        $page = new Page($count, C('PAGE_SIZE'));
        $page->setConfig('first', '首页');
        $page->setConfig('last', '末页');
        $page->setConfig('theme', ' %upPage%  %first%  %prePage%  %linkPage%  %nextPage% %end%%downPage%<span>%totalRow%条</span> <span>%nowPage%/%totalPage% 页</span>');
        $art_list = D('Article')->where($map)->limit($page->firstRow . ',' . $page->listRows)->order('id DESC')->select();

        $this->assign('art_list', $art_list);
        $show = $page->show();
        $this->assign('page', $show);

        $this->display();
    }
	
}

?>
