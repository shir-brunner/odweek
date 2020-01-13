<?php
	require_once("config.php");
	require_once("db_handler.php");
	require_once("helper_functions.php");
	
	require_once("facebook_data.php");
	
	if(isset($_GET['page']))
	{
		$facebook = new FacebookData();
		//$facebook->insertNewComments();
		
		$db_handler = new DbHandler();
		$users = $db_handler->getUsersData();
	}
	
	//command to import ALL comments from facebook to db
	//$facebook->insertComments($facebook->getAllComments());
	//exit;
	
/*	$facebook->insertAllLikes();
	exit;*/
?>

<html dir="rtl">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		
		<link rel="stylesheet" type="text/css" href="css/main.css">
		<link rel="stylesheet" type="text/css" href="scripts/tablesorter/style.css">
		<link rel="stylesheet" type="text/css" href="scripts/jui/themes/base/jquery-ui.css">
		
		<script language="javascript" src="scripts/jquery-1.8.3.min.js"></script>
		<script language="javascript" src="scripts/jui/ui/jquery-ui.js"></script>
		<script language="javascript" src="scripts/tablesorter/tsort.js"></script>
		<script language="javascript" src="scripts/qtip/jquery.qtip-1.0.0-rc3.min.js"></script>
		<script language="javascript" src="scripts/tablePaging.js"></script>
	</head>
	
	<body>
		<div id="top_bar">
			עוד שבוע של הגנה על המולדת
		</div>
		
		<div id="content">
			<div style="padding: 20px;">
				<?php
					switch($_GET['page'])
					{
						case 'user':
							require_once("user.php");
							break;
							
						case 'charts':
							require_once("charts.php");
							break;
							
						case 'all_comments':
							require_once("all_comments.php");
							break;
							
						case 'best_comments':
							require_once("best_comments.php");
							break;
							
						case 'monthly':
							require_once("monthly.php");
							break;
							
						case 'all_users':
							require_once("all_users.php");
							break;
							
						case 'summary':
							require_once("summary.php");
							break;
							
						default:
							require_once("welcome.php");
							break;
					}
				?>
			</div>
		</div>
		
		<div id="menu">
			<ul>
				<li class="menu_button <?php echo $_GET['page'] == 'all_comments' ? 'active' : ''; ?>"><a href="?page=all_comments">כל התגובות</a></li>
				<li class="menu_button <?php echo $_GET['page'] == 'best_comments' ? 'active' : ''; ?>"><a href="?page=best_comments">המוצלחות</a></li>
				<li class="menu_button <?php echo $_GET['page'] == 'all_users' ? 'active' : ''; ?>"><a href="?page=all_users">לפי מגיב</a></li>
				<li class="menu_button <?php echo $_GET['page'] == 'monthly' ? 'active' : ''; ?>"><a href="?page=monthly">מידע חודשי</a></li>
				<li class="menu_button <?php echo $_GET['page'] == 'summary' ? 'active' : ''; ?>"><a href="?page=summary">מידע כללי</a></li>
				<li class="menu_button <?php echo $_GET['page'] == 'charts' ? 'active' : ''; ?>"><a href="?page=charts">תרשימים</a></li>
				
				<?php if(!empty($users)) { ?>
					<li id="menu_button_random" class="menu_button">ערך אקראי</li>
					<li id="menu_button_users" class="menu_button <?php echo $_GET['page'] == 'user' ? 'active' : ''; ?>" closed="1">מגיבים <img id="users_expand_button" class="menu_expand_down" src="images/expand_down.png" /></li>
					<ul id="menu_users" class="menu_button">
						<?php
							foreach($users as $user)
							{
								echo '<a href="?page=user&uid=' . $user['uid'] . '">';
								echo '	<li>';
								echo '		<img class="menu_user_image" src="' . $user['picture'] . '" />';
								echo '		<span class="menu_user_name">' . strtok($user['user_name'], " ") . '</span>';
								echo '	</li>';
								echo '</a>';
							}
						?>
					</ul>
				<?php } ?>
			</ul>
		</div>
		
		<div id="random_comment_dialog" style="display: none;"></div>
	</body>
</html>

<script type="text/javascript">
	$(document).ready(function() {
		$('table.table_sorter').tableSort();
		$('.tablePaging').oneSimpleTablePagination({
			rowsPerPage: 10,
			topNav: true
		});
		
		$('#menu').css('height', $('#content').height());
		$('#menu_users').css('height', $('#content').height() - 410);
		
		$("#menu_users").hide();
		
		$('#menu_button_users').die().live('click', function() {
			var closed = $(this).attr('closed');
			
			if(closed == 1)
			{
				$("#menu_users").slideDown(function() {
					$("#users_expand_button").attr('src', 'images/expand_up.png');
					$('#menu_button_users').attr('closed', 0);
				});
			}
			else
			{
				$("#menu_users").slideUp(function() {
					$("#users_expand_button").attr('src', 'images/expand_down.png');
					$('#menu_button_users').attr('closed', 1);
				});
			}
		});
		
		$("#menu_button_random").die().live('click', function() {
			$("#random_comment_dialog").dialog({
	            modal: true,
				draggable: false,
				title: "הלכתי להביא תגובה...",
				width: 650,
	            buttons: {
	                "סגור": function () {
	                    $(this).dialog("close");
	                },
	                "עוד": function () {
	                    getRandomComment();
	                },					
	            }
			});
			
			getRandomComment();
		});
		
		var qtip_params = {
			show: { when: { event: 'click' }, delay: 0, solo: false, effect: { length: 0 }},
			hide: { when: { event: 'unfocus' } },
			position: {
			  corner: {
			     tooltip: "bottomMiddle",
			     target: "topMiddle"
			  }
			},
		   
			style: {
			  border: {
			     width: 1,
			     radius: 5
			  },
			  padding: 7, 
			  textAlign: 'center',
			  tip: true,
			  name: 'dark'
			},

			content:{
				url:  "ajax/comment_likes.php",
				method: 'post',
				text: '<div><img src="images/loader.gif" /></div>'
			}
		};
		
		$(".likes_details").each(function() {
			var comment_id = $(this).attr('comment_id');
			qtip_params.content.data = {comment_id: comment_id};
			
			if(parseInt($(this).html()) > 0)
			{
				$(this).qtip(qtip_params);
			}
		});
	});
	
	function getRandomComment()
	{
		var loading_html = '<img style="margin-top: 30px;" src="images/loader.gif" />';
		$("#random_comment_dialog")
			.html(loading_html)
			.css('text-align', 'center');
		
		$(".ui-dialog-title").html('הלכתי להביא תגובה...');
		
	    $.ajax({
	        url: 'ajax/random_comment.php',
			type: "GET",
	        dataType: "text",
			cache: false,
	        success: function(data) {
				$("#random_comment_dialog").html(data);
	        }
	    });
	}
</script>