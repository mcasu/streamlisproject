<?php include("../check_login.php"); ?>

<?PHP
require_once($_SERVER['DOCUMENT_ROOT'] . "/include/config.php");

$utils = $mainactions->GetUtilsInstance();
$dbactions = $mainactions->GetDBActionsInstance();

$viewer_list = $_POST['viewerlist'];
$publisher_id = $_POST['publisher_id'];
	
$result = $dbactions->DelViewersLink($viewer_list, $publisher_id);
if (!$result)
{
	echo "Unlinking viewers [ ".$viewer_list." ] to publisher " .$publisher_id. " FAILED\r\n".$dbactions->GetErrorMessage();
	//error_log("Unlinking viewers [ ".$viewer_list." ] to publisher " .$publisher_id. " FAILED\r\n".$dbactions->GetErrorMessage());
}
else
{
	echo "Unlinking viewers [ ".$viewer_list." ] to publisher " . $publisher_id. " SUCCESS";
	//error_log("Unlinking viewers [ ".$viewer_list." ] to publisher " . $publisher_id. " SUCCESS");
}

?>
