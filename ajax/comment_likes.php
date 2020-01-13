<?php
	require_once("../config.php");
	require_once("../db_handler.php");
	
	$db_handler = new DbHandler();
	$users = $db_handler->getCommentLikes($_REQUEST['comment_id']);
?>

<table>
	<?php
		foreach($users as $user)
		{
			echo '<tr>';
			
			echo '	<td><img style="width: 30px; height: 30px;" src="' . $user['picture']. '" /></td>';
			echo '	<td style="font-family: arial; color: white; padding-right: 10px;">' . $user['user_name'] . '</td>';
			
			echo '</tr>';
		}
	?>
</table>