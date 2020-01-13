<?php
	$summary = $db_handler->getSummaryData();
	$likes_info = $db_handler->getLikesInfo();
?>

<div id="general_user_info">
	<table style="width: 500px; display: inline;">
		<tr>
			<th>סה"כ מגיבים</th>
			<th><?php echo number_format($summary['users_count'], 0, '.', ','); ?></th>
		</tr>
		<tr>
			<th>סה"כ תגובות</th>
			<th><?php echo number_format($summary['comments'], 0, '.', ','); ?></th>
		</tr>
		<tr>
			<th>סה"כ לייקים</th>
			<th><?php echo number_format($summary['likes'], 0, '.', ','); ?></th>
		</tr>
		<tr>
			<th>ממוצע לייקים לתגובה</th>
			<th><?php echo number_format($summary['likes'] / $summary['comments'], 2, '.', ','); ?></th>
		</tr>
		<tr>
			<th>אורך תגובה ממוצעת</th>
			<th><?php echo number_format($summary['avg_comment_length'], 0, '.', ','); ?> אותיות</th>
		</tr>
		<tr>
			<th>אורך התגובה הארוכה ביותר</th>
			<th><?php echo number_format($summary['longest_comment_length'], 0, '.', ','); ?> אותיות (<?php echo $summary['longest_comment_user']; ?>)</th>
		</tr>
		<tr>
			<th>אורך התגובה הקצרה ביותר</th>
			<th><?php echo number_format($summary['shortest_comment_length'], 0, '.', ','); ?> אותיות (<?php echo $summary['shortest_comment_user']; ?>)</th>
		</tr>
		<tr>
			<th>ממוצע תגובות ליום</th>
			<th><?php echo number_format($summary['comments'] / daysDiff('2010-01-14', date('Y-m-d')), 2, '.', ','); ?></th>
		</tr>
		<tr>
			<th>תאריך תגובה ראשונה</th>
			<th><?php echo DateTime::createFromFormat('Y-m-d H:i:s', $summary['first_comment_time'])->format('H:i:s d/m/Y'); ?></th>
		</tr>
		<tr>
			<th>תאריך תגובה אחרונה</th>
			<th><?php echo DateTime::createFromFormat('Y-m-d H:i:s', $summary['last_comment_time'])->format('H:i:s d/m/Y'); ?></th>
		</tr>
	</table>

	<table style="width: 450px; margin-right: 35px; display: inline;">
		<tr>
			<th colspan="2">פירוט לייקים</th>
		</tr>
		
		<?php
			foreach($likes_info as $likes)
			{
				echo '<tr>';
				echo '	<th>תגובות שקיבלו ' . $likes['likes'] . ' לייקים</th>';
				echo '	<th>' . number_format($likes['comments'], 0, '.', ',') . '</th>';
				echo '</tr>';
			}
		?>
	</table>
</div>