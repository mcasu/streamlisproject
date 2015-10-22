<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<head>
    <script type="text/javascript" src="../js/jquery-1.11.0.min.js"></script>
    <script type="text/javascript" src="../js/highcharts-2.2.4/highcharts.js"></script>
    
    <script type="text/javascript">
	$(document).ready(function()
        {         
            var options_user_numberbyrole = {
                chart: {
                    renderTo: 'graph',
                    type: 'pie',
                    borderWidth: 2,
                    borderColor: '#333'
                },
                title: {
                    text: 'Numero utenti per tipo'
                },
                tooltip: {
                    pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            format: '<b>{point.name}</b>: {point.y}',
                            style: {
                                color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                            }
                        }
                    }
                },
                series: [{
                    type: 'pie',
                    name: 'Numero di utenti',
                    data: []
                }]
            }
            
            var options_user_logged_bylogintime = {
                chart: {
                    renderTo: 'graph',
                    type: 'column',
                    marginRight: 130,
                    marginBottom: 25,
                    borderWidth: 2,
                    borderColor: '#333'
                },
                title: {
                    text: 'Utenti loggati',
                    x: -20 //center
                },
                subtitle: {
                    text: '',
                    x: -20
                },
                xAxis: {
                    categories: []
                },
                yAxis: {
                    title: {
                        text: 'Minuti'
                    },
                    plotLines: [{
                        value: 0,
                        width: 1,
                        color: '#808080'
                    }]
                },
                tooltip: {
                    formatter: function() {
                            return '<b>'+ this.series.name +'</b><br/>'+
                            this.x +': '+ this.y;
                    }
                },
                legend: {
                    layout: 'vertical',
                    align: 'right',
                    verticalAlign: 'top',
                    x: -10,
                    y: 100,
                    borderWidth: 0
                },
                series: []
            }
            
            var $_GET = <?php echo json_encode($_GET); ?>;
            
            var graphtype = $_GET['type'];
            switch (graphtype)
            {
                case "user_numberbyrole":
		    alert ("Tipo di grafico: " + graphtype);
                    $.getJSON("/charts/load_chart_data.php?type=user_numberbyrole", function(json)
                    {
                        options_user_numberbyrole.series[0].data = json;
                        chart_user_numberbyrole = new Highcharts.Chart(options_user_numberbyrole);
                    });
                    break;
                case "user_logged_bylogintime":
		    alert ("Tipo di grafico: " + graphtype);
                    $.getJSON("/charts/load_chart_data.php?type=user_logged_bylogintime", function(json)
                    {
                        options_user_logged_bylogintime.xAxis.categories = json[0]['data'];
                        options_user_logged_bylogintime.series[0] = json[1];
                        chart_user_logged_bylogintime = new Highcharts.Chart(options_user_logged_bylogintime);
                    });
                    break;
            }
        });
    </script>
</head>
<body>
    
<?PHP

echo '<div id="graph"></div>';

?>
</body>

</html>