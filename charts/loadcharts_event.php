<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it-IT" lang="it-IT">
<head>
    
    <script type="text/javascript">
	$(document).ready(function()
        {         
            var options_event_ondemand_numberbypublisher = {
                chart: {
                    renderTo: 'graph_event_ondemand_numberbypublisher',
                    type: 'pie',
                    borderWidth: 2,
                    borderColor: '#333'
                },
                title: {
                    text: 'Numero eventi on-demand per congregazione'
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
                    name: 'Eventi On-demand',
                    data: []
                }]
            }
            
	    $.getJSON("/charts/load_chart_data.php?type=event_ondemand_numberbypublisher", function(json)
	    {
		options_event_ondemand_numberbypublisher.series[0].data = json;
		chart_event_ondemand_numberbypublisher = new Highcharts.Chart(options_event_ondemand_numberbypublisher);
	    });
        });
    </script>
</head>
<body>
    
<?PHP

echo '<div id="graph_event_ondemand_numberbypublisher" style="margin:4px;max-width:500px"></div>';

?>
</body>

</html>