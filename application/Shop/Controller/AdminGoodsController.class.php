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
class AdminGoodsController extends AdminbaseController {
	
	protected $category_model;
	protected $goods_model;
	protected $goods_url_model;
	
	function _initialize() {
		parent::_initialize();
		
		$this->category_model = M("Category");
		$this->goods_model = D("Goods");
		$this->goods_url_model = M("Goods_url");
		$this->store_model = M("Store");
	}
	function index(){
		$this->_lists();
		$this->_getTree();
		$this->display();
	}
	
	function add(){
		$categories = $this->category_model->order(array("listorder"=>"asc"))->select();
		$ctg_id = intval(I("get.category"));
		$this->_getTermTree();
		$category=$this->category_model->where("ctg_id=$ctg_id")->find();
		$this->assign("author","1");
		$this->assign("category",$category);
		$this->assign("categories",$categories);
		$this->display();
	}
	
	function add_post(){
		if (IS_POST) {
			if(empty($_POST['goods']['ctg_id'])){
				$this->error("请至少选择一个分类栏目！");
			}
			
			$goods = I("post.goods");
			$goods['post_title'] = htmlspecialchars_decode(trim($goods['post_title']));
			$goods['post_keywords']=trim($goods['post_keywords']);
			$goods['post_description']=trim($goods['post_description']);
			$goods['post_content']=htmlspecialchars_decode($goods['post_content']);
			
			$goods_name = array_filter($goods['name']);
			unset($goods['name']);
			$goods_price = array_filter($goods['price']);
			$goods_kuaidi = array_filter($goods['kuaidi']);
			foreach ($goods_name as $key=>$value){
				$name .= trim($value).'|**|';
				$price[] = trim($goods_price[$key]);
				$kuaidi[] = trim($goods_kuaidi[$key]);
			}
			$goods['goods_name'] = $name;
			$goods['price'] = json_encode($price);
			$goods['kuaidi'] = json_encode($kuaidi);
			
			/*套餐处理*/
			$goods['meal_name'] = trim($goods['meal_name']);
			$goods['meal_price'] = trim($goods['meal_price']);
			$goods['meal_kuaidi'] = trim($goods['meal_kuaidi']);
			
			$goods['post_date']=date("Y-m-d H:i:s",time());
			$goods['post_author']=get_current_admin_id();
			
			$url_post = $_POST['url'];
			$img = I('post.img');
			
			foreach ($url_post['goods'] as $k=>$v){
				$v = trim($v);
				if($v=='' || empty($v)){
					unset($url_post[$k]);
				}
				if(strlen($v) > 50){
					$v = xw_shorturl($v);
					if(is_array($v)){
						$this->error($v['err_msg']);
					}
				}
				
				$url['url'] .= $v."|**|";
				$goods['img'] .= trim($img[$k])."|**|";
			}
			$url_short = trim($url_post['meal']);
			
			if(strlen($url_short) > 50){
				$url_short = xw_shorturl($url_short);
				if(is_array($url_short)){
					$this->error($url_short['err_msg']);
				}
			}
			
			$url['url_meal'] = $url_short;
			unset($url_post);
			
			$store_post = I('post.store');
			$store_where['sto_name'] = $store_post['sto_name'];
			$store_find = $this->store_model->where($store_where)->field('sto_id')->find();
			$store_result = $store_find['sto_id'];
			if(empty($store_result)){
				$store_post['sto_status'] = 1;
				$store_result = $this->store_model->add($store_post);
			}
			$goods['store_id'] = $store_result;
			
			$url_result = $this->goods_url_model->add($url);
			
			if($url_result){
				$goods['url_id'] = $url_result;
				$result = $this->goods_model->add($goods);
				if($result){
					$this->success("添加成功！");
				}else{
					$this->error("添加失败，检查goods内容");
				}
			}else{
				$this->error("添加失败，检查url内容");
			}
		}
	}
	
	function iadd(){
		$categories = $this->category_model->order(array("listorder"=>"asc"))->select();
		$ctg_id = intval(I("get.category"));
		$this->_getTermTree();
		$category=$this->category_model->where("ctg_id=$ctg_id")->find();
		$this->assign("author","1");
		$this->assign("category",$category);
		$this->assign("categories",$categories);
		$this->display();
	}
	function iadd_post(){
		if (IS_POST) {
			$post = $_POST;
			
			import('Querylist.QueryList','simplewind/Lib/Extend/');
			$reg = array(
				"title" => array(".item-title a","text"),
				"img" => array("#J_test","src"),
				"price" => array(".price strong","text"),
				"salenum" => array(".info-sell a","text"),
				"roll" => array(".info-wrap strong","text"),
				"mall" => array(".shop-log-wrap p","text"),
				"mall_id" => array(".tmall-tag","text"),
				"mall_rate" => array(".tb-shop-rate","text"),
				"baoyou" => array(".price-baoyou","text"),
			);
			//块选择器
			$rang = "";
			
			$post_author=get_current_admin_id();
			
			echo "<hr>";
			foreach($post as $key=>$val){
				
				$val['goodurl'] = array_filter($val['goodurl']);
				
				$good['ctg_id'] = $val['ctg_id'];
				$good['sex_id'] = $val['sexid'];
				$val['goodurl'] = array_filter($val['goodurl']);
				for($i=0;$i<count($val['goodurl']);$i++){
					$hj = \QueryList::Query($val['goodurl'][$i],$reg,$rang,'','UTF-8');
					$result[] = $hj->jsonArr;
				}
				$good['meal_name'] = $val['mealname'];
				$good['meal_price'] = $val['mealprice'];
				if(strlen($val['mealurl']) > 50){
					$val['mealurl'] = xw_shorturl($val['mealurl']);
					if(is_array($val['mealurl'])){
						echo $val['mealurl']['err_msg'];
					}
				}
				$url['url_meal'] = $val['mealurl'];
				
				$good['post_title'] = $val['mealname'];
				if(empty($val['keywords'])){
					foreach($result as $rkey=>$rval){
						$val['keywords'] = xw_get_keywords($rval[0]['title']);
						$good['post_keywords'] .= implode(',',$val['keywords']);
					}
				}else{
					$good['post_keywords'] = $val['keywords'];
				}
				$good['post_description'] = $val['description'];
				$good['post_content'] = $val['content'];
				$good['post_date'] = date('Y-m-d H:i:s',time());
				$good['post_modified'] = date('Y-m-d H:i:s',time());
				$good['post_status'] = '0';
				$good['goods_count'] = count($result);
				$good['istop'] = 0;
				$good['recommended'] = 0;
				
				$good['post_author'] = $post_author;
				for($a=0;$a<count($result);$a++){
					$url['url'] .= $val['goodurl'][$a].'|**|';
					$good['goods_name'] .= $result[$a][0]['title'].'|**|';
					$good['img'] .= $result[$a][0]['img'].'|**|';
					$good['price'][] = $result[$a][0]['price'];
					$z_price += $result[$a][0]['price'];
					$good['salenum'][] = $result[$a][0]['salenum'];
					$good['score'][] = $result[$a][0]['roll'];
					$good['kuaidi'][] = empty($result[$a][0]['baoyou'])?'0':'1';
				}
				$store['sto_name'] = $result[0][0]['mall'];
				$store['sto_score'] = preg_replace("/\s|　/","",$result[0][0]['mall_rate']);
				
				$good['mid'] = empty($result[0][0]['mall_id'])?'1':'2';
				
				$good['save_price'] = $z_price - $good['meal_price'];
				$good_price = json_encode($good['price']);
				$good['price'] = $good_price;
				$good['salenum'] = json_encode($good['salenum']);
				$good['score'] = json_encode($good['score']);
				$good['kuaidi'] = json_encode($good['kuaidi']);
				
				if(!empty($good['goods_name'])){
					$where_store['sto_name'] = $store['sto_name'];
					$store_find = $this->store_model->where($where_store)->field('sto_id')->find();
					$store_result = $store_find['sto_id'];
					if(empty($store_result)){
						$store['sto_status'] = 1;
						$store_result = $this->store_model->add($store);
					}
					$good['store_id'] = $store_result;
					
					$addurl = $this->goods_url_model->add($url);
					if($addurl){
						$good['url_id'] = $addurl;
						$add = $this->goods_model->add($good);
						if($add){
							echo "添加成功<br>";
						}else{
							echo "在good添加失败<br>";
						}
					}else{
						echo "在url添加失败<br>";
					}
				}
				dump($good);
				unset($val);
				unset($post[$key]);
				unset($good);
				unset($result);
				unset($url);
				$this->display();
			}
		}
	}
	
	public function edit(){
		$id = intval(I("get.good_id"));
		
		$good_categoryArray = $this->goods_model->where(array("good_id"=>$id,"g_status"=>1))->getField("ctg_id",true);
		$this->_getTermTree($good_categoryArray);
		$terms=$this->category_model->select();
		
		$join = "".C('DB_PREFIX').'goods_url as b on a.url_id =b.url_id';
		$join2= "".C('DB_PREFIX').'users as c on a.post_author = c.id';
		$join3= "".C('DB_PREFIX').'store as d on a.store_id = d.sto_id';
		$good=$this->goods_model->alias("a")->join($join)->join($join2)->join($join3)->where("good_id=$id")->find();
		$this->assign("good",$good);
		$this->assign("smeta",json_decode($good['smeta'],true));
		$this->assign("terms",$terms);
		$this->assign("term",$good_categoryArray);
		$this->display();
	}
	
	public function edit_post(){
		if (IS_POST) {
			
			if(empty($_POST['good']['ctg_id'])){
				$this->error("请选择一个分类栏目！");
			}
			
			$url=$_POST['url'];
			
			/*链接处理*/
			foreach($url['good'] as $u_key=>$u_val){
				if(strlen($u_val) > 50){
					$url['good'][$u_key] = xw_shorturl($u_val);
					if(is_array($url['good'][$u_key])){
						$this->error($url['good'][$u_key]['err_msg']);
					}
				}
				$url_goods_new .= $url['good'][$u_key].'|**|';
			}
			$url_new['url'] = $url_goods_new;
			
			if(strlen($url['meal']) > 50){
				$url['meal'] = xw_shorturl($url['meal']);
				if(is_array($url['meal'])){
					$this->error($url['meal']['err_msg']);
				}
			}
			
			$url_new['url_meal'] = $url['meal'];
			
			/*good处理*/
			$good=I('post.good');
			
			foreach($good as $gkey=>$gval){
				if(is_array($gval)){
					$good[$gkey] = array_filter($gval);
				}else{
					$good[$gkey] = trim($gval);
				}
			}
			$good['post_content'] = htmlspecialchars_decode($good['post_content']);
			
			foreach ($good['goods_name'] as $key=>$value){
				$name .= trim($value).'|**|';
				$price[] = trim($good['price'][$key]);
				$kuaidi[] = trim($good['kuaidi'][$key]);
				$salenum[] = trim($good['salenum'][$key]);
			}
			$good['goods_name'] = $name;
			$good['price'] = json_encode($price);
			$good['kuaidi'] = json_encode($kuaidi);
			$good['salenum'] = json_encode($salenum);
			
			if(empty($good['post_modified'])){
				$good['post_modified']=date("Y-m-d H:i",time());
			}
			
			$good['post_author']=get_current_admin_id();
			
			foreach($good['img'] as $i_val){
				$good_img .= trim($i_val)."|**|";
			}
			$good['img'] = $good_img;
			
			/*smeta处理*/
			if(!empty($_POST['photos_alt']) && !empty($_POST['photos_url'])){
				foreach ($_POST['photos_url'] as $key=>$url){
					$photourl=sp_asset_relative_url($url);
					$_POST['smeta']['photo'][]=array("url"=>$photourl,"alt"=>$_POST['photos_alt'][$key]);
				}
			}
			$_POST['smeta']['thumb'] = sp_asset_relative_url($_POST['smeta']['thumb']);
			if(!empty($_POST['smeta']['outlink'])){
				$_POST['smeta']['outlink'] = base64_encode($_POST['smeta']['outlink']);
			}
			$good['smeta']=json_encode($_POST['smeta']);
			
			
			$url_id = $url['url_id'];
			$good_id = $good['good_id'];
			$url_result = $this->goods_url_model->where("url_id=$url_id")->save($url_new);
			
			if($url_result!==false){
				$result = $this->goods_model->where("good_id=$good_id")->save($good);
				if($result){
					$this->success("更改成功！");
				}else{
					$this->error("更改失败，检查goods内容");
				}
			}else{
				$this->error("更改失败，检查url内容");
			}
			
		}
	}
	
	//排序
	public function listorders() {
		$status = parent::_listorders($this->goods_model);
		if ($status) {
			$this->success("排序更新成功！");
		} else {
			$this->error("排序更新失败！");
		}
	}
	
	private  function _lists($status=1){
		$ctg_id = 0;
		if(!empty($_REQUEST["category"])){
			$ctg_id=intval($_REQUEST["category"]);
			$category=$this->category_model->where("ctg_id=$ctg_id")->find();
			$this->assign("category",$category);
			$_GET['category']=$ctg_id;
		}
		
		$where_ands=empty($ctg_id)?array("b.g_status=$status"):array("b.ctg_id = $ctg_id and b.g_status=$status");
		
		$fields=array(
				'start_time'=> array("field"=>"post_date","operator"=>">"),
				'end_time'  => array("field"=>"post_date","operator"=>"<"),
				'keyword'  => array("field"=>"goods_name","operator"=>"like"),
		);
		if(IS_POST){
			
			foreach ($fields as $param =>$val){
				if (isset($_POST[$param]) && !empty($_POST[$param])) {
					$operator=$val['operator'];
					$field   =$val['field'];
					$get=$_POST[$param];
					$_GET[$param]=$get;
					if($operator=="like"){
						$get="%$get%";
					}
					array_push($where_ands, "$field $operator '$get'");
				}
			}
		}else{
			foreach ($fields as $param =>$val){
				if (isset($_GET[$param]) && !empty($_GET[$param])) {
					$operator=$val['operator'];
					$field   =$val['field'];
					$get=$_GET[$param];
					if($operator=="like"){
						$get="%$get%";
					}
					array_push($where_ands, "$field $operator '$get'");
				}
			}
		}
		
		$where= join(" and ", $where_ands);
			
			
		$count=$this->goods_url_model
		->alias("a")
		->join(C ( 'DB_PREFIX' )."goods b ON a.url_id = b.url_id")
		->where($where)
		->count();
			
		$page = $this->page($count, 20);
		
			
		$posts=$this->goods_url_model
		->alias("a")
		->join(C ( 'DB_PREFIX' )."goods b ON a.url_id = b.url_id")
		->where($where)
		->limit($page->firstRow . ',' . $page->listRows)
		->order("b.listorder ASC,b.post_modified DESC")->select();
		$users_obj = M("Users");
		$users_data=$users_obj->field("id,user_login")->where("user_status=1")->select();
		$users=array();
		foreach ($users_data as $u){
			$users[$u['id']]=$u;
		}
    	$category = $this->category_model->order(array("ctg_id = $ctg_id"))->getField("ctg_id,name",true);
		
		/*信息处理*/
		foreach($posts as $pkey => $pval){
			$posts[$pkey]['goods_name'] = explode("|**|",$pval['goods_name']);
			array_pop($posts[$pkey]['goods_name']);
			$posts[$pkey]['url'] = explode("|**|",$pval['url']);
			array_pop($posts[$pkey]['url']);
			$posts[$pkey]['img'] = explode("|**|",$pval['img']);
			array_pop($posts[$pkey]['img']);
			
			$posts[$pkey]['price'] = json_decode($pval['price']);
			$posts[$pkey]['kuaidi'] = json_decode($pval['kuaidi']);
		}
		/*处理结束*/
		
		
		$this->assign("users",$users);
    	$this->assign("category",$category);
		$this->assign("Page", $page->show('Admin'));
		$this->assign("current_page",$page->GetCurrentPage());
		unset($_GET[C('VAR_URL_PARAMS')]);
		$this->assign("formget",$_GET);
		$this->assign("posts",$posts);
	}
	
	private function _getTree(){
		$ctg_id=empty($_REQUEST['category'])?0:intval($_REQUEST['category']);
		$result = $this->category_model->order(array("listorder"=>"asc"))->select();
		
		$tree = new \Tree();
		$tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
		$tree->nbsp = '&nbsp;&nbsp;&nbsp;';
		foreach ($result as $r) {
			$r['str_manage'] = '<a href="' . U("AdminGoods/add", array("parent" => $r['ctg_id'])) . '">添加子类</a> | <a href="' . U("AdminGoods/edit", array("id" => $r['ctg_id'])) . '">修改</a> | <a class="J_ajax_del" href="' . U("AdminGoods/delete", array("id" => $r['ctg_id'])) . '">删除</a> ';
			$r['visit'] = "<a href='#'>访问</a>";
			$r['taxonomys'] = $this->taxonomys[$r['taxonomy']];
			$r['id']=$r['ctg_id'];
			$r['parentid']=$r['parent'];
			$r['selected']=$ctg_id==$r['ctg_id']?"selected":"";
			$array[] = $r;
		}
		
		$tree->init($array);
		$str="<option value='\$id' \$selected>\$spacer\$name</option>";
		$taxonomys = $tree->get_tree(0, $str);
		$this->assign("taxonomys", $taxonomys);
	}
	
	function _getTermTree($term=array()){
		$result = $this->category_model->order(array("listorder"=>"asc"))->select();
		
		$tree = new \Tree();
		$tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
		$tree->nbsp = '&nbsp;&nbsp;&nbsp;';
		foreach ($result as $r) {
			$r['str_manage'] = '<a href="' . U("AdminTerm/add", array("parent" => $r['ctg_id'])) . '">添加子类</a> | <a href="' . U("AdminTerm/edit", array("id" => $r['ctg_id'])) . '">修改</a> | <a class="J_ajax_del" href="' . U("AdminTerm/delete", array("id" => $r['ctg_id'])) . '">删除</a> ';
			$r['visit'] = "<a href='#'>访问</a>";
			$r['taxonomys'] = $this->taxonomys[$r['taxonomy']];
			$r['id']=$r['ctg_id'];
			$r['parentid']=$r['parent'];
			$r['selected']=in_array($r['ctg_id'], $term)?"selected":"";
			$r['checked'] =in_array($r['ctg_id'], $term)?"checked":"";
			$array[] = $r;
		}
		
		$tree->init($array);
		$str="<option value='\$id' \$selected>\$spacer\$name</option>";
		$taxonomys = $tree->get_tree(0, $str);
		$this->assign("taxonomys", $taxonomys);
	}
	
	function delete(){
		if(isset($_GET['good_id'])){
			$good_id = intval(I("get.good_id"));
			$data['g_status']=0;
			if ($this->goods_model->where("good_id=$good_id")->save($data)) {
				$this->success("删除成功！");
			} else {
				$this->error("删除失败！");
			}
		}
		if(isset($_POST['good_ids'])){
			$good_ids=join(",",$_POST['good_ids']);
			$data['g_status']=0;
			if ($this->goods_model->where("good_id in ($good_ids)")->save($data)) {
				$this->success("删除成功！");
			} else {
				$this->error("删除失败！");
			}
		}
	}
	
	function check(){
		if(isset($_POST['good_ids']) && $_GET["check"]){
			$data["post_status"]=1;
			
			$good_ids=join(",",$_POST['good_ids']);
			if ( $this->goods_model->where("good_id in ($good_ids)")->save($data)!==false) {
				$this->success("审核成功！");
			} else {
				$this->error("审核失败！");
			}
		}
		if(isset($_POST['good_ids']) && $_GET["uncheck"]){
			
			$data["post_status"]=0;
			$good_ids=join(",",$_POST['good_ids']);
			if ( $this->goods_model->where("good_id in ($good_ids)")->save($data)) {
				$this->success("取消审核成功！");
			} else {
				$this->error("取消审核失败！");
			}
		}
	}
	
	function top(){
		if(isset($_POST['good_ids']) && $_GET["top"]){
			$data["istop"]=1;
				
			$good_ids=join(",",$_POST['good_ids']);
			if ( $this->goods_model->where("good_id in ($good_ids)")->save($data)!==false) {
				$this->success("置顶成功！");
			} else {
				$this->error("置顶失败！");
			}
		}
		if(isset($_POST['good_ids']) && $_GET["untop"]){
				
			$data["istop"]=0;
			$good_ids=join(",",$_POST['good_ids']);
			if ( $this->goods_model->where("good_id in ($good_ids)")->save($data)) {
				$this->success("取消置顶成功！");
			} else {
				$this->error("取消置顶失败！");
			}
		}
	}
	
	function recommend(){
		if(isset($_POST['good_ids']) && $_GET["recommend"]){
			$data["recommended"]=1;
	
			$good_ids=join(",",$_POST['good_ids']);
			if ( $this->goods_model->where("good_id in ($good_ids)")->save($data)!==false) {
				$this->success("推荐成功！");
			} else {
				$this->error("推荐失败！");
			}
		}
		if(isset($_POST['good_ids']) && $_GET["unrecommend"]){
	
			$data["recommended"]=0;
			$good_ids=join(",",$_POST['good_ids']);
			if ( $this->goods_model->where("good_id in ($good_ids)")->save($data)) {
				$this->success("取消推荐成功！");
			} else {
				$this->error("取消推荐失败！");
			}
		}
	}
	
	function move(){
		if(IS_POST){
			if(isset($_GET['good_ids']) && isset($_POST['ctg_id'])){
				$good_ids=$_GET['good_ids'];
				if ( $this->goods_model->where("good_id in ($good_ids)")->save($_POST) !== false) {
					$this->success("移动成功！");
				} else {
					$this->error("移动失败！");
				}
			}
		}else{
			$parentid = intval(I("get.parent"));
			
			$tree = new \Tree();
			$tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
			$tree->nbsp = '&nbsp;&nbsp;&nbsp;';
			$categories = $this->category_model->order(array("path"=>"asc"))->select();
			$new_terms=array();
			foreach ($categories as $r) {
				$r['id']=$r['ctg_id'];
				$r['parentid']=$r['parent'];
				$new_terms[] = $r;
			}
			$tree->init($new_terms);
			$tree_tpl="<option value='\$id'>\$spacer\$name</option>";
			$tree=$tree->get_tree(0,$tree_tpl);
			 
			$this->assign("categories_tree",$tree);
			$this->display();
		}
	}
	
	function recyclebin(){
		$this->_lists(0);
		$this->_getTree();
		$this->display();
	}
	
	function clean(){
		if(isset($_POST['good_ids'])){
			$good_ids = implode(",", $_POST['good_ids']);
			$url_ids= implode(",", array_keys($_POST['good_ids']));
			
			$goods_status=$this->goods_model->where("good_id in ($good_ids)")->delete();
			if($goods_status!==false){
				/*经测试无法使用
				foreach($url_ids as $val_id){
					$val_id=intval($val_id);
					$count=$this->$goods_model->where(array("url_id"=>$val_id))->count();
					if(empty($count)){
						$url_status=$this->goods_url_model->where("url_id = $val_id")->delete();
					}
				}*/
				$url_status=$this->goods_url_model->where("url_id in ($url_ids)")->delete();
				if($url_status!==false){
					$this->success("删除商品、url成功");
				}else{
					$this->error("删除url失败|url_id:$url_ids");
				}
			}else{
				$this->error("删除商品失败|good_id:$good_ids");
			}
		}else{
			if(isset($_GET['good_id'])){
				$good_id = intval(I("get.good_id"));
				$url_id = intval(I("get.url_id"));
				$status=$this->goods_model->where("good_id = $good_id")->delete();
				if($status!==false){
					$count=$this->goods_model->where(array("url_id"=>$url_id))->count();
					if(empty($count)){
						$status=$this->goods_url_model->where("url_id=$url_id")->delete();
					}
				}
				if ($status!==false) {
					$this->success("删除成功！");
				} else {
					$this->error("删除失败！");
				}
			}
		}
	}
	
	function restore(){
		if(isset($_GET['good_id'])){
			$good_id = intval(I("get.good_id"));
			$data=array("good_id"=>$good_id,"g_status"=>"1");
			if ($this->goods_model->save($data)) {
				$this->success("还原成功！");
			} else {
				$this->error("还原失败！");
			}
		}
	}
	
}