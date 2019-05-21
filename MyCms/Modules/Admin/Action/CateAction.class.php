<?php

class CateAction extends AdminAction {

    function index() {
        $cate = D('Cate')->order('sort Asc')->relation(true)->select();
        $this->list = get_cate($cate, '&nbsp;&nbsp;├&nbsp;&nbsp;');
        $this->display();
    }

    function add() {
        $pid = intval($this->_get('pid'));
        if ($pid > 0) {
            $this->assign('pid', $pid);
        } else if ($pid < 0) {
            $this->error('参数错误！');
        }
        //模型
        $mlist = D('Model')->where('status=1')->select();
        $this->assign('mlist', $mlist);
        //上级栏目
        $Cate = D('Cate')->order('sort Asc')->select();
        $this->list = get_cate($Cate, '&nbsp;&nbsp;├&nbsp;&nbsp;');
        $this->assign('type', 'add');
        $this->display();
    }

    function insert() {
        if ($vo = D('Cate')->create()) {
            $re = D('Cate')->add();
            if ($re !== false) {
                //判断如果是单页模型，直接创建单页数据
                if ($vo['mid'] == 2) {
                    $row['id'] = $re;
                    $row['title'] = $vo['title'];
                    $row['admin_id'] = $_SESSION['myid'];
                    D('Page')->add($row);
                }
                $this->success("添加成功", U('Cate/index'));
            } else {
                $this->error('添加失败');
            }
        } else {
            $this->error(D('Cate')->getError());
        }
    }

    public function edit() {
        $id = intval($this->_param('id'));
        if ($id <= 0) {
            $this->error('参数错误！');
        } else {
            //模型
            $mlist = D('Model')->where('status=1')->select();
            $this->assign('mlist', $mlist);
            //上级栏目
            $Cate = D('Cate')->order('sort Asc')->select();
            $this->list = get_cate($Cate, '&nbsp;&nbsp;├&nbsp;&nbsp;');
            //根据id查找内容
            $data = D('Cate')->where('id=' . $id)->find();
            $this->assign($data);
            $this->assign('type', 'edit');
            $this->display('add');
        }
    }

    public function update() {
        $vo = D('Cate')->create();
        $re = D('Cate')->save($vo);
        if ($re !== FALSE) {
            $p = D('Page')->where('id=' . $vo['id'])->find();
            //判断如果是单页模型，直接创建单页数据
            if ($vo['mid'] == 2 && empty($p)) {
                $row['id'] = $vo['id'];
                $row['title'] = $vo['title'];
                $row['admin_id'] = $_SESSION['myid'];
                D('Page')->add($row);
            } elseif (!empty($p)) {
                $row['id'] = $p['id'];
                $row['title'] = $vo['title'];
                D('Page')->save($row);
            }
            $this->success('修改成功', U('Cate/index'));
        } else {
            $this->error('修改失败', U('Cate/index'));
        }
    }

    //批量操作
    function bat() {

        $command = I('command');
        $ids = I('id');
	 if (empty($command)) {
            $this->error('没有选择操作！');
            exit();
        }
        $cate = D('Cate')->order('sort Asc')->select();

        foreach ($ids as $v) {
            $iddata[] = $v;
            $c = get_childsid($cate, $v);
            if (!empty($c)) {
                $idss[] = $c;
            }
        }
        foreach ($idss as $k => $va) {
            foreach ($va as $val) {
                $iddata[] = $val;
            }
        }


       
        switch ($command) {
            case 'delete':
                 $map['id'] = array('in', $iddata);
                $re = D('Cate')->where($map)->relation(true)->delete();
                $type = "删除";
                break;
            case 'status':
                $map['id'] = array('in', $ids);
                $re = D('Cate')->where($map)->setField('status', '1');
                $type = "启用";
                break;
            case 'unstatus':
                $map['id'] = array('in', $ids);
                $re = D('Cate')->where($map)->setField('status', '0');
                $type = "停用";
                break;
            case 'sort':
                $sort = $this->_post('sort');
                foreach ($sort as $key => $val) {
                    $re = D('Cate')->where('id=' . $key)->setField('sort', $val);
                }
                $type = "排序";
        }
        if ($re !== false) {
            $this->success('成功' . $type);
        } else {
            $this->error('失败！');
        }
    }

}

?>
