<?PHP
require_once($_SERVER['DOCUMENT_ROOT'] . "/include/membersite_config.php");

$utils = $fgmembersite->GetUtilsInstance();
$dbactions = $fgmembersite->GetDBActionsInstance();

$viewer_list = $_POST['viewerlist'];
$publisher_id = $_POST['publisher_id'];
	
$result = $dbactions->AddViewersLink($viewer_list, $publisher_id);
if (!$result)
{
	echo "Linking viewers [ ".$viewer_list." ] to publisher " .$publisher_id. " FAILED\r\n".$dbactions->GetErrorMessage();
}
else
{
	echo "Linking viewers [ ".$viewer_list." ] to publisher " . $publisher_id. " SUCCESS";
}

?>
