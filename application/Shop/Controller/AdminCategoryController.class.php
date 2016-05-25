<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Tuolaji <479923197@qq.com>
// +----------------------------------------------------------------------
namespace Shop\Controller;
use Common\Controller\AdminbaseController;
class AdminCategoryController extends AdminbaseController {
	
	//protected $terms_model;
	//protected $taxonomys=array("article"=>"文章","picture"=>"图片");
	protected $category_model;
	
	function _initialize() {
		parent::_initialize();
		$this->category_model = D("Common/Category");
		//$this->assign("taxonomys",$this->taxonomys);
	}
	function index(){
		$result = $this->category_model->order(array("ord"=>"asc"))->select();
		
		$tree = new \Tree();
		$tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
		$tree->nbsp = '&nbsp;&nbsp;&nbsp;';
		foreach ($result as $r) {
			$r['str_manage'] = '<a href="' . U("AdminCategory/add", array("parent" => $r['ctg_id'])) . '">添加子类</a> | <a href="' . U("AdminCategory/edit", array("id" => $r['ctg_id'])) . '">修改</a> | <a class="J_ajax_del" href="' . U("AdminCategory/delete", array("id" => $r['ctg_id'])) . '">删除</a> ';
			$url=U('Shop/list/index',array('id'=>$r['ctg_id']));
			$r['url'] = $url;
			$r['taxonomys'] = $this->taxonomys[$r['taxonomy']];
			$r['id']=$r['ctg_id'];
			$r['parentid']=$r['parent'];
			$array[] = $r;
		}
		
		$tree->init($array);
		$str = "<tr>
					<td><input name='listorders[\$id]' type='text' size='3' value='\$listorder' class='input input-order'></td>
					<td>\$id</td>
					<td>\$spacer <a href='\$url' target='_blank'>\$name</a></td>
	    			<td>\$taxonomys</td>
					<td align='center'><a href='\$url' target='_blank'>访问</a></td>
					<td>\$str_manage</td>
				</tr>";
		$taxonomys = $tree->get_tree(0, $str);
		$this->assign("taxonomys", $taxonomys);
		$this->display();
	}
	
	
	function add(){
	 	$parentid = intval(I("get.parent"));
	 	$tree = new \Tree();
	 	$tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
	 	$tree->nbsp = '&nbsp;&nbsp;&nbsp;';
	 	$categories = $this->category_model->order(array("path"=>"asc"))->select();
	 	
	 	$new_categories=array();
	 	foreach ($categories as $r) {
	 		$r['id']=$r['ctg_id'];
	 		$r['parentid']=$r['parent'];
			$r['selected']= (!empty($parentid) && $r['ctg_id']==$parentid)? "selected":"";
	 		$new_categories[] = $r;
	 	}
	 	$tree->init($new_categories);
	 	$tree_tpl="<option value='\$id' \$selected>\$spacer\$name</option>";
	 	$tree=$tree->get_tree(0,$tree_tpl);
	 	
	 	$this->assign("categories_tree",$tree);
	 	$this->assign("parent",$parentid);
	 	$this->display();
	}
	
	function add_post(){
		if (IS_POST) {
			if ($this->category_model->create()) {
				if ($this->category_model->add()!==false) {
					$this->success("添加成功！",U("AdminCategory/index"));
				} else {
					$this->error("添加失败！");
				}
			} else {
				$this->error($this->category_model->getError());
			}
		}
	}
	
	function edit(){
		$id = intval(I("get.id"));
		$data=$this->category_model->where(array("ctg_id" => $id))->find();
		$tree = new \Tree();
		$tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
		$tree->nbsp = '&nbsp;&nbsp;&nbsp;';
		$terms = $this->category_model->where(array("ctg_id" => array("NEQ",$id), "path"=>array("notlike","%-$id-%")))->order(array("path"=>"asc"))->select();
		
		$new_terms=array();
		foreach ($terms as $r) {
			$r['id']=$r['ctg_id'];
			$r['parentid']=$r['parent'];
			$r['selected']=$data['parent']==$r['ctg_id']?"selected":"";
			$new_terms[] = $r;
		}
		
		$tree->init($new_terms);
		$tree_tpl="<option value='\$id' \$selected>\$spacer\$name</option>";
		$tree=$tree->get_tree(0,$tree_tpl);
		
		$this->assign("categories_tree",$tree);
		$this->assign("data",$data);
		$this->display();
	}
	
	function edit_post(){
		if (IS_POST) {
			if ($this->category_model->create()) {
				if ($this->category_model->save()!==false) {
					$this->success("修改成功！");
				} else {
					$this->error("修改失败！");
				}
			} else {
				$this->error($this->category_model->getError());
			}
		}
	}
	
	//排序
	public function listorders() {
		$status = parent::_listorders($this->category_model);
		if ($status) {
			$this->success("排序更新成功！");
		} else {
			$this->error("排序更新失败！");
		}
	}
	
	/**
	 *  删除
	 */
	public function delete() {
		$id = intval(I("get.id"));
		$count = $this->category_model->where(array("parent" => $id))->count();
		
		if ($count > 0) {
			$this->error("该菜单下还有子类，无法删除！");
		}
		
		if ($this->category_model->delete($id)!==false) {
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}
	}
	
}