<?php

//后台分组
//默认模块
class IndexAction extends AdminAction {

    //后台首页，输出菜单项
    public function index() {
        /*if (true === $_SESSION['is_supper_admin']) {
            $menu = D('Menu')->where('status=1')->order('sort Asc')->select();
        } else {
            $menu = D('Menu')->field("ad_menu.*")
                ->join("inner join (select distinct mid as id from ad_menu_acl where uid = " . $_SESSION[C('USER_AUTH_KEY')] . " union all"
                . " select distinct mpid as id from  ad_menu_acl where uid=". $_SESSION[C('USER_AUTH_KEY')]. ") t on t.id=ad_menu.id")
                ->where('ad_menu.status=1')->order('ad_menu.sort Asc')->select();
			$menu = D('Menu')->where('status=1')->order('sort Asc')->select();
        }*/

		/*start update by wangshipeng 2014.06.06*/
		$menu = D('Menu')->where('status=1')->order('sort Asc')->select();
        $menudata = get_cate($menu);
		
        $menulist = array();
        foreach ($menudata as $val) {
			$menulist[$val['pid']][] = $val;
			/*if (true === $_SESSION['is_supper_admin']) {
				
			}
			else if ($_SESSION['common_admin_id'] == 4)	{
				if($val['pid'] == 8 && $val['id'] == 27){
					$menulist[$val['pid']][] = $val;
				}
			}
			else{
				if($val['id'] == 8){
					$menulist[$val['pid']][] = $val;
				}
			}*/
        }
		//var_dump($menulist);
		$this->assign('diff', $_SESSION['is_supper_admin']);
		$this->assign('common_id', $_SESSION['common_admin_id']);
		/*end update by wangshipeng 2014.06.06*/
        $this->assign('menulist', $menulist);
		$this->display();
		
    }

    //服务器常规信息
    public function main() {
        $data['serverOs'] = PHP_OS;
        $data['phpVersion'] = PHP_VERSION;
        $data['fileupload'] = ini_get('file_uploads') ? ini_get('upload_max_filesize') : '禁止上传';
        $data['serverUri'] = $_SERVER['SERVER_NAME'];
        $data['maxExcuteTime'] = ini_get('max_execution_time') . ' 秒';
        $data['maxExcuteMemory'] = ini_get('memory_limit');
        $data['magic_quote_gpc'] = MAGIC_QUOTE_GPC ? '开启' : '关闭';
        $data['allow_url_fopen'] = ini_get('allow_url_fopen') ? '开启' : '关闭';
        $data['notice'] = D('Admin')->where('id=' . $_SESSION['myid'])->getField('notice');
        $this->assign($data);
        $this->display();
    }

    public function notice() {
        if (!empty($_POST['notebook'])) {
            $val = $_POST['notebook'];
            $r = D('Admin')->where('id=' . $_SESSION['myid'])->setField('notice', $val);
            if ($r !== false) {
                $data['status'] = 'true';
                $data['message'] = '保存成功！';
                echo json_encode($data);
            }
        } else {
            $this->error('备忘数据错误！');
        }
    }
    
        public function cache() {

        $caches = array(
            "HomeCache" => array("name" => "网站前台缓存文件", "path" => RUNTIME_PATH . "Cache/Home/"),
            "AdminCache" => array("name" => "网站后台缓存文件", "path" => RUNTIME_PATH . "Cache/Admin/"),
        );
        if (!empty($_POST)) {
			if(empty($_POST['id'])){
				$this->error('没有选择操作项！');
			}else{
            foreach ($_POST['id'] as $p) {

                delDirAndFile($caches[$p]['path']);
            }
            $this->success('删除成功！');
			}
        } else {
            $this->assign("caches", $caches);
            $this->display();
        }
    }

    public function makecate(){
         $cate = D('Cate')->where('status=1')->order('sort Asc')->relation(true)->select();
        if (F('cate', $cate)) {
            $this->success('修改成功');
        } else {
            $this->error('修改失败');
        }
    }

	//静态页列表
    public function view() {
        
		$this->display();
    }

	//静态页生成
    public function make() {
		
       	$specialId = D('Cate')->where("title='专项业务' and status=1")->find();
        if (is_array($specialId)) {
            $specialId = $specialId['id'];
        }

        $this->assign('specialId', $specialId);

        $serviceGuide = D('Cate')->where("title='服务信息' and status=1")->find();
        if (is_array($specialId)) {
            $serviceGuide = $serviceGuide['id'];
        }

        $serviceGuides = D('Cate')->where('pid=' . $serviceGuide['id'] . ' and status=1')->order('sort')->select();
		
		//弹出层广告
		$this->gglist = D('Article')->where('cat_id= 54 and status=1')->limit(1)->order('id desc')->select();
		//焦点图
		$this->jdlist = D('Article')->where('cat_id= 29 and status=1')->limit(4)->order('create_time asc')->select();
		//焦点图下方
		$this->jdxflist = D('Article')->where('cat_id= 52 and status=1')->limit(2)->order('id desc')->select();
		//区域动态
		$map3['cat_id'] = array('in', '161');
		//有效期
		$map3['start_time'] = array('elt', time()); //发布时间小于现在时间
		$map3['_string'] = 'end_time = 0 OR end_time > ' . time(); //结束时间大于现在时间或者等于0
		$map3['status'] = 1;
		$map3['isre'] = 1;
		$this->qylist = D('Article')->where($map3)->limit(9)->order('id desc')->select();
		//停功能信息
		$map['cat_id'] = array('in', '167,168,169,182');
		//有效期
		$map['start_time'] = array('elt', time()); //发布时间小于现在时间
		//$map['_string'] = 'end_time = 0 OR end_time > ' . time(); //结束时间大于现在时间或者等于0
		$map['status'] = 1;
		$map['isre'] = 1;
		$this->tglist = D('Article')->where($map)->limit(6)->order('create_time desc')->select();
		//活动公告
		$map1['cat_id'] = array('in', '170,171,172,173');
		//有效期
		$map1['start_time'] = array('elt', time()); //发布时间小于现在时间
		$map1['_string'] = 'end_time = 0 OR end_time > ' . time(); //结束时间大于现在时间或者等于0
		$map1['status'] = 1;
		$map1['isre'] = 1;
		$this->hdlist = D('Article')->where($map1)->limit(6)->order('id desc')->select();
		//合同预约
		$this->htlist = D('Article')->where('cat_id= 51 and status=1')->limit(1)->order('id desc')->select();
		//自助服务
		$this->zzlist = D('Cate')->where('pid= 74 and status=1')->order('sort asc')->limit(10)->select();
		//便民信息
		$this->bmlist = D('Cate')->where('pid= 45 and status=1')->order('id desc')->select();
		//在线调查
		$this->votelist = D('Vote')->Field('title,id,tpl')->where('status=1')->limit(4)->order('id desc')->select();
		//政策法规
		$this->zclist = D('Cate')->where('pid= 4 and status=1')->order('id desc')->select();
		//服务公告
		$map2['cat_id'] = array('in', '1,2,3,167,168,169,182,170,171,172,173');
		//有效期
		$map2['start_time'] = array('elt', time()); //发布时间小于现在时间
		$map2['_string'] = 'end_time = 0 OR end_time > ' . time(); //结束时间大于现在时间或者等于0
		$map2['status'] = 1;
		$this->fwlist = D('Article')->where($map2)->limit(10)->order('id desc')->select();
		//首页导航
		$this->dhlist = D('Cate')->where('pid=0 and ismenu=1')->limit(5)->order('id asc')->select();
				
        $this->assign('serviceGuides', $serviceGuides);
        $this->submenu='show';
		$r = $this->buildHtml('index', HTML_PATH.'/', 'home');
		if($r !=null && $r !=""){
			$this->success('生成成功！');
		}
		else{
			$this->error("生成失败，请重试！");
		}
		$this->display('view');
    }

}

?>
