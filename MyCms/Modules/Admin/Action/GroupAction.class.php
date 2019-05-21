<?php

class GroupAction extends AdminAction {

    public function index() {
        $l = A('User');
        $l->group_list('');
        $this->display();
    }

    public function add() {
        $g = A('User');
        $g->group_list('status=1');
        $this->assign('type', 'add');
        $this->display();
    }

    public function insert() {
        D('Group')->create();
        $re = D('Group')->add();
        if ($re !== false) {
            $this->success('录入成功！', '__URL__/index');
        } else {
            $this->success('录入失败！', '__URL__/index');
        }
    }

    public function edit() {
        $id = intval($this->_param('id'));
        if ($id <= 0) {
            $this->error('参数错误！');
        } else {

            $list = D('Group')->where('id=' . $id)->find();
            $this->assign($list);
            $this->assign('type', 'edit');
            $this->display('add');
        }
    }

    public function update() {
        D('Group')->create();
        $re = D('Group')->save();
        if ($re !== false) {
            $this->success('修改成功！', '__URL__/index');
        } else {
            $this->success('修改失败！', '__URL__/index');
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
        switch ($command) {
            case 'delete':
                $re = D('Group')->where($map)->delete();
                $type = "删除";
                break;
            case 'status':
                $re = D('Group')->where($map)->setField('status', '1');
                $type = "启用";
                break;
            case 'unstatus':
                $re = D('Group')->where($map)->setField('status', '0');
                $type = "停用";
                break;
        }
        if ($re!==false) {
            $this->success('成功' . $type);
        } else {
            $this->error('失败！');
        }
    }

}

?>