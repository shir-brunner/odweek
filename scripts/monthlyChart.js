function createMonthlyChart(comments_data, likes_data)
{
	if($.plot)
	{
		var plot = $.plot(
			$("#monthly_chart"), 
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
				xaxis: {
					mode: "time", 
					timeformat: "%b %y",
					tickSize: [4, "month"],
					autoscaleMargin: 0.02
				},
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
				yaxis: {
					min: 0,
					minTickSize:1,
					TickSize:1,
					tickDecimals: 0,
					tickFormatter: function numberWithCommas(x) {
                          return x.toString().replace(/\B(?=(?:\d{3})+(?!\d))/g, ",");
                    },
				},
				legend: {
					position: "nw",
					margin: [-130, 50]
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
    $("#monthly_chart").bind("plothover", function (event, pos, item) {
        $("#x").text(pos.x);
        $("#y").text(pos.y);
        if (item) {
            if (previousPoint != item.dataIndex) {
                previousPoint = item.dataIndex;
                
                $("#tooltip").remove();
                var x = item.datapoint[0],
                    y = item.datapoint[1];
				//x = new Date(x).toUTCString();
				x = new Date(x);
				var monthNames = [ "January", "February", "March", "April", "May", "June",
				    "July", "August", "September", "October", "November", "December" ];
					
                showTooltip(item.pageX - 17, item.pageY - 45, monthNames[x.getMonth()] + " " + x.getFullYear() + " - " + y);
            }
        }
        else {
            $("#tooltip").remove();
            previousPoint = null;            
        }
    });
}