<?php
/*
 * ***************************************************************************
 * File Description  : 天津生态城网站-服务标准 Controller
 * File Version      : 1.0
 * Legal Copyright   : Copyright(C) 1999-2018 Dalian Haixin Information Engineering Co., Ltd.
 * Company Name      : NTT Software Corporation
 * Legal Trademark   : Dalian Haixin Information Engineering Co., Ltd.
 * Original Filename : OrgsAction.class.php
 * Author            : lzhang
 * Create Date       : 2018-09-13
 * Product Version   : 2.0
 * Product Name      : 天津生态城网站
 * ***************************************************************************
 */

/**
 * Srv Action Controller
 *
 * @category    天津生态城网站
 * @package     Controller
 * @copyright   1999-2018 Dalian Haixin Information Engineering Co., Ltd.
 * @version     1.0
 */
class SrvAction extends HomeAction {
    /**
     * 共通设置顶部导航项目高亮显示
     */
    public function _initialize() {
        parent::_initialize();
        // 首页
        $this->assign('topid', 4);
    }

    /**
     * Not Found page
     */
    public function _empty() {
        $this->redirect("Public/404");
    }

    /**
     * 成员单位信息
     * 成员单位: 能源公司,市政景观公司,水务公司,环保公司,交通公司
     */
    public function index() {
        $id = $this->_param('id');
        if (intval($id) <= 0) {
            $this->redirect("Public/404");
        }

        $cid = $this->_param('cid');
        if (intval($id) <= 1) {
             $cid = 1;
        }

        $map['cat_id'] = 184;

        //有效期
        $map['start_time'] = array('elt', time()); //发布时间小于现在时间
        $map['status'] = 1;
        $map['isre'] = 1;

        $this->totalCount = D('Article')->where($map)->count();

        // *******************************************************
        $this->pageLength = 20;
        $this->pageCount = ceil($this->totalCount /  $this->pageLength);
        $paged = $this->_get('page');
        if (null == $paged || $paged <= 0) {
            $paged = 1;
        } else if ($paged > $this->pageCount) {
            $paged = $this->pageCount;
        }

        $this->paged = $paged;
        $this->prevPage = $paged > 1 ? $paged - 1 : 1;
        $this->nextPage = $paged < $this->pageCount ? $paged + 1 : $this->pageCount;
        // *******************************************************

        // ---------------------------------------------------------------------------------------------------->>>
        $firstRow = ($paged-1) * $this->pageLength;
        $this->srvList = D('Article')->where($map)->limit($firstRow . ',' . $this->pageLength)->order('start_time desc')->select();
        // ----------------------------------------------------------------------------------------------------<<<

        // *******************************************************
        $viewPaged = 9;
        if ($this->pageCount > $viewPaged) {
            if ($paged <= 5) {
                $this->pageStart = 1;
                $this->pageEnd = 9;
            } else {
                if ($paged + 4 <= $this->pageCount) {
                    $this->pageStart = $paged - 5 + 1;
                    $this->pageEnd = $paged + 4;
                } else {
                    $this->pageEnd = $this->pageCount;
                    $this->pageStart = $this->pageCount - $viewPaged + 1;
                }
            }
        } else {
            $this->pageStart = 1;
            $this->pageEnd = $this->pageCount;
        }
        // *******************************************************


        $this->cid = $cid;
        $this->id = $id;
        $this->nav = $this->nav($this->cate, $id, '');

        // 显示页面
        $this->display();
    }


    public function view() {
        $id = $this->_param('id');
        if (intval($id) <= 0) {
            $this->redirect("Public/404");
        }

        $where['id'] = $id;
        // 点击查看次数+1
        D('Article')->where($where)->setInc('click');

        $srv = D('Article')->where($where)->find();
        if (empty($srv)) {
            $this->redirect("Public/404");
        }
        $srv['tags'] = explode(',', $srv['tag']);

        $this->assign('srv', $srv);

        $param['cat_id'] = 184;
        $param['start_time'] = array('elt', time()); //发布时间小于现在时间
        $param['status'] = 1;
        $param['isre'] = 1;

        $param['start_time'] = array('gt', $srv['start_time']);
        $prevview = D('Article')->where($param)->limit(1)->order('start_time asc')->find();
        $this->assign('prevview', $prevview);

        $param['start_time'] = array('lt', $srv['start_time']);
        $nextview = D('Article')->where($param)->limit(1)->order('start_time DESC')->find();
        $this->assign('nextview', $nextview);

        // 导航
        $this->nav = $this->nav($this->cate, 6, '');

        // 显示页面
        $this->display();
    }
}