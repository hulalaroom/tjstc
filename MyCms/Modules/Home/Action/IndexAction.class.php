<?php

// 前台分组
// 默认模块
class IndexAction extends HomeAction {
	public function _empty(){
		$this->redirect("Public/404");
	}

    public function index() {

		$this->pageCount();
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
				$this->gglist = D('Article')->where('cat_id= 54 and status=1')->limit(1)->order('create_time desc')->select();
				//焦点图
				$this->jdlist = D('Article')->where('cat_id= 29 and status=1')->limit(10)->order('update_time desc')->select();
				//焦点图下方
				$this->jdxflist = D('Article')->where('cat_id= 52 and status=1')->limit(2)->order('create_time desc')->select();
				//区域动态
				$map3['cat_id'] = 161;
				//有效期
				$map3['start_time'] = array('elt', time()); //发布时间小于现在时间
				$map3['_string'] = 'end_time = 0 OR end_time > ' . time(); //结束时间大于现在时间或者等于0
				$map3['status'] = 1;
				$map3['isre'] = 1;
				$this->qylist = D('Article')->where($map3)->limit(8)->order('create_time desc')->select();
				//停功能信息
				$map['cat_id'] = 2;
				//有效期
				$map['start_time'] = array('elt', time()); //发布时间小于现在时间
				//$map['_string'] = 'end_time = 0 OR end_time > ' . time(); //结束时间大于现在时间或者等于0
				$map['status'] = 1;
				$map['isre'] = 1;
				$this->tglist = D('Article')->where($map)->limit(8)->order('create_time desc')->select();

				//活动公告
				$map1['cat_id'] = 3;
				//有效期
				$map1['start_time'] = array('elt', time()); //发布时间小于现在时间
				$map1['_string'] = 'end_time = 0 OR end_time > ' . time(); //结束时间大于现在时间或者等于0
				$map1['status'] = 1;
				$map1['isre'] = 1;
				$this->hdlist = D('Article')->where($map1)->limit(8)->order('create_time desc')->select();
				//合同预约
				$this->htlist = D('Article')->where('cat_id= 51 and status=1')->limit(1)->order('id desc')->select();
				//自助服务
				$this->zzlist = D('Cate')->where('pid= 74 and status=1')->order('sort asc')->limit(10)->select();
				//便民信息
				$this->bmlist = D('Cate')->where('pid= 45 and status=1')->order('create_time desc')->select();

				//水质公告
				$this->waterlist = D('Article')->where('cat_id= 211 and status=1')->limit(8)->order('id desc')->select();

				//在线调查
				$this->votelist = D('Vote')->Field('title,id,tpl')->where('status=1')->limit(4)->order('id desc')->select();
				//政策法规
				$this->zclist = D('Cate')->where('pid= 4 and status=1')->order('create_time desc')->select();
				//服务公告(中间位置)
				$map2['cat_id'] = array('in', '1,2,3,167,168,169,182,170,171,172,173');
				//有效期
				$map2['start_time'] = array('elt', time()); //发布时间小于现在时间
				$map2['_string'] = 'end_time = 0 OR end_time > ' . time(); //结束时间大于现在时间或者等于0
				$map2['status'] = 1;
				$this->fwlist = D('Article')->where($map2)->limit(10)->order('create_time desc')->select();

        $this->assign('serviceGuides', $serviceGuides);
        $this->assign('homePage', true);
        $this->submenu='show';
        $this->display();
    }

	//页面访问量处理
	public function pageCount() {
		$condition['id'] = 1;
		$authInfo = D('Visit')->where($condition)->find();
		//访问次数处理
         $map['num'] = $authInfo['num'] + 1;
		 D('Visit')->where($condition)->save($map);
		 //浏览记录处理
		 $data['vid'] = 1;
		 $data['visit_time'] = time();
         $data['visit_ip'] = get_client_ip();
		 D('Visit_log')->add($data);
	}

	//首页预览
	public function views1() {

		//$article_id = intval($this->_param('id'));
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
        $this->assign('serviceGuides', $serviceGuides);
		//$this->assign('article_id', $article_id);
        $this->submenu='show';
        $this->display();
    }
}