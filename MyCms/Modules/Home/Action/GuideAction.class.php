<?php
/*
 * ***************************************************************************
 * File Description  : 天津生态城网站-服务指南 Controller
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
 * Guide Action Controller
 *
 * @category    天津生态城网站
 * @package     Controller
 * @copyright   1999-2018 Dalian Haixin Information Engineering Co., Ltd.
 * @version     1.0
 */
class GuideAction extends HomeAction {
    /**
     * 共通设置顶部导航项目高亮显示
     */
    public function _initialize() {
        parent::_initialize();
        // 首页
        $this->assign('topid', 30);
    }

    /**
     * Not Found page
     */
    public function _empty() {
        $this->redirect("Public/404");
    }

    /**
     * 服务指南
     *
     */
    public function view() {
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