<?php 
include("../check_login.php"); 
include(getenv("DOCUMENT_ROOT") . "/include/check_role_publisher.php");

$user_id = $_POST['user_id'];
	
$result = $dbactions->DeleteUser($user_id);
if (!$result)
{
	echo "UserID ".$user_id." FAILED\r\n".$dbactions->GetErrorMessage();
}
else
{
	echo "UserID ".$user_id." SUCCESS";
}

