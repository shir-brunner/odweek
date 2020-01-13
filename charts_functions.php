<?php

	function formatMonthlyGraphData($months)
	{
		$monthly_comments_js = '[';
		$monthly_likes_js = '[';
		foreach($months as $month)
		{
			$timestamp = DateTime::createFromFormat('Y-m', $month['month'])->getTimestamp() * 1000;
			$monthly_comments_js .= '[' . $timestamp . ', ' . $month['comments'] . '],';
			$monthly_likes_js .= '[' . $timestamp . ', ' . $month['likes'] . '],';
		}
		
		if(!empty($months))
		{
			$monthly_comments_js = substr($monthly_comments_js, 0, -1); //removes last ','
			$monthly_likes_js = substr($monthly_likes_js, 0, -1); //removes last ','
		}
		
		$monthly_comments_js .= ']';
		$monthly_likes_js .= ']';
		
		return array(
			'comments_js' => $monthly_comments_js,
			'likes_js' => $monthly_likes_js,
		);
	}
	
	function formatUsersGraphData($users)
	{
		$users_comments_js = '[';
		$users_likes_js = '[';
		
		$counter = 0;
		foreach($users as $user)
		{
			$counter++;
			$users_comments_js .= "[" . $counter . ", " . $user['comments'] . "],";
			$users_likes_js .= "[" . $counter . ", " . $user['likes'] . "],";
		}
		
		if(!empty($users))
		{
			$users_comments_js = substr($users_comments_js, 0, -1); //removes last ','
			$users_likes_js = substr($users_likes_js, 0, -1); //removes last ','
		}
		
		$users_comments_js .= ']';
		$users_likes_js .= ']';
		
		return array(
			'comments_js' => $users_comments_js,
			'likes_js' => $users_likes_js,
		);
	}
	
	function formatUsersLikesGraphData($users)
	{
		$users_likes_earned_js = '[';
		$users_likes_given_js = '[';
		
		$counter = 0;
		foreach($users as $user)
		{
			$counter++;
			$users_likes_earned_js .= "[" . $counter . ", " . $user['likes'] . "],";
			$users_likes_given_js .= "[" . $counter . ", " . $user['likes_given'] . "],";
		}
		
		if(!empty($users))
		{
			$users_likes_earned_js = substr($users_likes_earned_js, 0, -1); //removes last ','
			$users_likes_given_js = substr($users_likes_given_js, 0, -1); //removes last ','
		}
		
		$users_likes_earned_js .= ']';
		$users_likes_given_js .= ']';
		
		return array(
			'likes_earned_js' => $users_likes_earned_js,
			'likes_given_js' => $users_likes_given_js,
		);
	}
	
	function formatUsersGraphTicks($users)
	{
		$ticks_js = '[';
		
		$counter = 0;
		foreach($users as $user)
		{
			$counter++;
			$ticks_js .= "['" . $counter . "', '" . $counter . "'],";
		}
		
		$ticks_js = substr($ticks_js, 0, -1); //removes last ','
		
		$ticks_js .= ']';
		
		return $ticks_js;
	}
	
	function formatPicturesArray($users)
	{
		$pics_js = 'var users_pics = new Array();';
		
		$counter = 0;
		foreach($users as $user)
		{
			$counter++;
			$pics_js .= 'users_pics[' . $counter . '] = "' . $user['picture'] . '";';
		}
		
		return $pics_js;
	}
	
	function formatUserNamesArray($users)
	{
		$users_names_js = 'var users_names = new Array();';
		
		$counter = 0;
		foreach($users as $user)
		{
			$counter++;
			$users_names_js .= 'users_names[' . $counter . '] = "' . $user['user_name'] . '";';
		}
		
		return $users_names_js;
	}