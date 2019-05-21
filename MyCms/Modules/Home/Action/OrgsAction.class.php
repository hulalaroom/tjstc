<?php
/*
 * ***************************************************************************
 * File Description  : 天津生态城网站-成员单位 Controller
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
 * Orgs Action Controller
 *
 * @category    天津生态城网站
 * @package     Controller
 * @copyright   1999-2018 Dalian Haixin Information Engineering Co., Ltd.
 * @version     1.0
 */
class OrgsAction extends HomeAction {
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

        $this->cid = $cid;
        $this->id = $id;
        $this->nav = $this->nav($this->cate, $id, '');

        // 显示页面
        $this->display();
    }
}