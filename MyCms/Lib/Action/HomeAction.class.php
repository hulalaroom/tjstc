<?php

class HomeAction extends CommonAction {

    function _initialize() {
        $this->cate = D('Cate')->where('status=1')->order('sort Asc')->select();
        //新消息
        $map['status'] = 0;
        $map['toid'] = $_SESSION['uid'];
        $count = D('UserMessage')->where($map)->count();
        $this->assign('count', $count);
        $this->about = get_child($this->cate, '22');
		$r=MODULE_NAME.'-'.ACTION_NAME;
        $k=array_search ( $r,$_SESSION['rule']);

        $session_config = C('SESSION_OPTIONS');
			$session_config['expire']=180;
        if(!isset($_SESSION['last_access_time']) || (time() - $_SESSION['last_access_time']) < $session_config['expire']) {

            $_SESSION['last_access_time'] = time();
        }
	


        if($k!==false){
            $ok=D('User')->where('id='.$_SESSION['uid'])->setInc('point',$_SESSION['score'][$k]);
        }
    }

    public function check() {

	
		 
		
        $_SESSION['userurl']= $_SERVER['REQUEST_URI'];
		
        // if one of fields in $_SESSION, then show the login page
        if (!isset($_SESSION ['checklogin']) || !isset($_SESSION ['uname']) || !isset($_SESSION ['ugroup']) || !isset($_SESSION ['uid'])) {
			 //通过sessionid更改用户下线状态
		  $sessionId = session_id();
		  session_start();
		  $data = array ();
		  $id['sessionId']= $sessionId;
		  $newid=D('User')->where($id)->getField('id');
		  $data ['id'] = $newid;
		  $data['login_state']=0;
		  D('User')->save($data);
		  session_destroy();
            $this->redirect("Public/login");
        }
        // session expired
        else {
			
            $session_config = C('SESSION_OPTIONS');
            if((time() - $_SESSION['last_access_time']) > $session_config['expire']) {
          //通过sessionid更改用户下线状态
		  
		  $sessionId = session_id();
		  session_start();
		  $data = array ();
		  $id['sessionId']= $sessionId;
		  $newid=D('User')->where($id)->getField('id');
		  $data ['id'] = $newid;
		  $data['login_state']=0;

		  D('User')->save($data);
		  session_destroy();
		  
                $this->redirect("Public/login");
            }
        }
        //if (!isset($_SESSION['checklogin'])) {
        //    $this->redirect("Public/login");
        //}
    }

	   /*
 *
 * 面包屑
 *
 */

function nav($cate, $cid, $id, $model = 'Article') {
    $nav = '<a  href="__GROUP__">首页</a>';
    $p = get_parents($cate, $cid);
    $t = count($p) - 1;

    foreach ($p as $key => $v) {

        /*
		if (empty($v['tpl'])) {
            $url = U($v['Model']['name'] . '/index', 'id=' . $v['id']);
        } else {
            $url = U($v['Model']['name'] . '/' . $v['tpl'], 'id=' . $v['id']);
        }
		*/
		if (empty($v['url'])) {
            $url = U($v['Model']['name'] . '/index', 'id=' . $v['id']);
        } else {
            //$url = U($v['Model']['name'] . '/' . $v['tpl'], 'id=' . $v['id']);
            $url = $v['url'];
        }

        if (empty($id)) {
            if ($t !== $key) {
                if ($v['id'] == 4) {
                    $nav.= '<span>&gt;</span>' . $v['title'];
                } else {
                    $nav.= '<span>&gt;</span><a href="' . $url . '">' . $v['title'] . '</a>';
                }
            } else {
                $nav.= '<span>&gt;</span><span>' . $v['title'] . '</span>';
            }
        } else {
            $nav.= '<span>&gt;</span><a href="' . $url . '">' . $v['title'] . '</a>';
        }
    }
    if (!empty($id)) {
        $title = D($model)->where('id=' . $id)->getField('title');
        $nav.= '<span>&gt;</span><span>' . cutstr_html($title,'20') . '</span>';
        $this->assign('title',$title);
    }else{
        $title = D('Cate')->where('id='.$cid)->getField('title');
        $this->assign('title',$title);
    }
    return $nav;
}

}

?>
