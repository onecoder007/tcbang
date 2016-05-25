<?php
namespace Common\Model;
use Common\Model\CommonModel;
class CategoryModel extends CommonModel {
	
	/*
	 * term_id category name description pid path status
	 */
	
	//自动验证
	protected $_validate = array(
			//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
			array('name', 'require', '分类名称不能为空！', 1, 'regex', 3),
	);
	
	protected function _after_insert($data,$options){
		parent::_after_insert($data,$options);
		$ctg_id=$data['ctg_id'];
		$parent_id=$data['parent'];
		if($parent_id==0){
			$d['path']="0-$ctg_id";
		}else{
			$parent=$this->where("ctg_id=$parent_id")->find();
			$d['path']=$parent['path'].'-'.$ctg_id;
		}
		$this->where("ctg_id=$ctg_id")->save($d);
	}
	
	protected function _after_update($data,$options){
		parent::_after_update($data,$options);
		if(isset($data['parent'])){
			$ctg_id=$data['ctg_id'];
			$parent_id=$data['parent'];
			if($parent_id==0){
				$d['path']="0-$ctg_id";
			}else{
				$parent=$this->where("ctg_id=$parent_id")->find();
				$d['path']=$parent['path'].'-'.$ctg_id;
			}
			$result=$this->where("ctg_id=$ctg_id")->save($d);
			if($result){
				$children=$this->where(array("parent"=>$ctg_id))->select();
				foreach ($children as $child){
					$this->where(array("ctg_id"=>$child['ctg_id']))->save(array("parent"=>$ctg_id,"ctg_id"=>$child['ctg_id']));
				}
			}
		}
		
	}
	
	protected function _before_write(&$data) {
		parent::_before_write($data);
	}
	

}