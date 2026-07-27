<?php
// Establishing Connection with Server by passing server_name, user_id and password as a parameter
$hostname='localhost';
$username='aitf-mcl';
$password='aitfmcl';
$dbh = new PDO("mysql:host=$hostname;dbname=aitfweb",$username,$password);
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
require_once __DIR__ . '/include/session_helper.php';
aitf_start_session();
// Storing Session
$user_check=$_SESSION['login_user'];
// SQL Query To Fetch Complete Information Of User
$sth = $dbh->query("SELECT * FROM `login` WHERE `username` ='".$user_check."'");
$result = $sth->fetchAll(PDO::FETCH_ASSOC);
foreach($result as $r)
$login_session =$r['username'];
if(!isset($login_session)){
header('Location: loginSelection.php');
exit;
}
?>
