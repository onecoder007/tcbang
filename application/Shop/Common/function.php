<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Tuolaji <479923197@qq.com>
// +----------------------------------------------------------------------
/**
 * 查询文章列表，不做分页
 * @param string $tag  查询标签，以字符串方式传入,例："cid:1,2;field:post_title,post_content;limit:0,8;order:post_date desc,listorder desc;where:id>0;"<br>
 *  ids:调用指定id的一个或多个数据,如 1,2,3<br>
 * 	cid:数据所在分类,可调出一个或多个分类数据,如 1,2,3 默认值为全部,在当前分类为:'.$cid.'<br>
 * 	field:调用post指定字段,如(id,post_title...) 默认全部<br>
 * 	limit:数据条数,默认值为10,可以指定从第几条开始,如3,8(表示共调用8条,从第3条开始)<br>
 * 	order:排序方式，如：post_date desc<br>
 *	where:查询条件，字符串形式，和sql语句一样
 * @param array $where 查询条件，（暂只支持数组），格式和thinkphp where方法一样；
 */
function sp_sql_goods($tag,$where=array()){
	if(!is_array($where)){
		$where=array();
	}
	
	$tag=sp_param_lable($tag);
	$field = !empty($tag['field']) ? $tag['field'] : '*';
	$limit = !empty($tag['limit']) ? $tag['limit'] : '';
	$order = !empty($tag['order']) ? $tag['order'] : 'post_date';


	//根据参数生成查询条件
	$where['status'] = array('eq',1);
	$where['post_status'] = array('eq',1);

	if (isset($tag['cid'])) {
		$where['ctg_id'] = array('in',$tag['cid']);
	}
	
	if (isset($tag['ids'])) {
		$where['id'] = array('in',$tag['ids']);
	}
	

	$join = "".C('DB_PREFIX').'goods_url as b on a.url_id =b.url_id';
	$join2= "".C('DB_PREFIX').'users as c on a.post_author = c.id';
	$gs= M("Goods");

	$goods=$gs->alias("a")->join($join)->join($join2)->field($field)->where($where)->order($order)->limit($limit)->select();
	return $goods;
}
/*文章*/
function sp_sql_posts($tag,$where=array()){
	if(!is_array($where)){
		$where=array();
	}
	
	$tag=sp_param_lable($tag);
	$field = !empty($tag['field']) ? $tag['field'] : '*';
	$limit = !empty($tag['limit']) ? $tag['limit'] : '';
	$order = !empty($tag['order']) ? $tag['order'] : 'post_date';


	//根据参数生成查询条件
	$where['status'] = array('eq',1);
	$where['post_status'] = array('eq',1);

	if (isset($tag['cid'])) {
		$where['term_id'] = array('in',$tag['cid']);
	}
	
	if (isset($tag['ids'])) {
		$where['object_id'] = array('in',$tag['ids']);
	}
	


	$join = "".C('DB_PREFIX').'posts as b on a.object_id =b.id';
	$join2= "".C('DB_PREFIX').'users as c on b.post_author = c.id';
	$rs= M("TermRelationships");

	$posts=$rs->alias("a")->join($join)->join($join2)->field($field)->where($where)->order($order)->limit($limit)->select();
	return $posts;
}

/**
 * 查询商品列表，支持分页
 * @param string $tag  查询标签，以字符串方式传入,例："cid:1,2;field:post_title,post_content;limit:0,8;order:post_date desc,listorder desc;where:id>0;"<br>
 *  ids:调用指定id的一个或多个数据,如 1,2,3<br>
 * 	cid:数据所在分类,可调出一个或多个分类数据,如 1,2,3 默认值为全部,在当前分类为:'.$cid.'<br>
 * 	field:调用post指定字段,如(id,post_title...) 默认全部<br>
 * 	limit:数据条数,默认值为10,可以指定从第几条开始,如3,8(表示共调用8条,从第3条开始)<br>
 * 	order:排序方式，如：post_date desc<br>
 *	where:查询条件，字符串形式，和sql语句一样
 * @param array $where 查询条件，（暂只支持数组），格式和thinkphp where方法一样；
 * @param int $pagesize 每页条数.
 * @param array $pagesetting 分页设置<br> 
 * 	参数形式：<br>
 * 	array(<br>
 * 		&nbsp;&nbsp;"listlong" => "9",<br>
 * 		&nbsp;&nbsp;"first" => "首页",<br>
 * 		&nbsp;&nbsp;"last" => "尾页",<br>
 * 		&nbsp;&nbsp;"prev" => "上一页",<br>
 * 		&nbsp;&nbsp;"next" => "下一页",<br>
 * 		&nbsp;&nbsp;"list" => "*",<br>
 * 		&nbsp;&nbsp;"disabledclass" => ""<br>
 * 	)
 * @param string $pagetpl 以字符串方式传入,例："{first}{prev}{liststart}{list}{listend}{next}{last}"
 * @return array 包括分页的文章列表<br>
 * array(<br>
 * 	&nbsp;&nbsp;"posts"=>"",//文章列表，array<br>
 * 	&nbsp;&nbsp;"page"=>""//分页html<br>
 * )
 */

function sp_goods($tag,$where=array(),$pagesize=20,$pagesetting=array(),$pagetpl='{first}{prev}{liststart}{list}{listend}{next}{last}'){
	$where=is_array($where)?$where:array();
	$tag=sp_param_lable($tag);
	$field = !empty($tag['field']) ? $tag['field'] : '*';
	$limit = !empty($tag['limit']) ? $tag['limit'] : '10';
	$order = !empty($tag['order']) ? $tag['order'] : 'post_modified desc';

	//根据参数生成查询条件
	$where['post_status'] = array('eq',1);
	$where['post_status'] = array('eq',1);

	if (isset($tag['cid'])) {
		$where['term_id'] = array('in',$tag['cid']);
	}

	if (isset($tag['ids'])) {
		$where['object_id'] = array('in',$tag['ids']);
	}
	
	if (isset($tag['where'])) {
		$where['_string'] = $tag['where'];
	}
	
	$rs= M("Goods");
	$totalsize=$rs->alias("a")->field($field)->where($where)->count();

	import('Page');
	if ($pagesize == 0) {
		$pagesize = 20;
	}
	$PageParam = C("VAR_PAGE");
	$page = new \Page($totalsize,$pagesize);
	$page->setLinkWraper("li");
	$page->__set("PageParam", $PageParam);
	$pagesetting=!empty($pagesetting)?$pagesetting: array("listlong" => "9", "first" => "首页", "last" => "尾页", "prev" => "上一页", "next" => "下一页", "list" => "*", "disabledclass" => "");
	$page->SetPager('default', $pagetpl,$pagesetting);
	$posts=$rs->alias("a")->join($join)->field($field)->where($where)->order($order)->limit($page->firstRow . ',' . $page->listRows)->select();

	$content['posts']=$posts;
	$content['page']=$page->show('default');
	return $content;
}

/**
 * 功能：根据分类文章分类ID 获取该分类下所有文章（包含子分类中文章），调用方式同sp_sql_posts
 * @author labulaka 2014-11-09 14:30:49
 * @param int $cid 文章分类ID.
 * @param string $tag  查询标签，以字符串方式传入,例："cid:1,2;field:post_title,post_content;limit:0,8;order:post_date desc,listorder desc;where:id>0;"<br>
 * 		cid:数据所在分类,可调出一个或多个分类数据,如 1,2,3 默认值为全部,在当前分类为:'.$cid.'<br>
 * 		field:调用post指定字段,如(id,post_title...) 默认全部
 * 		limit:数据条数,默认值为10,可以指定从第几条开始,如3,8(表示共调用8条,从第3条开始)
 * 		order:排序方式，如：post_date desc<br>
 * 		where:查询条件，字符串形式，和sql语句一样
 * @param array $where 查询条件，（暂只支持数组），格式和thinkphp where方法一样；
 */

function sp_sql_posts_bycatid($cid,$tag,$where=array()){
	$cid=intval($cid);
	$catIDS=array();
	$terms=M("Terms")->field("term_id")->where("status=1 and ( term_id=$cid OR path like '%-$cid-%' )")->order('term_id asc')->select();

	foreach($terms as $item){
		$catIDS[]=$item['term_id'];
	}
	if(!empty($catIDS)){
		$catIDS=implode(",", $catIDS);
		$catIDS="cid:$catIDS;";
	}else{
		$catIDS="";
	}
	$content= sp_sql_posts($catIDS.$tag,$where);
	return $content;

}

/**
 * 文章分页查询方法
 * @param string $tag  查询标签，以字符串方式传入,例："cid:1,2;field:post_title,post_content;limit:0,8;order:post_date desc,listorder desc;where:id>0;"<br>
 * 	ids:调用指定id的一个或多个数据,如 1,2,3<br>
 * 	cid:数据所在分类,可调出一个或多个分类数据,如 1,2,3 默认值为全部,在当前分类为:'.$cid.'<br>
 * 	field:调用post指定字段,如(id,post_title...) 默认全部<br>
 * 	limit:数据条数,默认值为10,可以指定从第几条开始,如3,8(表示共调用8条,从第3条开始)<br>
 * 	order:排序方式，如：post_date desc<br>
 *	where:查询条件，字符串形式，和sql语句一样
 * @param int $pagesize 每页条数.
 * @param string $pagetpl 以字符串方式传入,例："{first}{prev}{liststart}{list}{listend}{next}{last}"
 * @return array 带分页数据的文章列表
 
 */

function sp_sql_goods_paged($tag,$pagesize=20,$pagetpl='{first}{prev}{liststart}{list}{listend}{next}{last}'){
	$where=array();
	$tag=sp_param_lable($tag);
	$field = !empty($tag['field']) ? $tag['field'] : '*';
	$limit = !empty($tag['limit']) ? $tag['limit'] : '';
	$order = !empty($tag['order']) ? $tag['order'] : 'post_date';

	//根据参数生成查询条件
	$where['post_status'] = array('eq',1);

	if (isset($tag['cid'])) {
		$where['ctg_id'] = array('in',$tag['cid']);
	}

	if (isset($tag['ids'])) {
		$where['id'] = array('in',$tag['ids']);
	}
	
	if (isset($tag['where'])) {
		$where['_string'] = $tag['where'];
	}

	$join = "".C('DB_PREFIX').'goods_url as b on a.url_id =b.url_id';
	$join2= "".C('DB_PREFIX').'users as c on a.post_author = c.id';
	$gs= M("Goods");
	$totalsize=$gs->alias("a")->join($join)->join($join2)->field($field)->where($where)->count();
	
	import('Page');
	if ($pagesize == 0) {
		$pagesize = 20;
	}
	$PageParam = C("VAR_PAGE");
	$page = new \Page($totalsize,$pagesize);
	$page->setLinkWraper("li");
	$page->__set("PageParam", $PageParam);
	$page->SetPager('default', $pagetpl, array("listlong" => "9", "first" => "首页", "last" => "尾页", "prev" => "上一页", "next" => "下一页", "list" => "*", "disabledclass" => ""));
	$posts=$gs->alias("a")->join($join)->join($join2)->field($field)->where($where)->order($order)->limit($page->firstRow . ',' . $page->listRows)->select();

	$content['posts']=$posts;
	$content['page']=$page->show('default');
	$content['count']=$totalsize;
	return $content;
}


/**
 * 功能：根据关键字 搜索商品（包含子分类中商品），已经分页，调用方式同sp_sql_posts_paged<br>
 * @author hanuser 2015-08
 * @param string $keyword 关键字.
 * @param string $tag 查询标签，以字符串方式传入,例："field:post_title,post_content;limit:0,8;order:post_date desc,listorder desc;where:id>0;"<br>
 * 		field:调用post指定字段,如(id,post_title...) 默认全部<br>
 * 		limit:数据条数,默认值为10,可以指定从第几条开始,如3,8(表示共调用8条,从第3条开始)<br>
 * 		order:排序方式，如：post_date desc<br>
 * 		where:查询条件，字符串形式，和sql语句一样
 * @param int $pagesize 每页条数.
 * @param string $pagetpl 以字符串方式传入,例："{first}{prev}{liststart}{list}{listend}{next}{last}"
 */
function sp_sql_goods_paged_bykeyword($keyword,$tag,$pagesize=20,$pagetpl='{first}{prev}{liststart}{list}{listend}{next}{last}'){
	if(is_array($keyword)){
		$keywords_jia = $keyword[0].' '.$keyword[1];
	}else{
		$keywords_jia = $keyword;
	}
	$keywords_array = explode(" ",$keywords_jia);
	foreach($keywords_array as $key=>$val){
		if(!empty($val)){
			$keywords_all[] .= '%' . $val . '%';
		}
	}
	
	$where=array();
	$tag=sp_param_lable($tag);
	$field = !empty($tag['field']) ? $tag['field'] : '*';
	$limit = !empty($tag['limit']) ? $tag['limit'] : '';
	$order = !empty($tag['order']) ? $tag['order'] : 'post_date';


	//根据参数生成查询条件
	$where['post_status'] = array('eq',1);
	$where['goods_name'] = array('like',$keywords_all,'and');
	
	if (isset($tag['cid'])) {
		$where['ctg_id'] = array('in',$tag['cid']);
	}

	if (isset($tag['ids'])) {
		$where['good_id'] = array('in',$tag['ids']);
	}
	
	$join = "".C('DB_PREFIX').'users as c on a.post_author = c.id';
	$gs= M("Goods");
	$totalsize=$gs->alias("a")->join($join)->field($field)->where($where)->count();
	import('Page');
	if ($pagesize == 0) {
		$pagesize = 20;
	}
	$PageParam = C("VAR_PAGE");
	$page = new Page($totalsize,$pagesize);
	$page->setLinkWraper("li");
	$page->__set("PageParam", $PageParam);
	$page->SetPager('default', $pagetpl, array("listlong" => "9", "first" => "首页", "last" => "尾页", "prev" => "上一页", "next" => "下一页", "list" => "*", "disabledclass" => ""));
	$goods=$gs->alias("a")->join($join)->field($field)->where($where)->order($order)->limit($page->firstRow . ',' . $page->listRows)->select();
	$content['count']=$totalsize;
	$content['goods']=$goods;
	$content['page']=$page->show('default');
	return $content;
}

/**
 * 功能：根据分类文章分类ID 获取该分类下所有文章（包含子分类中文章），已经分页，调用方式同sp_sql_posts_paged<br>
 * @author labulaka 2014-11-09 14:30:49
 * @param int $tid 文章分类ID.
 * @param string $tag 查询标签，以字符串方式传入,例："field:post_title,post_content;limit:0,8;order:post_date desc,listorder desc;where:id>0;"<br>
 * 		field:调用post指定字段,如(id,post_title...) 默认全部<br>
 * 		limit:数据条数,默认值为10,可以指定从第几条开始,如3,8(表示共调用8条,从第3条开始)<br>
 * 		order:排序方式，如：post_date desc<br>
 *		where:查询条件，字符串形式，和sql语句一样
 * @param int $pagesize 每页条数.
 * @param string $pagetpl 以字符串方式传入,例："{first}{prev}{liststart}{list}{listend}{next}{last}"
 */

function sp_sql_posts_paged_bycatid($cid,$tag,$pagesize=20,$pagetpl='{first}{prev}{liststart}{list}{listend}{next}{last}'){
	$cid=intval($cid);
	$catIDS=array();
	$terms=M("Terms")->field("term_id")->where("status=1 and ( term_id=$cid OR path like '%-$cid-%' )")->order('term_id asc')->select();
	
	foreach($terms as $item){
		$catIDS[]=$item['term_id'];
	}
	if(!empty($catIDS)){
		$catIDS=implode(",", $catIDS);
		$catIDS="cid:$catIDS;";
	}else{
		$catIDS="";
	}
	$content= sp_sql_posts_paged($catIDS.$tag,$pagesize,$pagetpl);
	return $content;

}
/**
 * 获取指定id的文章
 * @param int $tid 分类表下的tid.
 * @param string $tag 查询标签，以字符串方式传入,例："field:post_title,post_content;"<br>
 *	field:调用post指定字段,如(id,post_title...) 默认全部<br>
 * @return array 返回指定id的文章
 */
function sp_sql_good($id,$tag){
	$where=array();
	$tag=sp_param_lable($tag);
	$field = !empty($tag['field']) ? $tag['field'] : '*';

	//根据参数生成查询条件
	$where['status'] = array('eq',1);
	$where['good_id'] = array('eq',$id);

	$join = "".C('DB_PREFIX').'goods_url as b on a.url_id =b.url_id';
	$join2= "".C('DB_PREFIX').'users as c on a.post_author = c.id';
	$goods_model= M("goods");

	$good=$goods_model->alias("a")->join($join)->join($join2)->field($field)->where($where)->find();
	return $good;
}

/**
 * 获取指定条件的页面列表
 * @param string $tag 查询标签，以字符串方式传入,例："ids:1,2;field:post_title,post_content;limit:0,8;order:post_date desc,listorder desc;where:id>0;"<br>
 * 	ids:调用指定id的一个或多个数据,如 1,2,3<br>
 * 	field:调用post指定字段,如(id,post_title...) 默认全部<br>
 * 	limit:数据条数,默认值为10,可以指定从第几条开始,如3,8(表示共调用8条,从第3条开始)<br>
 * 	order:排序方式，如：post_date desc<br>
 *	where:查询条件，字符串形式，和sql语句一样
 * @return array 返回符合条件的所有页面
 */
function sp_sql_pages($tag){
	$where=array();
	$tag=sp_param_lable($tag);
	$field = !empty($tag['field']) ? $tag['field'] : '*';
	$limit = !empty($tag['limit']) ? $tag['limit'] : '';
	$order = !empty($tag['order']) ? $tag['order'] : 'post_date';

	//根据参数生成查询条件
	$where['post_status'] = array('eq',1);
	$where['post_type'] = array('eq',2);
	
	if (isset($tag['ids'])) {
		$where['id'] = array('in',$tag['ids']);
	}
	
	if (isset($tag['where'])) {
		$where['_string'] = $tag['where'];
	}

	$posts_model= M("Posts");

	$pages=$posts_model->field($field)->where($where)->order($order)->limit($limit)->select();
	return $pages;
}

/**
 * 获取指定id的页面
 * @param int $id 页面的id
 * @return array 返回符合条件的页面
 */
function sp_sql_page($id){
	$where=array();
	$where['id'] = array('eq',$id);

	$rs= M("Posts");
	$post=$rs->where($where)->find();
	return $post;
}


/**
 * 返回指定分类
 * @param int $ctg_id 分类id
 * @return array 返回符合条件的分类
 */
function sp_get_term($ctg_id){
	
	$terms=F('all_categories');
	if(empty($terms)){
		$term_obj= M("Category");
		$terms=$term_obj->where("status=1")->select();
		$mterms=array();
		
		foreach ($terms as $t){
			$tid=$t['ctg_id'];
			$mterms["t$tid"]=$t;
		}
		
		F('all_categories',$mterms);
		return $mterms["t$ctg_id"];
	}else{
		return $terms["t$ctg_id"];
	}
}
/**
 * 返回指定分类下的子分类
 * @param int $term_id 分类id
 * @return array 返回指定分类下的子分类
 */
function sp_get_child_terms($term_id){

	$term_id=intval($term_id);
	$term_obj = M("Terms");
	$terms=$term_obj->where("status=1 and parent=$term_id")->order("listorder asc")->select();
	
	return $terms;
}
/**
 * 返回符合条件的所有分类
 * @param string $tag 查询标签，以字符串方式传入,例："ids:1,2;field:term_id,name,description,seo_title;limit:0,8;order:path asc,listorder desc;where:term_id>0;"<br>
 * 	ids:调用指定id的一个或多个数据,如 1,2,3
 * 	field:调用terms表里的指定字段,如(term_id,name...) 默认全部，用*代表全部
 * 	limit:数据条数,默认值为10,可以指定从第几条开始,如3,8(表示共调用8条,从第3条开始)
 * 	order:排序方式，如：path desc,listorder asc<br>
 * 	where:查询条件，字符串形式，和sql语句一样
 * 
 *  /xiaowei添加
 *  datatable:选择不同的分类数据表
 *
 * @return array 返回符合条件的所有分类
 * 
 */
function sp_get_terms($tag){
	
	$where=array();
	$tag=sp_param_lable($tag);
	$field = !empty($tag['field']) ? $tag['field'] : '*';
	$limit = !empty($tag['limit']) ? $tag['limit'] : '';
	$order = !empty($tag['order']) ? $tag['order'] : 'term_id';
	$datatable = !empty($tag['datatable']) ? $tag['datatable'] : 'Terms';
	
	//根据参数生成查询条件
	$where['status'] = array('eq',1);
	
	if (isset($tag['ids'])) {
		$where['term_id'] = array('in',$tag['ids']);
	}
	
	if (isset($tag['where'])) {
		$where['_string'] = $tag['where'];
	}
	
	$term_obj= M($datatable);
	$terms=$term_obj->field($field)->where($where)->order($order)->limit($limit)->select();
	return $terms;
}

/**
 * 获取Portal应用当前模板下的模板列表
 * @return array
 */
function sp_admin_get_tpl_file_list(){
	$template_path=C("SP_TMPL_PATH").C("SP_DEFAULT_THEME")."/Portal/";
	$files=sp_scan_dir($template_path."*");
	$tpl_files=array();
	foreach ($files as $f){
		if($f!="." || $f!=".."){
			if(is_file($template_path.$f)){
				$suffix=C("TMPL_TEMPLATE_SUFFIX");
				$result=preg_match("/$suffix$/", $f);
				if($result){
					$tpl=str_replace($suffix, "", $f);
					$tpl_files[$tpl]=$tpl;
				}
			}
		}
	}
	return $tpl_files;
}

/**
 * xw 自定义函数-1
 * 短网址API方法
 */
function xw_shorturl($url){
	$ch=curl_init();
	curl_setopt($ch,CURLOPT_URL,"http://dwz.cn/create.php");
	curl_setopt($ch,CURLOPT_POST,true);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
	
	$data=array('url'=>$url);
	curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
	$strRes=curl_exec($ch);
	curl_close($ch);
	$arrResponse=json_decode($strRes,true);
	
	if($arrResponse['status']!==0){
		iconv('UTF-8','GBK',$arrResponse['err_msg'])."\n";
		return $arrResponse['err_msg'];
	}
	return $arrResponse['tinyurl'];
	
}


/**
 *
 * xw 自定义函数-1
 * 对 数组的每个数值 前后增加字符串
 * 一般在array_map() 函数里调用此函数
 * 
*/
function xw_callback_array_valueadd(&$value,$key,$userdata){
	if(is_array($userdata)){
		$value = $userdata[0].$value.$userdata[1];
	}else{
		$value = $value.$userdata;
	}
}

/**
 *
 * xw 自定义函数-2
 * 获取店铺信息
 * 
*/
function xw_get_store($store_id){
	
	$store_model = M('Store');
	$where['sto_id'] = $store_id;
	$store = $store_model->where($where)->find();
	return $store;
}

/**
 *
 * xw 自定义函数-4
 * Discuz!在线关键字提取 函数
 * 
*/
function xw_get_keywords($title = '', $content = '', $encode = 'utf-8'){
	if($title == ''){
        return false;
    }
    $title = strip_tags($title);
    $content = strip_tags($content);
    if(strlen($content)>2400){ //在线分词服务有长度限制
        $content =  mb_substr($content, 0, 800, $encode);
    }
    /*$content = rawurlencode($content);不进行url转码*/
    $url = 'http://keyword.discuz.com/related_kw.html?title='.$title.'&content='.$content.'&ics='.$encode.'&ocs='.$encode;
    $xml_array=simplexml_load_file($url);                        //将XML中的数据,读取到数组对象中  
    $result = $xml_array->keyword->result;
    $data = array();
    foreach ($result->item as $key => $value) {
        array_push($data, (string)$value->kw);
    }
    if(count($data) > 0){
        return $data;
    }else{
        return false;
    }
}

/**
 *
 * xw 自定义函数-5
 * 多维数组搜索返回当前数组程序
 * 
*/
function array_search_multi($arr,$column,$findstring){ 
	foreach ($arr as $key=>$value){
		if($value[$column]==$findstring){
			return $value;
		}
	}
	return false; 
}

/**
 *
 * xw 自定义函数-6
 * 二维数组 根据价格分段 降序排列
 * 
*/
function xw_goods_sort($goods,$node=array(20,50,100,150,180)){
	$count = count($node);
	foreach($goods as $gkey=>$gval){
		
		for($i=0;$i<$count;$i++){
			if($gval['meal_price']<=$node[$i]){
				$name = "lt_".$node[$i];
				
				${$name}[] = $gval;
				break;
			}
		}

		if($gval['meal_price']>$node[$count-1]){
			$lt_else[] = $gval;
		}
	}
	
	/*先排序一个*/
	$num1 = 'lt_'.$node[0];
	usort($$num1, function($a1, $b1) {
		$ap1 = $a1['save_price'];
		$bp1 = $b1['save_price'];
		if ($ap1 == $bp1)
			return 0;
		return ($ap1 > $bp1) ? -1 : 1;
	});
	
	/*若为空，则赋值空数组*/
	if(empty($$num1)){
		$$num1 = array();
	}
	
	for($ii=1;$ii<$count;$ii++){
		$name2 = "lt_".$node[$ii];
		
		usort($$name2, function($a, $b) {
			$ap = $a['save_price'];
			$bp = $b['save_price'];
			if ($ap == $bp)
				return 0;
			return ($ap > $bp) ? -1 : 1;
		});
		
		if(!empty($$name2)){
			$$num1 = array_merge($$num1,$$name2);
		}
		
	}
	
	/*排序、融合最后一个else*/
	if(!empty($lt_else)){
		usort($lt_else, function($a1, $b1) {
			$ap1 = $a1['save_price'];
			$bp1 = $b1['save_price'];
			if ($ap1 == $bp1)
				return 0;
			return ($ap1 > $bp1) ? -1 : 1;
		});
	
		$$num1 = array_merge($$num1,$lt_else);
	}
	
	return $$num1;
}