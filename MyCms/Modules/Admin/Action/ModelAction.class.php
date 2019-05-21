<?php
class ModelAction extends CommonAction{
	//模型主页
    public function index(){
		$m_model = D('model');
		$model = $m_model->select();
		//print_r($model);exit;
		$this->assign('model',$model);
		$this->display();
	}
	//添加
	public function add(){
		$this->assign('type', 'add');
		$this->display();
	}
	public function insert(){
		
		$data = D('Model')->create();
		$r = D('Model')->add($data);
		if(!$r){
			$this->error("添加失败！");
		}
		$this->success('添加成功', '__URL__/index');
	}
	//修改
	public function edit(){
	
		$id = intval($_GET['id']);
		if(!empty($id)){
			$model= D('model')->where(" id = $id")->find();
		}
		$this->assign($model);
		$this->display("add");
	}
	public function update(){
	
		$id = intval($_POST['id']);
		$data= D('model')->create();
		$r = D('model')->where("id = $id")->save($data);
		if($r == false){
			$this->error('更新失败！');
		}
		$this->success('更新成功', '__URL__/index');
	}
	//删除
	public function delete(){
		 $id = intval($_GET['id']);
		 $m_model = D('model');
		 if(!empty($id)){
			$r = $m_model->where("id = $id")->delete();
		 }
		 
		 if($r == false){
			$this->error('删除失败！');
		 }
		 $this->success('删除成功', '__URL__/index');
	}
}
?>
