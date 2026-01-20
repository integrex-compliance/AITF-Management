<?php
	if(isset($_POST['action']))
	{
	if($_POST['action']=="Upload File")
	{
   // Configuration - Your Options
      $allowed_filetypes = array('.mdb'); // These will be the types of file that will pass the validation.
      $max_filesize = 52428800; // Maximum filesize in BYTES (currently 0.5MB).
      $upload_path = './database/'; // The place the files will be uploaded to (currently a 'files' directory).
 
   $filename = $_FILES['userfile']['name']; // Get the name of the file (including file extension).
   $ext = substr($filename, strpos($filename,'.'), strlen($filename)-1); // Get the extension from the filename.
	echo $filename;
   //Check if the filetype is allowed, if not DIE and inform the user.
   if(!in_array($ext,$allowed_filetypes))
      header("Location: admin.php?msg=".urlencode("The file you attempted to upload is not allowed."));
 
   // Now check the filesize, if it is too large then DIE and inform the user.
   if(filesize($_FILES['userfile']['tmp_name']) > $max_filesize)
      header("Location: admin.php?msg=".urlencode("The file you attempted to upload is too large."));
 
   // Check if we can upload to the specified path, if not DIE and inform the user.
   if(!is_writable($upload_path))
      header("Location: admin.php?msg=".urlencode("You cannot upload to the specified directory, please CHMOD it to 777."));
 
   // Upload the file to your specified path.
   if(move_uploaded_file($_FILES['userfile']['tmp_name'],$upload_path . $filename))
		{
		 header("Location: admin.php?msg=".urlencode("updated client list successfully"));
		}
      else
		{
		 header("Location: admin.php?msg=".urlencode("Somthing goes wrong try again."));
		}
 }
}
?>
