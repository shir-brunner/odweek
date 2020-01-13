<table class="table_sorter tablePaging">
	<thead>
		<tr>
			<th style="width: 30px;">#</th>
			<th style="width: 50px;">תמונה</th>
			<th>תגובה</th>
			<th style="width: 40px;">לייקים</th>
			<th style="width: 150px;">מגיב</th>
		</tr>
	</thead>
	
	<tbody>
		<?php
		    $counter = 0;
			foreach($db_handler->getBestComments() as $comment_id => $comment)
			{
				$counter++;
				
				echo '<tr>';
				
				echo '	<td style="text-align: center;">' . $counter . '</td>';
				echo '	<td style="text-align: center;"><a href="?page=user&uid=' . $comment['uid'] . '"><img style="width: 35px; height: 35px;" src="' . $comment['picture'] . '" /></a></td>';
				echo '	<td>' . nl2br($comment['text']) . '</td>';
				echo '	<td style="text-align: center;"><div class="likes_details" comment_id="' . $comment_id . '">' . $comment['likes'] . '</div></td>';
				echo '	<td style="text-align: center;">' . $comment['user_name'] . '</td>';
				
				echo '</tr>';
			}
		?>
	</tbody>
</table>