<?php

require_once("facebook.php");

class FacebookData
{
	private $facebook;
	private $users_data = array();
	private $conn;
	
	public function __construct()
	{
		$config = array(
		  'appId' => '410035305789891',
		  'secret' => 'e6743b7c4ef7d0894ebad91b38c639e9',
		  'fileUpload' => false, // optional
		  'allowSignedRequest' => false, // optional, but should be set to false for non-canvas apps
		);

		$this->facebook = new Facebook($config);
		
		if(!$this->user = $this->facebook->getUser())
		{
			header('Location: ' . $this->facebook->getLoginUrl(array(
				"scope" => "read_stream, read_insights",
			)));
		}
		
		global $db_host;
		global $db_user;
		global $db_password;
		global $db_name;
		
		$this->conn = mysql_connect($db_host, $db_user, $db_password);
		mysql_select_db($db_name, $this->conn);
		
		mysql_set_charset('utf8', $this->conn);
	}
	
	public function getAllComments()
	{
		set_time_limit(0);
		
		$post_id = "1324090839_253534891611";
		
		$first_comments = $this->facebook->api(array(
			'method' => 'fql.query',
			'query' => "SELECT text, likes, fromid, time, id FROM comment WHERE post_id IN ('$post_id') LIMIT 5000",
		));
		
		$last_comments = $this->facebook->api(array(
			'method' => 'fql.query',
			'query' => "SELECT text, likes, fromid, time, id FROM comment WHERE post_id IN ('$post_id') ORDER BY time DESC LIMIT 5000",
		));
		
		$comments = array_merge($first_comments, array_reverse($last_comments));
		
		$this->setUsersData($comments);
		
		return $comments;
	}
	
	public function setUsersData($comments)
	{
		foreach($comments as $comment_num => $comment)
		{
			$fromid = $comment['fromid'];
			
			if(!isset($this->users_data[$fromid]))
			{
				$user_name = $this->facebook->api(array(
			       'method' => 'fql.query',
			       'query' => "SELECT name, pic_small FROM user WHERE uid = $fromid",
			    ));
				
				$this->users_data[$fromid] = array(
					'user_name' => $user_name[0]['name'],
					'pic' => $user_name[0]['pic_small'],
				);
			}
		}
	}
	
	public function getSummary()
	{
		$comments = $this->getAllComments();
		
		$users = array();
		
		foreach($comments as $comment)
		{
			$uid = $comment['fromid'];
			
			$users[$uid]['comments']++;
			
			$users[$uid]['likes'] += $comment['likes'];
		}
		
		foreach($users as $uid => $user)
		{
			$percent = $user['comments'] / sizeof($comments) * 100;
			$users[$uid]['percent_of_all_comments'] = $percent;
		}
		
		return $users;
	}
	
	public function insertComments($comments)
	{
		$sql = "INSERT INTO comments(id, uid, text, likes, time) VALUES ";
		
		$inserted_comments = array();
		
		foreach($comments as $comment)
		{
			$comment_id = $comment['id'];
			if(isset($inserted_comments[$comment_id]))
			{
				//comment already inserted
				continue;
			}
			
			$sql .= "('" . $comment_id . "', ";
			$sql .= "'" . $comment['fromid'] . "', ";
			$sql .= "'" . mysql_real_escape_string($comment['text']) . "', ";
			$sql .= "'" . $comment['likes'] . "', ";
			$sql .= "'" . date('Y-m-d H:i:s', $comment['time']) . "'),";
			
			$inserted_comments[$comment_id] = $comment_id;
		}
		
		$sql = substr($sql, 0, -1);
		$sql .= ';';
		
		mysql_query($sql, $this->conn);
	}
	
	public function getNewComments()
	{
		set_time_limit(125);
		
		$post_id = "1324090839_253534891611";
		
		$first_comments = $this->facebook->api(array(
			'method' => 'fql.query',
			'query' => "SELECT text, likes, fromid, time, id FROM comment WHERE post_id IN ('$post_id') LIMIT 5000",
		));
		
		$last_comments = $this->facebook->api(array(
			'method' => 'fql.query',
			'query' => "SELECT text, likes, fromid, time, id FROM comment WHERE post_id IN ('$post_id') ORDER BY time DESC LIMIT 5000",
		));
		
		$comments = array_merge($first_comments, array_reverse($last_comments));
		
		$this->setUsersData($comments);
		
		return $comments;
	}
	
	public function insertNewComments()
	{
		set_time_limit(30);
		
		$post_id = "1324090839_253534891611";
		
		$new_comments = $this->facebook->api(array(
			'method' => 'fql.query',
			'query' => "SELECT text, likes, fromid, time, id FROM comment WHERE post_id IN ('$post_id') ORDER BY time DESC LIMIT 50",
		));
		
		$str = '';
		foreach($new_comments as $comment)
		{
			$str .= "'" . $comment['id'] . "',";
		}
		
		if(empty($new_comments))
		{
			return;
		}
		
		$str = substr($str, 0, -1);
		
		$sql = "DELETE FROM comments WHERE id IN ($str);";
		mysql_query($sql, $this->conn); //delete
		
		$this->insertComments($new_comments);
	}
	
	public function insertAllLikes()
	{
		$db_handler = new DbHandler();
		
		$comments = $this->getNonUpdatedComments();
		
		set_time_limit(0);
		foreach($comments as $comment_id)
		{
			$this->insertLikes($comment_id);
		}
	}
	
	//should be run by yoav because only he can get all likers uids
	private function insertLikes($comment_id)
	{
		$likes = $this->facebook->api(array(
			'method' => 'fql.query',
			'query' => "SELECT user_id FROM like WHERE post_id = '$comment_id'",
		));

		echo '<pre>';
		print_r($likes);
		echo '</pre><hr />';
		
		$sql = "DELETE FROM likes WHERE comment_id = '$comment_id';";
		mysql_query($sql, $this->conn); //delete old likes
		
		$sql = "INSERT INTO likes(comment_id, uid) VALUES ";
		foreach($likes as $user)
		{
			$sql .= "('" . $comment_id . "',";
			$sql .= "'" . $user['user_id'] . "'),";
		}
		
		if(!empty($likes))
		{
			$sql = substr($sql, 0, -1);
			if(!mysql_query($sql, $this->conn))
			{
				echo 'INSERT LIKES FAILED!<br>';
				print_r($sql);
				echo mysql_error($this->conn);
				exit;
			}
		}
		
		//TODO: update comments table's likes here with the new likes count
	}
	
	//gets all the comments that has out of date likes in likes table
	//meaning: comments.likes differs from likes table count
	private function getNonUpdatedComments()
	{
		$sql = "
			SELECT
				id
			FROM comments
			LEFT JOIN (
				SELECT
					comment_id,
					COUNT(*) AS likes_count
				FROM likes
				GROUP BY comment_id
			) AS likes ON likes.comment_id = comments.id

			WHERE comments.likes <> IFNULL(likes.likes_count, 0)
		";
		
		$result = mysql_query($sql, $this->conn);
		
		$comments = array();
		
		if(mysql_num_rows($result) > 0)
		{
			while($record = mysql_fetch_array($result))
			{
				$comments[$record['id']] = $record['id'];
			}
		}
		
		return $comments;
	}
}