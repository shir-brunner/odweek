<?php if(sizeof($users) > 0) { ?>

	<div id="general_stats">
		<table class="table_sorter" style="text-align: center;">
			<thead>
				<tr>
					<th style="width: 30px;">#</th>
					<th style="width: 50px;">תמונה</th>
					<th>מגיב</th>
					<th>תגובות</th>
					<th>לייקים (קיבל)</th>
					<th>לייקים (נתן)</th>
					<th>ממוצע לייקים לתגובה</th>
					<th>% מכלל התגובות</th>
				</tr>
			</thead>
			
			<tbody>
				<?php
					$sum_data = array('likes' => 0, 'comments' => 0);
					$counter = 0;
					
					foreach($users as $user)
					{
						$counter++;
						
						echo '<tr>';
						echo '<td>' . $counter . '</td>';
						echo '	<td><a href="?page=user&uid=' . $user['uid'] . '"><img style="width: 35px; height: 35px;" src="' . $user['picture'] . '" /></a></td>';
						echo '	<td>' . $user['user_name'] . '</td>';
						echo '	<td>' . number_format($user['comments'], 0, '.', ',') . '</td>';
						echo '	<td>' . number_format($user['likes'], 0, '.', ',') . '</td>';
						echo '	<td>' . number_format($user['likes_given'], 0, '.', ',') . '</td>';
						echo '	<td>' . number_format($user['likes'] / $user['comments'], 2, '.', ',') . '</td>';
						echo '	<td>' . number_format($user['percent_of_all_comments'], 2, '.', ',') . '%</td>';
						echo '</tr>';
						
						$sum_data['comments'] += $user['comments'];
						$sum_data['likes'] += $user['likes'];
					}
				?>
			</tbody>
			
			<tfoot>
				<tr>
					<th colspan="3"><b>סה"כ</b></th>
					<th><?php echo number_format($sum_data['comments'], 0, '.', ','); ?></th>
					<th><?php echo number_format($sum_data['likes'], 0, '.', ','); ?></th>
					<th></th>
					<th><?php echo number_format($sum_data['likes'] / $sum_data['comments'], 2, '.', ','); ?></th>
					<th>100%</th>
				</tr>
			</tfoot>
		</table>
	</div>
	
<?php } else { ?>

	<h1 style="margin-right: 30px;">אני מאוד מצטער אך לא נמצא מידע כלל...</h3>

<?php } ?>