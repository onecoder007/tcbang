<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
/**
 * 文章内页
 */
namespace Shop\Controller;
use Common\Controller\HomeBaseController;
class ArticleController extends HomeBaseController {
    //文章内页
    public function index() {
    	$id=intval($_GET['id']);
    	$article=sp_sql_good($id,'');
		
    	$ctgid=$article['ctg_id'];
    	$category_obj= M("Category");
    	$term=$category_obj->where("ctg_id='$ctgid'")->find();
    	
    	$article_id=$article['good_id'];
		
		/*每次访问 都 +1 */
    	$Goods_model=M("Goods");
    	$Goods_model->save(array("good_id"=>$article_id,"post_hits"=>array("exp","post_hits+1")));
    	
    	$article_date=$article['post_modified'];
    	
    	$join = "".C('DB_PREFIX').'goods_url as b on a.url_id =b.url_id';
    	$join2= "".C('DB_PREFIX').'users as c on a.post_author = c.id';
    	$gs= M("goods");
    	
    	$next=$gs->alias("a")->join($join)->join($join2)->where(array("post_modified"=>array("egt",$article_date), "a.good_id"=>array('neq',$id), "status"=>1,'ctg_id'=>$ctgid))->order("post_modified asc")->find();
    	$prev=$gs->alias("a")->join($join)->join($join2)->where(array("post_modified"=>array("elt",$article_date), "a.good_id"=>array('neq',$id), "status"=>1,'ctg_id'=>$ctgid))->order("post_modified desc")->find();
		
		/*信息处理*/
		$article['goods_name'] = explode("|**|",$article['goods_name']);
		array_pop($article['goods_name']);
		$article['url'] = explode("|**|",$article['url']);
		array_pop($article['url']);
		$article['img'] = explode("|**|",$article['img']);
		array_pop($article['img']);
		
		$article['price'] = json_decode($article['price']);
		$article['kuaidi'] = json_decode($article['kuaidi']);
		/*处理结束*/
		
    	$this->assign("next",$next);
    	$this->assign("prev",$prev);
		   	
    	$content_data=sp_content_page($article['post_content']);
    	$article['post_content']=$content_data['content'];
    	$this->assign("page",$content_data['page']);
    	$this->assign($article);
    	$this->assign("smeta",$smeta);
    	$this->assign("term",$term);
    	$this->assign("article_id",$article_id);
    	
    	$tplname=$term["one_tpl"];
    	$tplname=sp_get_apphome_tpl($tplname, "article");
    	$this->display(":$tplname");
    }
    
    public function do_like(){
    	$this->check_login();
    	
    	$id=intval($_GET['id']);//posts表中id
    	
    	$posts_model=M("Posts");
    	
    	$can_like=sp_check_user_action("posts$id",1);
    	
    	if($can_like){
    		$posts_model->save(array("id"=>$id,"post_like"=>array("exp","post_like+1")));
    		$this->success("赞好啦！");
    	}else{
    		$this->error("您已赞过啦！");
    	}
    	
    }
	
	public function link(){
		$codeurl = I('get.code');
		$codeurl=base64_decode($codeurl);
		
		if(!empty($codeurl)){
			$this->assign('codeurl',$codeurl);
			$this->display();
		}else{
			$this->error("链接已失效，请联系管理员",'/Shop/index',1);
		}
		
	}
}
