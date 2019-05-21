<?php
class PublicAction extends Action {

    function login() {
        if (!isset($_SESSION[C('USER_AUTH_KEY')])) {
            $this->display();
        } else {
            $this->redirect('Index/index');
        }
    }

    // 登录检测
    public function checkLogin() {
        if (empty($_POST['adminname'])) {
            $this->error('请输入用户名！');
        } elseif (empty($_POST['password'])) {
            $this->error('请输入密码！');
        }
        /*zhrz
        elseif (empty($_POST['verify'])) {
            $this->error('验证码不可为空！');
        }
        end*/
        //生成认证条件
        $map = array();
        // 支持使用绑定帐号登录
        $map['adminname'] = $_POST['adminname'];
        $map["ad_admin.status"] = array('gt', 0);
        /*zhrz
        if (session('verify') != md5($_POST['verify'])) {
            $this->error('验证码错误！');
        }
         */
        import('ORG.Util.RBAC');

        //$authInfo = RBAC::authenticate($map);

        $authInfo = D('Admin')->field('ad_admin.*,b.id as role_id,b.name as role_name')
            ->join(' ad_role_admin a ON a.user_id = ad_admin.id')
            ->join(' ad_role b ON b.id=a.role_id')
            ->where($map)->find();
//        echo D('Admin')->getLastSql();
//        var_dump($authInfo);
//        die;

//        dump($authInfo);
        //使用用户名、密码和状态的方式进行认证
        if (false == $authInfo) {
            $this->error('用户名不存在或未激活！');
        } else {
            if ($authInfo['password'] != md5('my' . $_POST['password'])) {
                $this->error('密码错误！');
            }
            $_SESSION[C('USER_AUTH_KEY')] = $authInfo['id'];
            $_SESSION['admin_nick'] = $authInfo['admin_nick'];
            if ($authInfo['role_id'] == 2) {
                $_SESSION['is_supper_admin'] = true;
            } else {
				//判断普通管理员身份
				$_SESSION['common_admin_id'] = $authInfo['id'];
				
                $_SESSION['is_supper_admin'] = false;
            }
            //id为1的用户，为超级管理员
            if ($authInfo['role_id'] == 1 || $authInfo['role_id'] == 2) {
                $_SESSION['myadmin'] = true;
            }
            //保存登录信息
            // 缓存访问权限
            RBAC::saveAccessList();

            $data = array ();
            $data ['id'] = $authInfo ['id'];
            $data ['last_login_time'] = time();
            $data ['login_count'] = $authInfo ['login_count'] + 1;
            $data ['last_login_ip'] = get_client_ip();

            D('Admin')->save($data);

            $this->success('登录成功！', '__GROUP__/Index/index');
        }
    }

    public function verify() {
        $type = isset($_GET['type']) ? $_GET['type'] : 'gif';
        import("ORG.Util.Image");
        ob_end_clean();
        Image::buildImageVerify(4, 1, $type,100,50);
    }

    // 用户登出
    public function logout() {
        if (isset($_SESSION[C('USER_AUTH_KEY')])) {
            unset($_SESSION[C('USER_AUTH_KEY')]);
            unset($_SESSION);
            session_destroy();
            if (!empty($_GET['info'])) {
                $this->success($_GET['info'], __URL__ . '/login/');
            } else {
                $this->success('您已成功退出登录！', __URL__ . '/login/');
            }
        } else {

            $this->error('已经退出，请重新登录！');
        }
    }

    // 更换密码
    public function repass() {
        $data = D('Admin')->create();
        if ($data !== FALSE) {
            $oldpass = D('Admin')->where('id=' . $_SESSION[C('USER_AUTH_KEY')])->getField('password');

            if ($oldpass !== md5('my' . $_POST['pass'])) {
                $this->error('原密码错误！');
            }
            if ($_POST['newpass'] !== $_POST['newpassok']) {
                $this->error('两次密码不同！');
            } else {
                $data['password'] = md5('my' . $_POST['newpass']);
                $data['id']=$_SESSION[C('USER_AUTH_KEY')];
                $re = D('Admin')->save($data);
            }

            if ($re !== false) {
                $this->success('修改成功！');
				$this->logout();
				echo "<script>window.parent.location.reload();</script>";
				
            } else {
                $this->error('修改失败！');
            }
        } else {
            $this->display();
        }
		
    }

}

?>
