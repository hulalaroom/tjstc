<?php
/*
 * ***************************************************************************
 * File Description  : 天津生态城网站-手机应用 Controller
 * File Version      : 1.0
 * Legal Copyright   : Copyright(C) 1999-2018 Dalian Haixin Information Engineering Co., Ltd.
 * Company Name      : NTT Software Corporation
 * Legal Trademark   : Dalian Haixin Information Engineering Co., Ltd.
 * Original Filename : AppAction.class.php
 * Author            : lzhang
 * Create Date       : 2018-09-13
 * Product Version   : 2.0
 * Product Name      : 天津生态城网站
 * ***************************************************************************
 */

/**
 * App Action Controller
 *
 * @category    天津生态城网站
 * @package     Controller
 * @copyright   1999-2018 Dalian Haixin Information Engineering Co., Ltd.
 * @version     1.0
 */
class AppAction extends HomeAction {
    /**
     * 共通设置顶部导航项目高亮显示
     */
    public function _initialize() {
        parent::_initialize();
        // 首页
        $this->assign('homePage', true);
    }

    /**
     * Not Found page
     */
    public function _empty() {
        $this->redirect("Public/404");
    }

    /**
     * 手机应用download
     * 四类项目: 1.停供能信息；2.水质公告；3.活动通知；4.区域动态
     */
    public function download() {
        // 显示页面
        $this->display();
    }
}