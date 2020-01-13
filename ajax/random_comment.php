<?php
	require_once("../config.php");
	require_once("../db_handler.php");
	
	$db_handler = new DbHandler();
	$comment = $db_handler->getRandomComment();
?>

<p>
	<?php echo $comment['text']; ?>
</p>

<script language="javascript">
	$(document).ready(function() {
		var html = "<?php echo $comment['user_name']; ?>";
		html += '<div style="float: left;">';
		html += '	<img style="width: 25px; height: 25px;" src="<?php echo $comment["picture"]; ?>" />'
		html += '</div>';
		$(".ui-dialog-title").html(html);
	});
</script>