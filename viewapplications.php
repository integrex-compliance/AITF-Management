<!doctype html>
<!--[if IE 7 ]>    <html lang="en-gb" class="isie ie7 oldie no-js"> <![endif]-->
<!--[if IE 8 ]>    <html lang="en-gb" class="isie ie8 oldie no-js"> <![endif]-->
<!--[if IE 9 ]>    <html lang="en-gb" class="isie ie9 no-js"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html lang="en-gb" class="no-js"> <!--<![endif]-->

<head>
	<title>AITF - Assessment &amp; Institute for Technical Forum</title>
	
	<meta charset="utf-8">
	<meta name="keywords" content="" />
	<meta name="description" content="" />
    
    <!-- Favicon --> 
	<link rel="shortcut icon" href="imageused/fav.ico">
    
    <!-- this styles only adds some repairs on idevices  -->
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    
    <!-- Google fonts - witch you want to use - (rest you can just remove) -->
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700' rel='stylesheet' type='text/css'>
    
    <!--[if lt IE 9]>
		<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
    
    <!-- Favicon -->
  	<link rel="shortcut icon" type="image/x-icon" href="imageused/fav.ico">
    
    <!-- ######### CSS STYLES ######### -->
	
    <link rel="stylesheet" href="css/reset.css" type="text/css" />
	<link rel="stylesheet" href="css/style.css" type="text/css" />
    
    <!-- responsive devices styles -->
	<link rel="stylesheet" media="screen" href="css/responsive-leyouts.css" type="text/css" />   

<!-- just remove the below comments witch color skin you want to use -->
    <!--<link rel="stylesheet" href="styles/colors/lightblue.css" />-->
    <!--<link rel="stylesheet" href="styles/colors/lightgreen.css" />-->
    <!--<link rel="stylesheet" href="styles/colors/blue.css" />-->
    <!--<link rel="stylesheet" href="styles/colors/green.css" />-->
    <!--<link rel="stylesheet" href="styles/colors/red.css" />-->
    <!--<link rel="stylesheet" href="styles/colors/cyan.css" />-->
    <!--<link rel="stylesheet" href="styles/colors/purple.css" />-->
    <!--<link rel="stylesheet" href="styles/colors/yellow.css" />-->
    <!--<link rel="stylesheet" href="styles/colors/brown.css" />-->   
    
    <!-- style switcher -->
    <link rel = "stylesheet" media = "screen" href = "js/style-switcher/color-switcher.css" />
	  
    <!-- jquery jcarousel -->
    <link rel="stylesheet" type="text/css" href="js/jcarousel/skin.css" />
    <link rel="stylesheet" type="text/css" href="js/jcarousel/skintwo.css" />
    
</head>
<div class="image">
	<img src="imageused/webheading.jpg"/> 
</div>
<div class="site_wrapper">

	<div class="container">
    
    	<div class="top_section">
              
            <div class="logo_innerpage"><a href="index.html" class="logo_innerpage"><h1>A<i>I</i>TF <small>Management Certification Limited</small></h1></a></div><!-- end logo -->
               
        <nav id="access" class="access" role="navigation">
        <div id="menu" class="menu">
        	
        	<ul id="tiny">
                    <li><a href="admin.php?msg=".urlencode("")>Update Client List</a></li>
				  <li><a href="viewapplications.php">New Applications</a></li>
				  <li><a href="logout.php">Log Out</a></li>

		</ul>
	</div>
	</div>
</div>
<div class="site_wrapper">
<div class="container">
      <div class="punchline_text_box">
        <div class="left">
		<div class="content_fullwidth">

    <div class="one_full">
    
		<h2>LIST OF CLIENTS AND THEIR CURRENT STATUS OF CERTIFICATION</h2>
			<div class="table-style">
                <table class="table-list">
                    <tr>
                        <th>Organisaion name</th>
                        <th>Contact person</th>
                        <th>Email ID</th>
                        <th>Contact NO</th>
                        <th>Application file</th>
                   </tr>
<?php
try{
   // Connect $dbh = new PDO("odbc:Driver={Microsoft Access Driver (*.mdb)};Dbq=".realpath("database/olapplications.mdb"));
$hostname='localhost';
$username='aitf-mcl';
$password='aitfmcl';
$dbh = new PDO("mysql:host=$hostname;dbname=aitfweb",$username,$password);
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$stmt = $dbh->query("SELECT * FROM `olapplications`");
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach( $results as $r)//<-------line 14
{
if($r['contact_no']!="")
{
?> <tr>
		<td><?php 
			print($r['organisation_name']);
			?>
		</td>
		<td><?php
			print($r['contact_name']);
			?>
		</td>
		<td><?php
			print($r['email_id']);
			?>
		</td>
		<td><?php
			print($r['contact_no']);
			?>
		</td>
		<td>
		
		<a href=<?php echo "'".$r['app_file']."'"?> target="_blank" class="knowmore_but">Download&nbsp;Here</a></div>

		</td>
   </tr>

<?php
}
}
    $dbh = null;
}
catch(PDOException $e){
    echo $e->getMessage();
}
?>
  </table>
			</div>
    
    </div><!-- end tables -->
   

</div><!-- end content full width area -->
	</div>
    </div>	
 	</div>
    </div>
