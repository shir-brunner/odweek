<?php
	if(!isset($_GET['uid']))
	{
		header('Location: index.php');
	}
	
	require_once("charts_functions.php");
	
	$comments = $db_handler->getAllUserComments($_GET['uid']);
	$user = $db_handler->getUser($_GET['uid']);
	
	/**********************************************************/
	/******** convert monthly data to javascript array ********/
	/**********************************************************/
	
	$months = $db_handler->getMonthlyData($_GET['uid']);
	
	$monthly_js = formatMonthlyGraphData($months);
	$monthly_comments_js = $monthly_js['comments_js'];
	$monthly_likes_js = $monthly_js['likes_js'];
?>

<div id="general_user_info">
	<table style="width: 500px; display: inline;">
		<tr>
			<th colspan="2"><?php echo $user['user_name']; ?></th>
		</tr>
		<tr>
			<th>סה"כ תגובות</th>
			<th><?php echo number_format($user['comments'], 0, '.', ','); ?></th>
		</tr>
		<tr>
			<th>סה"כ לייקים (קיבל)</th>
			<th><?php echo number_format($user['likes'], 0, '.', ','); ?></th>
		</tr>
		<tr>
			<th>סה"כ לייקים (נתן)</th>
			<th><?php echo number_format($user['likes_given'], 0, '.', ','); ?></th>
		</tr>
		<tr>
			<th>ממוצע לייקים לתגובה</th>
			<th><?php echo number_format($user['likes'] / $user['comments'], 2, '.', ','); ?></th>
		</tr>
		<tr>
			<th>% מכלל התגובות</th>
			<th><?php echo number_format($user['percent_of_all_comments'], 2, '.', ','); ?>%</th>
		</tr>
		<tr>
			<th>אורך תגובה ממוצעת</th>
			<th><?php echo number_format($user['avg_comment_length'], 0, '.', ','); ?> אותיות</th>
		</tr>
		<tr>
			<th>אורך התגובה הארוכה ביותר</th>
			<th><?php echo number_format($user['longest_comment_length'], 0, '.', ','); ?> אותיות</th>
		</tr>
		<tr>
			<th>אורך התגובה הקצרה ביותר</th>
			<th><?php echo number_format($user['shortest_comment_length'], 0, '.', ','); ?> אותיות</th>
		</tr>
		<tr>
			<th>ממוצע תגובות ליום</th>
			<th><?php echo number_format($user['comments'] / daysDiff('2010-01-14', date('Y-m-d')), 2, '.', ','); ?></th>
		</tr>
		<tr>
			<th>תאריך תגובה ראשונה</th>
			<th><?php echo DateTime::createFromFormat('Y-m-d H:i:s', $user['first_comment_time'])->format('H:i:s d/m/Y'); ?></th>
		</tr>
		<tr>
			<th>תאריך תגובה אחרונה</th>
			<th><?php echo DateTime::createFromFormat('Y-m-d H:i:s', $user['last_comment_time'])->format('H:i:s d/m/Y'); ?></th>
		</tr>
	</table>

	<table style="width: 450px; margin-right: 35px; display: inline;">
		<tr>
			<th colspan="2">פירוט לייקים</th>
		</tr>
		
		<?php
			foreach($user['likes_info'] as $likes)
			{
				echo '<tr>';
				echo '	<th>תגובות שקיבלו ' . $likes['likes'] . ' לייקים</th>';
				echo '	<th>' . number_format($likes['comments'], 0, '.', ',') . '</th>';
				echo '</tr>';
			}
		?>
	</table>
</div>


<div class="chart_container" style="margin-top: 50px; margin-bottom: 50px;">
	<div id="monthly_chart" class="chart"></div>
</div>

<table class="table_sorter">
	<thead>
		<tr>
			<th style="width: 40px;">#</th>
			<th style="width: 50px;">תמונה</th>
			<th>תגובה</th>
			<th style="width: 40px;">לייקים</th>
			<th>תאריך</th>
		</tr>
	</thead>
	
	<tbody>
		<?php
		    $counter = 0;
			foreach($comments as $comment_id => $comment)
			{
				$counter++;
				
				echo '<tr>';
				
				echo '	<td style="text-align: center;">' . $counter . '</td>';
				echo '	<td style="text-align: center;"><img style="width: 35px; height: 35px;" src="' . $comment['picture'] . '" /></td>';
				echo '	<td>' . nl2br($comment['text']) . '</td>';
				echo '	<td style="text-align: center;"><div class="likes_details" comment_id="' . $comment_id . '">' . $comment['likes'] . '</div></td>';
				echo '	<td data-sortAs="' . DateTime::createFromFormat('Y-m-d H:i:s', $comment['time'])->format('YmdHis') . '" style="text-align: center;">' . DateTime::createFromFormat('Y-m-d H:i:s', $comment['time'])->format('H:i:s d/m/Y') . '</td>';
				
				echo '</tr>';
			}
		?>
	</tbody>
</table>

<script language="javascript" src="scripts/flot/jquery.flot.min.js"></script>
<script language="javascript" src="scripts/monthlyChart.js"></script>

<script language="javascript">
	$(document).ready(function() {
		//puts the users picture in the upper corners
		var html_left = '<img style="position: absolute; left: 20px; top: 20px; width: 90px; height: 90px;" src="<?php echo $user["picture"]; ?>" />';
		var html_right = '<img style="position: absolute; right: 20px; top: 20px; width: 90px; height: 90px;" src="<?php echo $user["picture"]; ?>" />';
		$('body').append(html_left + html_right);
		
		createMonthlyChart(<?php echo $monthly_comments_js; ?>, <?php echo $monthly_likes_js; ?>);
	});
</script>