<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace Shop\Controller;
use Common\Controller\HomeBaseController;
/**
 * 文章列表
*/
class ListController extends HomeBaseController {

	//文章内页
	public function index() {
		$ctg_id = I('get.id');
		
		$category_new = M('Category');
		$category=$category_new->where("ctg_id=$ctg_id")->find();
		
		if(empty($category)){
		    header('HTTP/1.1 404 Not Found');
		    header('Status:404 Not Found');
		    if(sp_template_file_exists(MODULE_NAME."/404")){
		        $this->display(":404");
		    }
		    	
		    return ;
		}
		
		$terms_model=sp_get_terms('where:ctg_id>0;order:ctg_id ASC;datatable:Category;');
		foreach ($terms_model as $key=>$val){
			$terms[$val['ctg_id']] = $val;
			
			if($val['parent']==$ctg_id && $ctg_id>0){
				$terms_children[$val['ctg_id']]=$val;
			}
			
			if($val['parent']==0){
				$terms_parents[$val['ctg_id']]=$val;
			}
			
		}
		foreach($terms as $tkey=>$tval){
			if($terms[$ctg_id]['parent']>0 && $tval['parent']==$terms[$ctg_id]['parent']){
				$terms_children[] = $tval;
			}
		}
		
		if($ctg_id>0){
			
			$terms_children_ids = array_keys($terms_children);
			$terms_children_ids[] = $ctg_id;
			
			$where['ctg_id'] = array('IN',$terms_children_ids);
			
			if($terms[$ctg_id]['parent']==0){
				$parent_id=$ctg_id;
			}else{
				$parent_id=$terms[$ctg_id]['parent'];
			}
		}
		
		$goods_model = M('Goods');
		$where['post_status'] = 1;
		$where['g_status'] = 1;
		$goods = $goods_model->where($where)->select();
		
		$this->assign($category);
		$this->assign('parent_id',$parent_id);
		$this->assign('terms_parents',$terms_parents);
		$this->assign('terms_children',$terms_children);
		$this->assign('goods',$goods);
		$tplname=$term["list_tpl"];
    	$tplname=sp_get_apphome_tpl($tplname, "list");
		$this->display(":$tplname");

	}
	
	public function nav_index(){
		$navcatname="文章分类";
		$datas=sp_get_terms("field:term_id,name");
		$navrule=array(
				"action"=>"List/index",
				"param"=>array(
						"id"=>"term_id"
				),
				"label"=>"name");
		exit(sp_get_nav4admin($navcatname,$datas,$navrule));
		
	}
	
}
