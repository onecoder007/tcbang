<?php
	include "TopSdk.php";
	date_default_timezone_set('Asia/Shanghai'); 

	$httpdns = new HttpdnsGetRequest;
	$client = new ClusterTopClient("appkey","appsecret");
	// $client = new TopClient("appkey","appsecret");
	// $client->appkey = "appkey";
	// $client->secretKey = "appsecret";
	$client->gatewayUrl = "https://eco.taobao.com/router/rest";

	var_dump($client->execute($httpdns));

?>