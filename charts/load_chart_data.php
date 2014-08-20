<?PHP

require_once("../include/config.php");

if (!isset($_GET["type"]))
{
    exit;
}

$type = $_GET['type'];

$dbactions = $mainactions->GetDBActionsInstance();

switch ($type)
{
    case "user_numberbyrole":
	$rows = GraphDataUserNumberByRole($dbactions);
	print json_encode($rows, JSON_NUMERIC_CHECK);
	break;
    case "user_logged_bylogintime":
	$rows = GraphDataUserLoggedByLoginTime($dbactions);
	print json_encode($rows, JSON_NUMERIC_CHECK);
	break;
}

function GraphDataUserNumberByRole($dbactions)
{
    $result = $dbactions->GetUserNumbersByRole();
    
    if ($result)
    {
	$rows = array();
	while ($row = mysql_fetch_array($result))
	{
	    // role
	    $values[0] = $row['role_name'];
	    // user number
	    $values[1] = $row['user_number'];
	    
	    array_push($rows, $values);
	}
	return $rows;
    }
}

function GraphDataUserLoggedByLoginTime($dbactions)
{
    $result = $dbactions->GetUserLoggedByLoginTime();
    
    if ($result)
    {
	$rows = array();
	$category = array();
	$category['name'] = 'Username';
	$series1 = array();
	$series1['name'] = 'Utenti';
	    
	while ($row = mysql_fetch_array($result))
	{
	    // username
	    $category['data'][] = $row['username'];
	    
	    // last login timestamp
	    //$time_diff = abs(time() - strtotime($row['last_login']));
	    
	    $d1=new DateTime();
	    $d1->setTimestamp(time());
	    $d2=new DateTime($row['last_login']);
	    $diff=$d2->diff($d1);

	    $minutes = $diff->format('%i') + ($diff->format('%h')*60) + ($diff->format('%d')*24*60);
	    
	    /*
	    $fullDays = floor($time_diff/(60*60*24));
	    $fullHours = floor(($time_diff-($fullDays*60*60*24))/(60*60));
	    $fullMinutes = floor(($time_diff-($fullDays*60*60*24)-($fullHours*60*60))/60);
	    */
	    
	    $series1['data'][] = $minutes;
	}
	
	array_push($rows,$category);
	array_push($rows,$series1);
	
	return $rows;
    }
}

?>