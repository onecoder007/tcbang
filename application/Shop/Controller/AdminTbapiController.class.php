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
class AdminTbapiController extends AdminbaseController {
	
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
		$this->display();
	}
	
	function apiadd(){
		$goodsController = A('AdminGoods');
		$goodsController->_getTermTree();
		$this->display();
	}
	
	function api_post(){
		header("Content-Type: text/html; charset=UTF-8");
		
		import('taobaoSdk.TopSdk','simplewind/Lib/Extend/','.php');
		//Vendor('taobao-sdk.TopSdk');
		$c = new \TopClient;
		$c->appkey = '23250652';
		$c->secretKey = '0d3bc76d37f37439864035fee70450f8';
		$c->format = "json";
		
		$patt = '/(?<=id=)\d+/';
		$item_post = $_POST;
		
		$goodUrlArray = $_POST['goodUrl'];
		
		foreach($goodUrlArray as $itkey=>$itval){
			$goodUrlArray[$itkey] = array_filter($itval);
			if($itval[0]==""){
				unset($item_post['ctg_id'][$itkey]);
				unset($item_post['sexid'][$itkey]);
				unset($item_post['meal_price'][$itkey]);
				unset($item_post['url_meal'][$itkey]);
				unset($item_post['goodUrl'][$itkey]);
				unset($goodUrlArray[$itkey]);
			}
		}
		unset($item_post['meal_name']);
		unset($item_post['keywords']);
		unset($item_post['description']);
		unset($item_post['content']);
		
		//dump($item_post);
		//dump($goodUrlArray);
		
		//得出商品id数组
		foreach($goodUrlArray as $gkey=>$gval){
			$gcount = count($gval);
			$m_g_count[$gkey] = $gcount;
			for($gi=0;$gi<$gcount;$gi++){
				$item = $gval[$gi];
				preg_match($patt,$item,$item_id_match);
				$item_Array[$gkey]['url_id'] .= $item_id_match[0]."|**|";
				$item_id_Array_all[] = $item_id_match[0];
			}
			$item_Array[$gkey]['ctg_id'] = $item_post['ctg_id'][$gkey];
			$item_Array[$gkey]['sex_id'] = $item_post['sexid'][$gkey];
			//$item_Array[$gkey]['meal_name'] = $item_post['meal_name'][$gkey];
			$item_Array[$gkey]['meal_price'] = $item_post['meal_price'][$gkey];
			$item_Array[$gkey]['url_meal'] = $item_post['url_meal'][$gkey];
			//$item_Array[$gkey]['keywords'] = $item_post['keywords'][$gkey];
			$item_Array[$gkey]['description'] = $item_post['description'][$gkey];
			$item_Array[$gkey]['content'] = $item_post['content'][$gkey];
			
			$item_Array[$gkey]['goods_count'] = $gcount;
			$item_Array[$gkey]['recommended'] = 0;
			$item_Array[$gkey]['post_date'] =  date("Y-m-d H:i:s",time());
			$item_Array[$gkey]['post_modified'] = date("Y-m-d H:i:s",time());
			$item_Array[$gkey]['post_status'] = 0;
			$item_Array[$gkey]['g_status'] = 1;
			
		}
		
		$item_id_all = join(",",$item_id_Array_all);
		
		//根据数组得出商品
		$req = new \TbkItemsDetailGetRequest;
		$req->setFields("num_iid,seller_id,nick,title,volume,pic_url,item_url");
		$req->setNumIids($item_id_all);
		$resp = $c->execute($req);
		$ab = json_decode( json_encode( $resp),true);
		$result_item_Array = $ab['tbk_items']['tbk_item'];
		
		//根据数组得出商品简版 -- 得出商品所在地和商品类型
		$req3 = new \TbkItemInfoGetRequest;
		$req3->setFields("num_iid,user_type,provcity");
		$req3->setNumIids($item_id_all);
		$resp3 = $c->execute($req3);
		$ab3 = json_decode( json_encode( $resp3),true);
		$result_item_simp_Array = $ab3['results']['n_tbk_item'];
		
/*店铺信息获取api 【封禁】
		//根据商品信息得出店铺数据
		foreach($result_item_Array as $tbkval){
			$store_nick_Array_all[] = $tbkval['nick'];
		}
		$store_nick_all = join(",",$store_nick_Array_all);
		
		$req2 = new \TbkShopsDetailGetRequest;
		$req2->setFields("click_url,user_id,seller_nick,shop_title,pic_url");
		$req2->setSellerNicks($store_nick_all);
		$req2->setIsMobile("false");
		$resp2 = $c->execute($req2);
		$ab2 = json_decode( json_encode( $resp2),true);
		$result_store_Array = $ab2['tbk_shops']['tbk_shop'];
*/	
		
		//三数据合并
		foreach($result_item_Array as $rkey=>$rval){
			$item_simp_value = array_search_multi($result_item_simp_Array,'num_iid',$rval['num_iid']);
			$result_item_Array[$rkey] = array_merge($result_item_Array[$rkey],$item_simp_value);
			
			//封禁店铺信息查询--原因：店铺获取有限制（10）、批量时会为空
			//$store_value = array_search_multi($result_store_Array,'user_id',$rval['seller_id']);
			//$result_item_Array[$rkey]['store'] = $store_value;
			
		}
		
		dump($result_item_Array);
		
		foreach($item_Array as $bkey=>$bval){
			$search_item_id = explode("|**|",$bval['url_id']);
			array_pop($search_item_id);
			
			$url_id['url'] = $bval['url_id'];
			$url_id['url_meal'] = $bval['url_meal'];
			$item_Array[$bkey]['url'] = $url_id;//判断数组
			//$item_Array[$bkey]['url_id'] = json_encode($url_id);
			
			//定义数组，否则合并的时候会 空
			$item_Array[$bkey]['post_keywords'] = array();
			
			for($si=0;$si<count($search_item_id);$si++){
				$item_value = array_search_multi($result_item_Array,'num_iid',$search_item_id[$si]);
				if($item_value){
					$item_Array[$bkey]['goods_name'] .= $item_value['title']."|**|";
					$item_Array[$bkey]['img'] .= $item_value['pic_url']."|**|";
					$item_Array[$bkey]['mid'] = $item_value['user_type']+1;
					$store_id['seller_id'] = $item_value['seller_id'];
					$store_id['sto_name'] = $item_value['nick'];
					$price_Array[] = $item_value['discount_price'];
					$salenum_Array[] = $item_value['volume'];
					$val_keywords = xw_get_keywords($item_value['title']);
					//$item_Array[$bkey]['post_keywords'] = array();
					$item_Array[$bkey]['post_keywords'] = array_merge($item_Array[$bkey]['post_keywords'],$val_keywords);
					$price_all += $item_value['discount_price'];
				}
			}
			
			$item_Array[$bkey]['post_author'] = get_current_admin_id();
			$item_Array[$bkey]['comment_status'] = 0;
			$item_Array[$bkey]['meal_name'] = $item_Array[$bkey]['post_keywords'][0].$item_Array[$bkey]['post_keywords'][1].$item_Array[$bkey]['post_keywords'][2]."套餐";
			$item_Array[$bkey]['post_keywords'] = join(",",array_unique($item_Array[$bkey]['post_keywords']));
			$item_Array[$bkey]['price'] = json_encode($price_Array);
			$item_Array[$bkey]['salenum'] = json_encode($salenum_Array);
			$item_Array[$bkey]['store'] = $store_id;//判断数组
			//$item_Array[$bkey]['store_id'] = json_encode($store_id);
			$item_Array[$bkey]['save_price'] = $price_all-$item_Array[$bkey]['meal_price'];
			
			unset($price_all);
			unset($price_Array);
			unset($salenum_Array);
			
		}
		echo "<hr>item_Array";
		dump($item_Array);
		//删除空数组
		foreach($item_Array as $ekey=>$eval){
			if(!is_int($eval['mid'])){
				$unknow_meal[] = $item_Array[$ekey];
				unset($item_Array[$ekey]);
			}
		}
		
		echo "<hr>没加入的店铺：";
		dump($unknow_meal);
		
		echo "item_Array 结束<hr>";
		dump($item_Array);
		
		//录入数据库
		
		//方法1：循环录入
		foreach($item_Array as $endkey=>$endval){
			
			$store_where['sto_name'] = $endval['store']['sto_name'];
			$store_find = $this->store_model->where($store_where)->field('sto_id')->find();
			$store_result = $store_find['sto_id'];
			if(empty($store_result)){
				
				$store_result = $this->store_model->add($endval['store']);
			}
			$item_Array[$endkey]['store_id'] = $store_result;
			
			$url_result = $this->goods_url_model->add($endval['url']);
			
			if($url_result){
				$item_Array[$endkey]['url_id'] = $url_result;
				$result = $this->goods_model->add($item_Array[$endkey]);
				if($result){
					echo "添加成功<br>";
					//$this->success("添加成功！");
				}else{
					echo "添加失败，检查goods内容<br>";
					//$this->error("添加失败，检查goods内容");
				}
			}else{
				echo "添加失败，检查url内容<br>";
				//$this->error("添加失败，检查url内容");
			}
			
		}
		
/*
		//方法2：整体录入--只录入goods表 -- 数组必须是从0开始
		$goods_model = M("Goods");
		$goods_add = $goods_model->addAll($item_Array);
		dump($goods_add);	
		if($goods_add){
			echo "添加成功";
		}
*/	
	
	}

	
}