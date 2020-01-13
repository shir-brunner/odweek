function createUsersLikesChart(likes_earned, likes_given, ticks)
{
	if($.plot)
	{
		var plot = $.plot(
			$("#users_likes_chart"), 
			[
				{
	                data: likes_earned,
					label: 'לייקים (קיבל)',
					color: 'blue'
	            },
				{
	                data: likes_given,
					label: 'לייקים (נתן)',
					color: 'green'
	            },
			],
			{
				tooltip: true,
                series: {
                    lines: {
                        show: true
                    },
                    points: {
                        show: false
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
					margin: [-170, 50]
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
    $("#users_likes_chart").bind("plothover", function (event, pos, item) {
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