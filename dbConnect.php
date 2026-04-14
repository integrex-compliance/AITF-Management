<?php
//include_once 'site_header_end.php';

//try{
//$hostname='mysql:host=localhost;dbname=aitf;charset=utf8';
//$username='root';
//$password='';
//$db = new PDO($hostname,$username,$password);
//$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
////var_dump($db);
//}
//catch(Exception $e)
//{
//	echo  $e->getMessage();
//// 	$e->getTraceAsString()	
//}

try {
	$hostname = 'mysql:host=localhost;dbname=aitf_web;charset=utf8';
	$username = 'aitf-mcl';
	$password = 'aitfmcl';
	$db = new PDO($hostname, $username, $password);
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$db->exec("SET time_zone = '+05:30'");
} catch (Exception $e) {
	error_log($e->getMessage());
	$db = null;
	// 	$e->getTraceAsString()	
}
