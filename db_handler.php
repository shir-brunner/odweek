<?php

require_once("facebook.php");

class DbHandler
{
	private $facebook;
	private $users_data = array();
	private $conn;
	
	const QUERY_LIMIT = 10000;
	
	public function __construct()
	{
		set_time_limit(300);
		
		$config = array(
		  'appId' => '410035305789891',
		  'secret' => 'e6743b7c4ef7d0894ebad91b38c639e9',
		  'fileUpload' => false, // optional
		  'allowSignedRequest' => false, // optional, but should be set to false for non-canvas apps
		);

		/*$this->facebook = new Facebook($config);
		
		if(!$this->user = $this->facebook->getUser())
		{
			header('Location: ' . $this->facebook->getLoginUrl(array(
				"scope" => "read_stream, read_insights",
			)));
		}*/
		
		global $db_host;
		global $db_user;
		global $db_password;
		global $db_name;
		
		$this->conn = mysqli_connect($db_host, $db_user, $db_password);
        mysqli_select_db($this->conn, $db_name);
        mysqli_set_charset($this->conn, 'utf8');
	}

	public function getBestComments()
	{
		$sql = "SELECT * FROM comments WHERE likes > 2 ORDER BY likes DESC LIMIT " . self::QUERY_LIMIT;
		return $this->getComments($sql);
	}
		
	public function getAllComments()
	{
		$sql = "SELECT * FROM comments ORDER BY time ASC LIMIT " . self::QUERY_LIMIT;
		return $this->getComments($sql);
	}
	
	public function getAllUserComments($uid)
	{
		$uid = mysqli_real_escape_string($this->conn, $uid);
		
		$sql = "SELECT * FROM comments WHERE uid = '$uid' ORDER BY time ASC LIMIT " . self::QUERY_LIMIT;
		return $this->getComments($sql);
	}
	
	private function getComments($sql)
	{
		$users = array();
		$comments = array();
		
		$result = mysqli_query($this->conn, $sql);
		if(mysqli_num_rows($result) > 0)
		{
			while($record = mysqli_fetch_array($result))
			{
				$comments[$record['id']] = array(
					'uid' => $record['uid'],
					'text' => $record['text'],
					'likes' => $record['likes'],
					'time' => $record['time'],
				);
				
				$users[$record['uid']] = $record['uid'];
			}
		}
		
		$users_data = $this->getUsersDetails($users);
		
		foreach($comments as $comment_id => $comment)
		{
			$uid = $comment['uid'];
			$comments[$comment_id]['user_name'] = isset($users_data[$uid]) ? $users_data[$uid]['user_name'] : 'Unknown';
			$comments[$comment_id]['picture'] = isset($users_data[$uid]) ? $users_data[$uid]['picture'] : '';
		}
		
		return $comments;
	}
	
	public function getUsersDetails($users)
	{
		if(!empty($this->users_data) || empty($users))
		{
			return $this->users_data;
		}
		
		$users_str = implode(',', $users);

        $result = mysqli_query($this->conn, "SELECT * FROM users WHERE uid IN ($users_str)");
        if(mysqli_num_rows($result) > 0)
        {
            while($user = mysqli_fetch_array($result))
            {
                $this->users_data[$user['uid']] = array(
                    'user_name' => $user['name'],
                    'picture' => 'https://graph.facebook.com/' . $user['uid'] . '/picture?type=small',
                );
            }
        }

		return $this->users_data;
	}
	
	public function getUsersData()
	{
		$sql = "
			SELECT
				comments.uid,
				COUNT(*) AS comments,
				SUM(likes) AS likes,
				IFNULL(likes_table.likes_given, 0) AS likes_given
			FROM comments
			
			LEFT JOIN (
				SELECT
					likes.uid,
					COUNT(*) AS likes_given
				FROM likes
				GROUP BY likes.uid
			) AS likes_table ON likes_table.uid = comments.uid
			
			GROUP BY comments.uid
			
			ORDER BY comments DESC";
		
		$users = array();
		
		$result = mysqli_query($this->conn, $sql);
		if(mysqli_num_rows($result) > 0)
		{
			$comments_count = $this->getCommentsCount();
			
			while($record = mysqli_fetch_array($result))
			{
				$users[$record['uid']] = array(
					'uid' => $record['uid'],
					'comments' => $record['comments'],
					'likes' => $record['likes'],
					'likes_given' => $record['likes_given'],
					'percent_of_all_comments' => $record['comments'] / $comments_count * 100,
				);
			}
		}
		
		foreach($users as $user)
		{
			$uids_array[] = $user['uid'];
		}
		
		$users_data = $this->getUsersDetails($uids_array);
		
		foreach($users as $uid => $comment)
		{
			$users[$uid]['user_name'] = isset($users_data[$uid]) ? $users_data[$uid]['user_name'] : 'Unknown';
			$users[$uid]['picture'] = isset($users_data[$uid]) ? $users_data[$uid]['picture'] : '';
		}
		
		return $users;
	}
	
	public function getMonthlyData($uid = null)
	{
		$uid = mysqli_real_escape_string($this->conn, $uid);
		
		$sql = "
			SELECT
				MONTHNAME(time) AS month_name,
				DATE_FORMAT(time, '%Y-%m') AS month,
				YEAR(time) AS year,
				COUNT(*) AS comments,
				SUM(likes) AS likes
			FROM comments
			
			" . ($uid != null ? "WHERE uid = '$uid'" : "") . "
			GROUP BY YEAR(time), MONTH(time)
			
			ORDER BY time DESC";
		
		$months = array();
		
		$result = mysqli_query($this->conn, $sql);
		if(mysqli_num_rows($result) > 0)
		{
			$comments_count = $this->getCommentsCount();
			
			while($record = mysqli_fetch_array($result))
			{
				$months[] = array(
					'comments' => $record['comments'],
					'likes' => $record['likes'],
					'month_name' => $record['month_name'] . ' ' . $record['year'],
					'month' => $record['month'],
					'percent_of_all_comments' => $record['comments'] / $comments_count * 100,
				);
			}
		}
		
		return $months;
	}
	
	private function getCommentsCount()
	{
		$result = mysqli_query($this->conn, "SELECT COUNT(*) AS comments_count FROM comments");
		$record = mysqli_fetch_array($result);
		return $record['comments_count'];
	}
	
	private function getLikesCount()
	{
		$result = mysqli_query($this->conn, "SELECT SUM(likes) AS likes_count FROM comments");
		$record = mysqli_fetch_array($result);
		return $record['likes_count'];
	}
	
	public function getUser($uid)
	{
		$uid = mysqli_real_escape_string($this->conn, $uid);
		
		$sql = "
			SELECT
				COUNT(*) AS comments,
				SUM(likes) AS likes,
				MIN(time) AS first_comment_time,
				MAX(time) AS last_comment_time,
				AVG(CHAR_LENGTH(text)) AS avg_comment_length,
				MAX(CHAR_LENGTH(text)) AS longest_comment_length,
				MIN(CHAR_LENGTH(text)) AS shortest_comment_length,
				
				(
					SELECT COUNT(*) FROM likes WHERE uid = $uid
				) AS likes_given
			FROM comments
			WHERE uid = $uid
		";
		
		$user = array();
		
		$result = mysqli_query($this->conn, $sql);
		if(mysqli_num_rows($result) > 0)
		{
			$record = mysqli_fetch_array($result);
			$comments_count = $this->getCommentsCount();
			
			$user = array(
				'uid' => $uid,
				'user_name' => isset($this->users_data[$uid]) ? $this->users_data[$uid]['user_name'] : 'Unknown',
				'picture' => isset($this->users_data[$uid]) ? $this->users_data[$uid]['picture'] : '',
				'comments' => $record['comments'],
				'likes' => $record['likes'],
				'likes_given' => $record['likes_given'],
				'percent_of_all_comments' => $record['comments'] / $comments_count * 100,
				'first_comment_time' => $record['first_comment_time'],
				'last_comment_time' => $record['last_comment_time'],
				'avg_comment_length' => $record['avg_comment_length'],
				'longest_comment_length' => $record['longest_comment_length'],
				'shortest_comment_length' => $record['shortest_comment_length'],
			);
		}
		
		$user['likes_info'] = $this->getLikesInfo($uid);
		
		return $user;
	}
	
	public function getLikesInfo($uid = null)
	{
		$uid = mysqli_real_escape_string($this->conn, $uid);
		
		$sql = "
			SELECT
				likes,
				COUNT(*) AS comments
				
			FROM comments
			" . ($uid != null ? "WHERE uid = '$uid'" : "") . "
			
			GROUP BY likes
		";
		
		$likes_info = array();
		
		$result = mysqli_query($this->conn, $sql);
		if(mysqli_num_rows($result) > 0)
		{
			while($record = mysqli_fetch_array($result))
			{
				$likes_info[] = array(
					'likes' => $record['likes'],
					'comments' => $record['comments'],
				);
			}
		}
		
		return $likes_info;
	}
	
	public function getSummaryData()
	{
		$summary = array();
		
		$summary['comments'] = $this->getCommentsCount();
		$summary['likes'] = $this->getLikesCount();
		$summary['users_count'] = sizeof($this->users_data);
		$summary['longest_comment_user'] = $this->users_data[$this->getLongestCommentUser()]['user_name'];
		$summary['shortest_comment_user'] = $this->users_data[$this->getShortestCommentUser()]['user_name'];
		
		$sql = "
			SELECT
				MIN(time) AS first_comment_time,
				MAX(time) AS last_comment_time,
				AVG(CHAR_LENGTH(text)) AS avg_comment_length,
				MAX(CHAR_LENGTH(text)) AS longest_comment_length,
				MIN(CHAR_LENGTH(text)) AS shortest_comment_length
			FROM comments
		";
		
		$result = mysqli_query($this->conn, $sql);
		if(mysqli_num_rows($result) > 0)
		{
			$record = mysqli_fetch_array($result);
			
			$summary['first_comment_time'] = $record['first_comment_time'];
			$summary['last_comment_time'] = $record['last_comment_time'];
			$summary['avg_comment_length'] = $record['avg_comment_length'];
			$summary['longest_comment_length'] = $record['longest_comment_length'];
			$summary['shortest_comment_length'] = $record['shortest_comment_length'];
		}
		
		return $summary;
	}
	
	private function getLongestCommentUser()
	{
		$result = mysqli_query($this->conn, "SELECT uid FROM comments ORDER BY CHAR_LENGTH(text) DESC LIMIT 1");
		$record = mysqli_fetch_array($result);
		return $record['uid'];		
	}
	
	private function getShortestCommentUser()
	{
		$result = mysqli_query($this->conn, "SELECT uid FROM comments ORDER BY CHAR_LENGTH(text) ASC LIMIT 1");
		$record = mysqli_fetch_array($result);
		return $record['uid'];
	}
	
	public function getRandomComment()
	{
		$result = mysqli_query($this->conn, "SELECT uid, text FROM comments ORDER BY RAND() LIMIT 1");
		$record = mysqli_fetch_array($result);
		
		$users_data = $this->getUsersDetails(array($record['uid']));
		
		return array(
			'uid' => $record['uid'],
			'user_name' => isset($users_data[$record['uid']]) ? $users_data[$record['uid']]['user_name'] : 'Unknown',
			'picture' => isset($users_data[$record['uid']]) ? $users_data[$record['uid']]['picture'] : '',
			'text' => $record['text'],
		);
	}
	
	public function getLikesGiven($uid = null)
	{
		$sql = "
			SELECT uid, COUNT(*) AS likes
			FROM likes
			" . ($uid != null ? "WHERE uid = '$uid'" : "") . "
			GROUP BY uid
		";
		
		$users_likes = array();
		
		$result = mysql_query($sql, $this->conn);
		if(mysql_num_rows($result) > 0)
		{
			while($record = mysql_fetch_array($result))
			{
				$users_likes[$record['uid']] = array(
					'uid' => $record['uid'],
					'likes' => $record['likes'],
				);
			}
		}
		
		return $users_likes;
	}
	
	public function getCommentLikes($comment_id)
	{
		$comment_id = mysqli_real_escape_string($this->conn, $comment_id);
		
		$sql = "
			SELECT uid
			FROM likes
			WHERE comment_id = '$comment_id'
		";
		
		$result = mysqli_query($this->conn, $sql);
		if(mysqli_num_rows($result) == 0)
		{
			return array();
		}
		
		$users = array();
		
		while($record = mysqli_fetch_array($result))
		{
			$users[$record['uid']] = $record['uid'];
		}
		
		$users_data = $this->getUsersDetails($users);
		
		$res = array();
		
		foreach($users as $uid => $user)
		{
			$res[$uid]['user_name'] = isset($users_data[$uid]) ? $users_data[$uid]['user_name'] : 'Unknown';
			$res[$uid]['picture'] = isset($users_data[$uid]) ? $users_data[$uid]['picture'] : '';
		}
		
		return $res;
	}
}