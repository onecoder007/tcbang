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
class TestController extends HomeBaseController {
    //文章内页
    public function index() {
		
		$goods_model = M("Goods");
		$goods = $goods_model->limit(100,10)->order('post_date desc,listorder desc')->field("good_id,meal_price,meal_name")->select();
		dump($goods);
		echo "<hr>";
		$this->xw_goods_sort($goods);
		
	}
	
	public function save(){
		$goods_model = M("Goods");
		$goods = $goods_model->order('post_date desc,listorder desc')->field("good_id,meal_price,price,save_price")->select();
		
		foreach($goods as $key=>$val){
			$price = json_decode($val['price']);
			
			foreach($price as $pval){
				$z_price += $pval;
			}
			
			$goods[$key]['save_price'] = $z_price-$val['meal_price'];
			unset($z_price);
		}
		
		dump($goods);
		foreach($goods as $gval){
			$result[] = $goods_model->save($gval);
		}
		$count = count($result);
		echo $count;
		dump(count($result));
		
	}
	
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
	
	dump($lt_150);
	
	echo "<hr>";
	
	//先排序一个
	$num1 = 'lt_'.$node[0];
	usort($$num1, function($a1, $b1) {
		$ap1 = $a1['meal_price'];
		$bp1 = $b1['meal_price'];
		if ($ap1 == $bp1)
			return 0;
		return ($ap1 > $bp1) ? -1 : 1;
	});
	
	//若为空，则赋值空数组
	if(empty($$num1)){
		$$num1 = array();
	}
	
	for($ii=1;$ii<$count;$ii++){
		$name2 = "lt_".$node[$ii];
		
		usort($$name2, function($a, $b) {
			$ap = $a['meal_price'];
			$bp = $b['meal_price'];
			if ($ap == $bp)
				return 0;
			return ($ap > $bp) ? -1 : 1;
		});
		
		if(!empty($$name2)){
			$$num1 = array_merge($$num1,$$name2);
		}
		
	}
	
	//排序、融合最后一个else
	if(!empty($lt_else)){
		usort($lt_else, function($a1, $b1) {
			$ap1 = $a1['meal_price'];
			$bp1 = $b1['meal_price'];
			if ($ap1 == $bp1)
				return 0;
			return ($ap1 > $bp1) ? -1 : 1;
		});
	
		$$num1 = array_merge($$num1,$lt_else);
	}
	
	dump($$num1);
	
	
}
    	
}
