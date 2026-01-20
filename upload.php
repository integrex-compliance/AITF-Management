<?php
	require("class.phpmailer.php");
	$rege = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';
	$cno = $_POST['cno'];
	$name = $_POST['name'];
	$oname = $_POST['oname'];
	$eid = $_POST['eid'];
	$mess="";
	if(isset($_POST['action']))
	{
	if($_POST['action']=="Upload File")
	{
	if($_POST['name']=="" || $_POST['cno']=="" || $_POST['eid']=="" || $_POST['oname']=="")
	{
		$mess = "All fields have to filled"; 
	}
	else if((!preg_match("/[^0-9]/",'',$_POST['cno'])) && strlen($cno) !=10)
	{
		$mess = "Invalid Contact NO enter mobile no";
	}
	else if(!preg_match($rege,$_POST['eid']))
	{
		$mess = "Invalid Email ID";
	}
	else
	{
   // Configuration - Your Options
      $allowed_filetypes = array('.doc','.pdf'); // These will be the types of file that will pass the validation.
      $max_filesize = 524288; // Maximum filesize in BYTES (currently 0.5MB).
      $upload_path = './text/'; // The place the files will be uploaded to (currently a 'files' directory).
 
   $filename = $_POST['oname'].$_FILES['userfile']['name']; // Get the name of the file (including file extension).
   $ext = substr($filename, strpos($filename,'.'), strlen($filename)-1); // Get the extension from the filename.
   //Check if the filetype is allowed, if not DIE and inform the user.
   if(!in_array($ext,$allowed_filetypes))
      $mess = "The file you attempted to upload is not allowed.";
 
   // Now check the filesize, if it is too large then DIE and inform the user.
   if(filesize($_FILES['userfile']['tmp_name']) > $max_filesize)
      $mess = "The file you attempted to upload is too large.";
 
   // Check if we can upload to the specified path, if not DIE and inform the user.
   if(!is_writable($upload_path))
     $mess = "You cannot upload to the specified directory, please CHMOD it to 777.";
 
   // Upload the file to your specified path.
   if(move_uploaded_file($_FILES['userfile']['tmp_name'],$upload_path . $filename))
	{
		$f=$upload_path.$filename;
		//$dbh = new PDO("odbc:Driver={Microsoft Access Driver (*.mdb)};Dbq=".realpath("database/olapplications.mdb"));
		try
		{
		$hostname='localhost';
		$username='aitf-mcl';
		$password='aitfmcl';
		$dbh = new PDO("mysql:host=$hostname;dbname=aitfweb",$username,$password);
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$sql = "INSERT INTO olapplications(contact_no,organisation_name,contact_name,email_id,app_file) VALUES (?,?,?,?,?)";                             
		$stmt = $dbh->prepare($sql);
		$stmt->bindParam(1, $cno);      
		$stmt->bindParam(2, $oname);
		$stmt->bindParam(3, $name);
		$stmt->bindParam(4, $eid);
		$stmt->bindParam(5, $f);                             
		$q=$stmt->execute();
		}
		catch (Exception $ex) {
        		$mess = $ex;
			}
		$email = new PHPMailer();
		$email->From      = $eid;
		$email->FromName  = 'aitf.co.in';
		$email->Subject   = 'New application on website';
		$email->Body      = 'New application from '.$oname.' organisation contact person '.$name.' Conatact no '.$cno.'.';
		$email->AddAddress( 'aitfacct@gmail.com' );
		//$email->AddAttachment( $_FILES['uploaded_file']['tmp_name'],$_FILES['uploaded_file']['name']);
 		$email->Send(); 
		$mess = "successfully applied we will get to u soon";
	}
      else
     {
	   $mess = "There was an error during the file upload.Please try again."; // It failed :(.
	}
	}
	 //header("Location: application.php?msg=".urlencode($mess));
		echo '<script type="text/javascript">'; 
				echo "alert('".$mess."');"; 
				echo 'window.location.href = "application.php";';
				echo '</script>';
 }
}
?>