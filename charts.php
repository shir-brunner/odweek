<?php
	require_once("charts_functions.php");
	
	/**********************************************************/
	/******** convert monthly data to javascript array ********/
	/**********************************************************/
	
	$months = $db_handler->getMonthlyData();
	
	$monthly_js = formatMonthlyGraphData($months);
	$monthly_comments_js = $monthly_js['comments_js'];
	$monthly_likes_js = $monthly_js['likes_js'];
	
	/********************************************************/
	/******** convert users data to javascript array ********/
	/********************************************************/
	
	//$users = $db_handler->getUsersData();
	$users_sliced = array_slice($users, 0, 12);
	
	$users_js = formatUsersGraphData($users_sliced);
	$users_comments_js = $users_js['comments_js'];
	$users_likes_js = $users_js['likes_js'];
	$ticks_js = formatUsersGraphTicks($users_sliced);
	
	$likes_graph_js = formatUsersLikesGraphData($users_sliced);
	$users_likes_earned_js = $likes_graph_js['likes_earned_js'];
	$users_likes_given_js = $likes_graph_js['likes_given_js'];
?>

<div class="chart_container">
	<h3 class="chart_title">תגובות ולייקים לפי חודשים</h3>
	<div id="monthly_chart" class="chart"></div>
	<hr />
</div>

<div class="chart_container">
	<h3 class="chart_title">תגובות ולייקים לפי מגיבים</h3>
	<div id="users_chart" class="chart"></div>
	<hr />
</div>

<div class="chart_container">
	<h3 class="chart_title">גרף לייקים לפי מגיבים</h3>
	<div id="users_likes_chart" class="chart"></div>
	<hr />
</div>

<script language="javascript" src="scripts/flot/jquery.flot.min.js"></script>
<script language="javascript" src="scripts/monthlyChart.js"></script>
<script language="javascript" src="scripts/usersChart.js"></script>
<script language="javascript" src="scripts/usersLikesChart.js"></script>

<script language="javascript">
	<?php echo formatPicturesArray($users_sliced); ?>
	<?php echo formatUserNamesArray($users_sliced); ?>
	
	$(document).ready(function() {
		createMonthlyChart(<?php echo $monthly_comments_js; ?>, <?php echo $monthly_likes_js; ?>);
		createUsersChart(<?php echo $users_comments_js; ?>, <?php echo $users_likes_js; ?>, <?php echo $ticks_js; ?>);
		createUsersLikesChart(<?php echo $users_likes_earned_js; ?>, <?php echo $users_likes_given_js; ?>, <?php echo $ticks_js; ?>);
		
		//TODO try to fire when users chart ready event occurs
		setTimeout(function() {
			$('#users_chart .xAxis, #users_likes_chart .xAxis').find('.tickLabel').each(function() {
				var value = $(this).html();
				var html = '<img title="' + users_names[value] + '" style="width: 30px; height: 30px;" src="' + users_pics[value] + '" />';
				
				$(this).html(html);
			});
		}, 500);
	});
</script>