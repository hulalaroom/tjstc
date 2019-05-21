<?php
/*
 * ***************************************************************************
 * File Description  : 天津生态城网站-政策法规 Controller
 * File Version      : 1.0
 * Legal Copyright   : Copyright(C) 1999-2018 Dalian Haixin Information Engineering Co., Ltd.
 * Company Name      : NTT Software Corporation
 * Legal Trademark   : Dalian Haixin Information Engineering Co., Ltd.
 * Original Filename : RuleAction.class.php
 * Author            : lzhang
 * Create Date       : 2018-09-13
 * Product Version   : 2.0
 * Product Name      : 天津生态城网站
 * ***************************************************************************
 */

/**
 * Rule Action Controller
 *
 * @category    天津生态城网站
 * @package     Controller
 * @copyright   1999-2018 Dalian Haixin Information Engineering Co., Ltd.
 * @version     1.0
 */
class RuleAction extends HomeAction {
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
     * 政策法规信息
     * 政策法规: 供热服务,供水服务,供气服务,中水服务
     */
    public function index() {
        $id = $this->_param('id');
        if (intval($id) <= 0) {
            $this->redirect("Public/404");
        }

        $cid = $this->_param('cid');
        if (intval($cid) <= 1) {
             $cid = 1;
        }

        $this->cid = $cid;
        $this->id = $id;
        $this->nav = $this->nav($this->cate, $id, '');

        $catIdArr = array('1' => 176, '2' => 175, '3' => 174, '4' => 181);

        $map['cat_id'] = $catIdArr[$cid];

        //有效期
        $map['start_time'] = array('elt', time()); //发布时间小于现在时间
        //$map['_string'] = 'end_time = 0 OR end_time > ' . time(); //结束时间大于现在时间或者等于0
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
        $this->tglist = D('Article')->where($map)->limit($firstRow . ',' . $this->pageLength)->order('start_time desc')->select();
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

        $rule = D('Article')->where($where)->find();
        if (empty($rule)) {
            $this->redirect("Public/404");
        }
        $rule['tags'] = explode(',', $rule['tag']);

        $this->assign('rule', $rule);

        $param['cat_id'] = $rule['cat_id'];

        $param['start_time'] = array('elt', time()); //发布时间小于现在时间
        $param['status'] = 1;
        $param['isre'] = 1;

        $param['start_time'] = array('gt', $rule['start_time']);
        $prevview = D('Article')->where($param)->limit(1)->order('start_time asc')->find();
        $this->assign('prevview', $prevview);

        $param['start_time'] = array('lt', $rule['start_time']);
        $nextview = D('Article')->where($param)->limit(1)->order('start_time DESC')->find();
        $this->assign('nextview', $nextview);

        // 导航
        $this->id = $id;
        $this->nav = $this->nav($this->cate, $rule['cat_id'], '');

        // 显示页面
        $this->display();
    }
}