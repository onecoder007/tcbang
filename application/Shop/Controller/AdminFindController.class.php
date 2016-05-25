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
class AdminFindController extends AdminbaseController {
	
	function index(){
		import('phpQuery.phpQuery','simplewind/Lib/Extend/','.php');
		\phpQuery::$defaultCharset="gbk";
		
		//$url_ago = I('post.url');
		//foreach($url_ago as $key=>$val){
			
		//}
		$url = 'https://detail.tmall.com/item.htm?spm=a230r.1.14.107.mzOG9s&id=40111047312&ns=1&abbucket=2&sku_properties=5919063:6536025';
		\phpQuery::newDocumentFile('https://detail.tmall.com/item.htm?spm=a230r.1.14.107.mzOG9s&id=40111047312&ns=1&abbucket=2&sku_properties=5919063:6536025'); 
		echo pq("")->html();
		$info['str'] =  pq(".tb-main-title h3")->html();
		$info['imgurl'] = pq("#J_ImgBooth")->html();
		$info['kuaidi'] = pq("#J_PostageToggleCont p")->html();
		$info['price'] = pq(".tm-price")->html();
		$info['score'] = pq(".main-info")->html();
		$info['rate'] = pq(".rate-score strong")->html();
		//dump($info);
		
		\phpQuery::$documents = array();
		
		foreach($info as $i_key => $i_val){
			$info['i_key'] = iconv("GBK","UTF-8//IGNORE",$i_val);
			$info['i_key'] = trim($info['i_key']);
		}
		
		//dump($info);
		
		$test = M("Test");
		$data['content'] = $info;
		//if($test->add($data)){
		//	echo "添加成功";
		//}else {
		//	echo "失败";
		//}
	}
	
	function snoopy(){
		$url = 'https://s.taobao.com/search?initiative_id=tbindexz_20150914&spm=a21bo.7724922.8452-taobao-item.2&sourceId=tb.index&search_type=item&ssid=s5-e&commend=all&imgfile=&q=%E9%BC%A0%E6%A0%87&suggest=history_2&_input_charset=utf-8&wq=&suggest_query=&source=suggest';
		//$url = 'http://querylist.cc/';
		
		$snoopy = new \Org\Collect\Snoopy;
		$snoopy->fetchtext($url);
		dump($snoopy->results);
		echo '错误：'.$snoopy->error;
	}
	
	function php_info(){
		phpinfo();
	}
	
	function querylist(){
		import('Querylist.QueryList','simplewind/Lib/Extend/');
		
		$url = "http://ai.taobao.com/auction/edetail.htm?e=tjEY%2BEj7mcQjmraEDZVrLrPIEpjg9R6%2Fn1Y2qSkaZVCLltG5xFicOcyvbMN%2FjluyDPIwxrc30riUoQH65Fgld4V5325j0YItPxUvkVcCd3vNfwH7%2BvW8alFE%2BBP3Qa7QUa6whSeFsYBt2myTO7A4NnnkvUcoLZ98VzmAD7JBMpE%3D&ptype=100010&unid=&from=basic&clk1=6e42a22a371464a8085773fe3c57a6d9&upsid=6e42a22a371464a8085773fe3c57a6d9";
		//元素选择器
		
		$reg = array(
			"title" => array(".item-title a","text"),
			"img" => array("#J_test","src"),
			"price" => array(".price strong","text"),
			"sell" => array(".info-sell a","text"),
			"roll" => array(".info-wrap strong","text"),
			"mall" => array(".shop-log-wrap p","text")
		);
		//块选择器
		$rang = "";
		//采集
		$hj = \QueryList::Query($url,$reg,$rang,'','UTF-8');
		
		//输入采集结果
		dump($hj->jsonArr);
		
	}
	
	function curl(){
		$agent = '';
		$url = 'https://detail.tmall.com/item.htm?id=18501477447&ali_refid=a3_420432_1006:1103469087:N:%E7%94%B5%E8%84%91%E9%9F%B3%E7%AE%B1:bb9a795563c1265c79434af86d82fcbd&ali_trackid=1_bb9a795563c1265c79434af86d82fcbd&spm=a230r.1.14.6.wCyt9H';
		//$url = 'http://www.tcbang.cn';
		header("Content-type: text/html; charset=GB2312");
//$url = "http://detail.tmall.com/item.htm?id=2315770603";
//$url = "http://www.donlimmall.com/";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_COOKIEJAR, "cookie00.txt");
curl_setopt($ch, CURLOPT_COOKIEFILE, "cookie00.txt");
curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1); 
$ret = curl_exec($ch);
curl_close($ch);
echo $ret;
		
		//dump(curl_error($ch));
		
		//dump($results);
	}
	
}