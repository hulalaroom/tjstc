<?php

class ConfigAction extends AdminAction {

    function index() {
        $this->display();
    }

    function site_update() {

        if (F('site', $_POST, CONF_PATH)) {
			$this->del_cache();
            $this->success('修改成功');
        } else {
            $this->error('修改失败');
        }
    }

    function attach() {
        $this->display();
    }

    function attach_update() {
        if (F('attach', $_POST, CONF_PATH)) {
			$this->del_cache();
            $this->success('修改成功');
        } else {
            $this->error('修改失败');
        }
    }

    function email() {
        $this->display();
    }

    function email_update() {
        if (F('email', $_POST, CONF_PATH)) {
			$this->del_cache();
            $this->success('修改成功');
        } else {
            $this->error('修改失败');
        }
    }
	function  content(){
		//echo C('user_public_reg1_1');
		$this->display();
	}
	
	function content_update() {
        if (F('content', $_POST, CONF_PATH)) {
			$this->del_cache();
            $this->success('修改成功');
        } else {
            $this->error('修改失败');
        }
    }
      function weixin() {
        $this->display();
    }

    function weixin_update() {
        if (F('weixin', $_POST, CONF_PATH)) {
			$this->del_cache();
            $this->success('修改成功');
        } else {
            $this->error('修改失败');
        }
    }

	function del_cache() { 
	    unlink('./Cache/~runtime.php');// 删除主编译缓存文件
	} 

    /*
     * 后台菜单设置 
     */

    function menu() {
        $menu = D('Menu')->order('sort Asc')->select();
        $this->list = get_cate($menu, '&nbsp;&nbsp;├&nbsp;&nbsp;');
        $this->display();
    }

    function menuadd() {
        $pid = intval($this->_get('pid'));
        if ($pid > 0) {
            $this->assign('pid', $pid);
        } else if ($pid < 0) {
            $this->error('参数错误！');
        }
        $menu = D('Menu')->order('sort Asc')->select();
        $this->list = get_cate($menu, '&nbsp;&nbsp;├&nbsp;&nbsp;');

        $user_list = D('Admin')->field('ad_admin.*')
            ->join(' ad_role_admin a ON a.user_id = ad_admin.id')
            ->join(' ad_role b ON b.id=a.role_id')
            ->where(" ad_admin.status=1 and b.id=1")->select();

        $this->assign('user_list', $user_list);

        $this->assign('type', 'add');
        $this->display();
    }

    function menuinsert() {
        $menu = D('Menu');
        if ($data = $menu->create()) {
            $menu->startTrans();
            $re = $menu->add();
            if ($re !== false) {
                // insert acl
                $menuAcl = D('Menu_acl');
                $menuAcl->startTrans();

                $adminors = $_POST['user_id'];

                $menuData = array();
                $menuData['mid'] = $re;
                $menuData['mpid'] = $data['pid'];

                $ret = true;
                foreach ($adminors as $uid) {
                    $menuData['uid'] = $uid;

                    try {
                        $ret = $menuAcl->add($menuData);
                    } catch (Exception $ex) {
                        $ret = false;
                    }

                    if ($ret === false) {
                        break;
                    }
                }

                if ($ret === false) {
                    $menuAcl->rollback();
                    $menu->rollback();
                    $this->error('添加失败');
                } else {
                    $menu->commit();
                    $this->success("添加成功", U('Config/menu'));
                }
            } else {
                $menu->rollback();
                $this->error('添加失败');
            }
        } else {
            $this->error($menu->getError());
        }
    }

    public function menuedit() {
        $id = intval($this->_param('id'));
        if ($id <= 0) {
            $this->error('参数错误！');
        } else {
            $menu = D('Menu')->order('sort Asc')->select();
            $this->list = get_cate($menu, '&nbsp;&nbsp;├&nbsp;&nbsp;');

            $user_list = D('Admin')->field('ad_admin.*')
                ->join(' ad_role_admin a ON a.user_id = ad_admin.id')
                ->join(' ad_role b ON b.id=a.role_id')
                ->where(" ad_admin.status=1 and b.id=1")->select();

            $this->assign('user_list', $user_list);

            //根据id查找内容
            $data = D('Menu')->where('id=' . $id)->find();

            $userIds = D('Menu_acl')->field('uid')->where("mid=" . $id)->select();
            if (is_null($userIds)) {
                $data['userIds'] = array();
            } else {
                foreach($userIds as $userId) {
                    $data['userIds'][] = $userId['uid'];
                }
            }

            $this->assign($data);
            $this->assign('type', 'edit');
            $this->display('menuadd');
        }
    }

    public function menuupdate() {
        $menu = D('Menu');
        $data = $menu->create();
        $menu->startTrans();
        $r = $menu->save($data);
        if ($r === FALSE) {
            $menu->rollback();
            $this->error('修改失败', U('Config/menu'));
        } else {
            $menuAcl = D('Menu_acl');
            $menuAcl->startTrans();

            $adminors = $_POST['user_id'];
            try {
                if (is_null($adminors) || (is_array($adminors) && empty($adminors))) {
                    // delete from ad_menu_acl
                    $menuAcl->where("mid=" . $data['id'])->delete();
                } elseif (is_array($adminors) && 0 < sizeof($adminors)) {
                    $curDatas = $menuAcl->field("uid")->where("mid=" . $data['id'])->select();
                    if (is_null($curDatas) || empty($curDatas)) {
                        $diff = $adminors;
                    } else {
                        $curData = array();
                        foreach($curDatas as $userId) {
                            $curData[] = $userId['uid'];
                        }
                        $diff = array_diff($adminors, $curData);
                    }

                    $map['mid'] = $data['id'];
                    $map['uid'] = array('not in', $adminors);
                    $menuAcl->where($map)->delete();

                    $menuData = array();
                    $menuData['mid'] = $data['id'];
                    $menuData['mpid'] = $data['pid'];
                    foreach ($diff as $uid) {
                        $menuData['uid'] = $uid;
                        $menuAcl->add($menuData);
                    }
                }
                $menuAcl->commit();
            } catch (Exception $e) {
                $menuAcl->rollback();
                $menu->rollback();

                $this->success('修改失败', U('Config/menu'));
                return;
            }

            $menu->commit();
            $this->success('修改成功', U('Config/menu'));
        }
    }

    //批量操作
    function menubat() {

        $command = I('command');
        $ids = I('id');

        if (is_array($ids)) {
            $ids = implode(',', $ids);
        }
        $map['id'] = array('in', $ids);
        switch ($command) {
            case 'delete':
                $menu = D('Menu');
                $menu->startTrans();
                $re = $menu->where($map)->delete();
                if ($re !== false) {
                    $menuAcl = D('Menu_acl');
                    $menuAcl->startTrans();
                    $aclmap['mid'] = array('in', $ids);
                    $re = $menuAcl->where($aclmap)->delete();
                    if ($re === false) {
                        $menuAcl->rollback();
                    }
                }
                if ($re === false) {
                    $menu->rollback();
                } else {
                    $menuAcl->commit();
                    $menu->commit();
                }

                $type = "删除";
                break;
            case 'status':
                $re = D('Menu')->where($map)->setField('status', '1');
                $type = "启用";
                break;
            case 'unstatus':
                $re = D('Menu')->where($map)->setField('status', '0');
                $type = "停用";
                break;
            case 'sort':
                $sort = $this->_post('sort');
                foreach ($sort as $key => $val) {
                    $re = D('Menu')->where('id=' . $key)->setField('sort', $val);
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
