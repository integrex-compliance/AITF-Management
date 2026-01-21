


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

try{
	$hostname = 'mysql:host=localhost;dbname=aitf_web;charset=utf8';
	$username = 'aitf-mcl';
	$password = 'aitfmcl';
	$db = new PDO($hostname, $username, $password);
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch(Exception $e)
{
	echo $e->getMessage();
// 	$e->getTraceAsString()	
}