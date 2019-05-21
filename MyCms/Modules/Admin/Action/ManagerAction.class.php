<?php
/**
 * Created by lzhang
 *
 * Date:
 */

class ManagerAction extends AdminAction {
    public function index() {
        $map = array();
        //每页条数
        if (!empty($_GET['limit'])) {
            $limit = $_GET['limit'];
            $index['limit'] = $_GET['limit'];
        } else {
            $limit = C('PAGE_SIZE');
            $index['limit'] = C('PAGE_SIZE');
        }
        //状态查询
        if (array_key_exists('status', $_GET) && is_numeric($_GET['status'])) {
            $map['ad_admin.status'] = $_GET['status'];
            $index['status'] = $_GET['status'];
        }
        //关键词
        if (!empty($_GET['keyword'])) {
            $map[$_GET['types']] = array('like', '%' . $_GET['keyword'] . '%');
            $index['types'] = $_GET['types'];
            $index['keyword'] = $_GET['keyword'];
        }
        // 用户角色组
        if (array_key_exists('group_id', $_GET) && is_numeric($_GET['group_id'])) {
            $map['role_id'] = array('eq',$_GET['group_id']);
            $index['group_id'] = $_GET['group_id'];
        }

        //$data = array('id' => 2, 'name'=>'普通管理员');
        //$r = D('Role')->save($data);

        $glist = D('Role')->where("status = 1")->select();
        $this->assign('glist', $glist);

        //列表
        import("ORG.Util.Page");
        $count = D('Admin')->where($map)->count();
        $page = new Page($count, $limit);
        $user_list = D('Admin')->field('ad_admin.*,b.id as role_id,b.name as role_name')
            ->join(' ad_role_admin a ON a.user_id = ad_admin.id')
            ->join(' ad_role b ON b.id=a.role_id')
            ->where($map)->order('ad_admin.last_login_time desc')->limit($page->firstRow . ',' . $page->listRows)->select();

        $this->assign('user_list', $user_list);
        //分页显示及默认页数
        $show = $page->show();
        $this->assign('page',$show);// 赋值分页输出
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
            $glist = D('Role')->where("status = 1")->select();
            $this->assign('glist', $glist);

            $list = D('Admin')->field('ad_admin.*,b.id as role_id,b.name as role_name')
                ->join(' ad_role_admin a ON a.user_id = ad_admin.id')
                ->join(' ad_role b ON b.id=a.role_id')
                ->where("ad_admin.id = " . $id)->find();

            $this->assign($list);
            $this->assign('type', 'edit');
            $this->display('add');
        }
    }

    public function update() {
        $admin = D('Admin');
        $data = $admin->create();
        if (array_key_exists('password', $_POST)) {
            if (!empty($_POST['password'])) {
                $data['password'] = md5('my' . $_POST['password']);
            } else {
                unset($data['password']);
            }
        }
        $admin->startTrans();
        $re = $admin->data($data)->save();
        if ($re !== false) {
            try {
                $roleAdmin = D('Role_admin');
                $roleAdmin->startTrans();
                $roleAdmin->where(array("user_id" => $data['id']))->delete();

                $dataRole = $roleAdmin->create();
                $dataRole['user_id'] = $data['id'];
                $roleAdmin->data($dataRole)->add();
                if ($re !== false) {
                    $admin->commit();
                    $roleAdmin->commit();
                }
            } catch (Exception $e) {
                $admin->rollback();
                $roleAdmin->rollback();
            }
            $this->success('修改成功！', '__URL__/index');
        } else {
            $admin->rollback();
            $this->success('修改失败！', '__URL__/index');
        }
    }

    public function add() {
        $glist = D('Role')->where("status = 1")->select();
        $this->assign('glist', $glist);

        $this->assign('type', 'add');
        $this->display('add');
    }

    public function insert() {
        //D('Admin')->create();
        $admin = D('Admin');
        $data = $admin->create();
        if (empty($data['adminname'])) {
            $this->error('请设置用户名！');
        } elseif (empty($data['password'])) {
            $this->error('请设置密码！');
        } elseif (empty($data['admin_nick'])) {
            $this->error('请设置真实姓名！');
        } else {
            $data['password'] = md5('my' . $data['password']);
            $admin->startTrans();
            $re = $admin->data($data)->add();
            if ($re !== false) {
                $dataRole = D('Role_admin')->create();
                $dataRole['user_id'] = $re;
                try {
                    $re = D('Role_admin')->data($dataRole)->add();
                } catch(Exception $e) {
                    $re = false;
                }

                if ($re !== false) {
                    $admin->commit();
                    $this->success('录入成功！', '__URL__/index');
                } else {
                    $admin->rollback();
                    $this->error('录入失败！');
                }
            } else {
                $admin->rollback();
                $this->error('录入失败！用户名已存在！');
            }
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
        $admin = D('Admin')->where($map);
        switch ($command) {
            case 'delete':
                $admin->startTrans();
                $admin->delete();
                try {
                    $map['user_id'] = array('in', $ids);
                    $re = D("Role_admin")->where($map)->delete();
                } catch(Exception $e) {
                    $re = false;
                }
                if ($re !== false) {
                    $admin->commit();
                } else {
                    $admin->rollback();
                }
                $type = "删除";
                break;
            case 'status':
                $re = $admin->setField('status', '1');
                $type = "启用";
                break;
            case 'unstatus':
                $re = $admin->setField('status', '0');
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