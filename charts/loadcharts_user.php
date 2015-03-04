<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<head>
    
    <script type="text/javascript">
	$(document).ready(function()
        {         
            var options_user_numberbyrole = {
                chart: {
                    renderTo: 'graph_user_numberbyrole',
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
            };
            
            var options_user_logged_bylogintime = {
                chart: {
                    renderTo: 'graph_user_logged_bylogintime',
                    type: 'column',
                    marginRight: 80,
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
                            this.x +': loggato da '+ this.y + ' minuti';
                    }
                },
                legend: {
                    layout: 'vertical',
                    align: 'right',
                    verticalAlign: 'top',
                    x: -10,
                    y: 120,
                    borderWidth: 0
                },
                series: []
            };
            
            <?php echo json_encode($_GET); ?>
            
	    $.getJSON("/charts/load_chart_data.php?type=user_numberbyrole", 
            { publisher_id: <?php echo json_encode(filter_input(INPUT_GET, 'publisher_id')); ?>}, 
            function(json)
	    {
		options_user_numberbyrole.series[0].data = json;
		chart_user_numberbyrole = new Highcharts.Chart(options_user_numberbyrole);
	    });

	    $.getJSON("/charts/load_chart_data.php?type=user_logged_bylogintime",
            { publisher_id: <?php echo json_encode(filter_input(INPUT_GET, 'publisher_id')); ?>}, 
            function(json)
	    {
		options_user_logged_bylogintime.xAxis.categories = json[0]['data'];
		options_user_logged_bylogintime.series[0] = json[1];
		chart_user_logged_bylogintime = new Highcharts.Chart(options_user_logged_bylogintime);
	    });
        });
    </script>
</head>
<body>
    
<?PHP

echo '<div id="graph_user_numberbyrole" style="margin:4px;max-width:400px""></div>';

echo '<div id="graph_user_logged_bylogintime" style="margin:4px"></div>';

?>
</body>

</html>