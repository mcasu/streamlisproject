<?PHP

require_once("./include/config.php");

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
            $('#dashboard_graph_user_type').highcharts({
                chart: {
                    type: 'bar',
                    borderWidth: 2
                },
                title: {
                    text: 'Numero utenti per tipo'
                },
                xAxis: {
                    title: {
                        text: ''
                    },
                    categories: ['ADMIN', 'NORMAL', 'PUBLISHER'],
                    tickInterval: 1
                },
                yAxis: {
                    title: {
                        text: ''
                    },
                    tickInterval: 1
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

echo '<div id="dashboard_graph_user_type"></div>';

?>
</body>

</html>