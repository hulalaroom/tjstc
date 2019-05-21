<?php
/*空类*/
class EmptyAction extends Action {
	//空模块
	public function index(){
		$this->redirect("Public/404");
    }
	//空操作
	public function _empty(){
		$this->redirect("Public/404");
	}
}