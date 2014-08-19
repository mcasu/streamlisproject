<?PHP

require_once("./include/config.php");

if (!isset($_GET["type"]))
{
    exit;
}

$type = $_GET["type"];

$dbactions = $mainactions->GetDBActionsInstance();

        $user_num_byrole = $dbactions->GetUserNumbersByRole();
    
        if ($user_num_byrole)
        {
            while ($row = mysql_fetch_array($user_num_byrole))
            {
                $categories[] = $row['role_name'];
                $data[] = $row['user_number'];
            }
        }


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<head>
    <script type="text/javascript" src="../js/jquery-1.8.3.min.js"></script>
    <script type="text/javascript" src="../js/highcharts-2.2.4/highcharts.js"></script>
    
    <script type="text/javascript">
	$(document).ready(function()
        {
            $('#user_numberbyrole').highcharts({
                chart: {
                    type: 'bar',
                    borderWidth: 2,
                    borderColor: '#333'
                },
                title: {
                    text: 'Numero utenti per tipo'
                },
                xAxis: {
                    title: {
                        text: ''
                    },
                    categories: ['ADMIN', 'NORMAL', 'PUBLISHER']
                },
                yAxis: {
                    title: {
                        text: ''
                    },
                    tickInterval: 2
                },
                series: [{
                    name: 'Numero di utenti',
                    data: [<?php echo join($data, ',') ?>],
                    pointStart: 0
                }]
            });
        });
    </script>
</head>
<body>
    
<?PHP

    switch ($type)
    {
        case "user_numberbyrole":
            echo '<div id="user_numberbyrole"></div>';
            break;
        default:
            echo '<div>IMPOSSIBILE CARICARE IL GRAFICO</div>';
            break;
    }

?>
</body>

</html>