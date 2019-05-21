<?php

class MapAction extends AdminAction {
    public function index() {
        $list = D("Map")->select();
        $this->assign("content", $list[0]['contents']);
        $this->display();
    }

    public function update() {
        $data['contents'] = $_POST['content'];
        $list = D("Map")->select();
        if (is_null($list)) {
            $ret = D("Map")->data($data)->add();
        } else {
            $data['id'] = $list[0]['id'];
            $ret = D("Map")->data($data)->save();
        }

        if ($ret === FALSE) {
            $this->error('保存失败!');
        } else {
            $this->success('保存成功!', '__URL__/index');
        }
    }
} 