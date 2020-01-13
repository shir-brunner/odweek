<?php
	$months = $db_handler->getMonthlyData();
?>

<?php if(sizeof($months) > 0) { ?>

	<div id="general_stats">
		<table class="table_sorter" style="text-align: center;">
			<thead>
				<tr>
					<th>חודש</th>
					<th>תגובות</th>
					<th>לייקים</th>
					<th>ממוצע לייקים לתגובה</th>
					<th>% מכלל התגובות</th>
				</tr>
			</thead>
			
			<tbody>
				<?php
					$sum_data = array('likes' => 0, 'comments' => 0);
					$counter = 0;
					
					foreach($months as $month)
					{
						echo '<tr>';
						echo '	<td data-sortAs="' . $month['month'] . '">' . $month['month_name'] . '</td>';
						echo '	<td>' . number_format($month['comments'], 0, '.', ',') . '</td>';
						echo '	<td>' . number_format($month['likes'], 0, '.', ',') . '</td>';
						echo '	<td>' . number_format($month['likes'] / $month['comments'], 2, '.', ',') . '</td>';
						echo '	<td>' . number_format($month['percent_of_all_comments'], 2, '.', ',') . '%</td>';
						echo '</tr>';
						
						$sum_data['comments'] += $month['comments'];
						$sum_data['likes'] += $month['likes'];
					}
				?>
			</tbody>
			
			<tfoot>
				<tr>
					<th><b>סה"כ</b></th>
					<th><?php echo number_format($sum_data['comments'], 0, '.', ','); ?></th>
					<th><?php echo number_format($sum_data['likes'], 0, '.', ','); ?></th>
					<th><?php echo number_format($sum_data['likes'] / $sum_data['comments'], 2, '.', ','); ?></th>
					<th>100%</th>
				</tr>
			</tfoot>
		</table>
	</div>
	
<?php } else { ?>

	<h1 style="margin-right: 30px;">אני מאוד מצטער אך לא נמצא מידע כלל...</h3>

<?php } ?>