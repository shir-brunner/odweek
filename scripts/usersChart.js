function createUsersChart(comments_data, likes_data, ticks)
{
	if($.plot)
	{
		var plot = $.plot(
			$("#users_chart"), 
			[
				{
	                data: comments_data,
					label: 'תגובות',
					color: 'red'
	            },
				{
	                data: likes_data,
					label: 'לייקים',
					color: 'blue'
	            },
			],
			{
				tooltip: true,
				series: {
					bars: {
					  	'show': 'true',
						'align': "center",
						'barWidth': 0.3
					}
				},
                grid: {
                    borderWidth: 0, 
                    hoverable: true,
                    clickable: true
                },
				xaxis: {
					ticks: ticks,
					autoscaleMargin: 0.02
				},
				yaxis: {
					min: 0,
					minTickSize:1,
					TickSize:1,
					tickDecimals: 0,
					tickFormatter: function numberWithCommas(x) {
                          return x.toString().replace(/\B(?=(?:\d{3})+(?!\d))/g, ",");
                    }
				},
				legend: {
					position: "nw",
					margin: [-145, 50]
				}
			}
		);
	}
	
    function showTooltip(x, y, contents) {
	    $('<div id="tooltip">' + contents + '</div>').css( {
	        position: 'absolute',
	        display: 'none',
	        top: y + 5,
	        left: x + 5,
	        border: '1px solid #fdd',
	        padding: '2px',
	        'background-color': 'orange',
	        opacity: 0.80
	    }).appendTo("body").fadeIn(200);
    }

    var previousPoint = null;
    $("#users_chart").bind("plothover", function (event, pos, item) {
        $("#x").text(pos.x);
        $("#y").text(pos.y);
        if (item) {
            if (previousPoint != item.dataIndex) {
                previousPoint = item.dataIndex;
                
                $("#tooltip").remove();
                var x = item.datapoint[0],
                    y = item.datapoint[1];
				//x = new Date(x).toUTCString();
				//x = new Date(x);
					
                showTooltip(item.pageX - 17, item.pageY - 45, y);
            }
        }
        else {
            $("#tooltip").remove();
            previousPoint = null;            
        }
    });
}