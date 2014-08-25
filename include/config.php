<?php
	error_reporting(E_ALL ^ E_NOTICE);
	//error_reporting(0);
	date_default_timezone_set('PRC'); //设置中国时区	
	define("NOW",time());

	define("DB_HOST","localhost");
	define("DB_USER","root");
	define("DB_PASSWORD","1234");
	define("DB_DATABASE","news");
	$syslogFile=$_SERVER['DOCUMENT_ROOT']."/news-".date('Ymd').".log";
	
	define("HOST","http://news.dev") ;
	define("BASE_DIR",dirname(dirname(__FILE__)));
	
?>

