<?php
/*
 * Created on 2011-9-4
 *
 * To change the template for $this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
class SSUser
{
	private $ssUid;
	private $ssName;
	private $ssProfileImage;
	private $ssFollowersCount;
	private $ssFriendsCount;
	private $ssTweetCount;
	private $ssLocation;
	private $ssCreatedAt;
	private $ssFriendArray;
	private $ssFollowerArray;
	private $ssVerified;
	private $ssJsonInfo;
	private $ssNow;
	private $index;
	
	/**
	 * 初始化用户信息，对于当前用户需要调用verify_credentials(),不包含 $ssFriendArray and $ssFollowerArray
	 */
	public function SSUser($msg)
	{
		$this->ssUid = $msg["id"];
		$this->ssName = $msg["name"];
		$this->ssFollowersCount = $msg["followers_count"];
		$this->ssFriendsCount = $msg["friends_count"];
		$this->ssTweetCount = $msg["statuses_count"];
		$this->ssLocation = $msg["location"];
		$this->ssCreatedAt = $msg["created_at"];
		$this->ssProfileImage = $msg["profile_image_url"];
		$this->ssVerified = $msg["verified"];
		$this->ssJsonInfo = json_encode($msg);
		
			$reg = strtotime($msg["created_at"]);
			$follow_temp = $msg["followers_count"];
			if($follow_temp > 500)
			{
				$follow_temp = 500 + ($follow_temp-500)/2;
				$follow_temp = $follow_temp>1000?1000:$follow_temp;
			}
			
		$this->index =  floor((date("U") - $reg)/86400)+$msg["friends_count"]*2
				+$follow_temp+$msg["statuses_count"];
		
	}
	
	public function getUid()
	{
		return $this->ssUid;
	}

	public function setNow()
	{
		$this->ssNow = date("U");
	}

	public function showBasicInfo()
	{
		return sprintf("<div><img src='%s'/> %s(%s) Location:%s Followers(%u) Friends(%u) Tweet(%u) → %u</div>",
			$this->ssProfileImage,$this->ssName,$this->ssUid,$this->ssLocation,$this->ssFollowersCount,
			$this->ssFriendsCount,$this->ssTweetCount,$this->index);
	}
	
	//指数
	public function getUserIndex($p_user)
	{
		if(!$p_user["verified"])
		{
			$reg = strtotime($p_user["created_at"]);
			$follow_temp = $p_user["followers_count"];
			if($follow_temp > 500)
			{
				$follow_temp = 500 + ($follow_temp-500)/2;
				$follow_temp = $follow_temp>1000?1000:$follow_temp;
			}
			return floor(($this->ssNow - $reg)/86400)+$p_user["friends_count"]*2
				+$follow_temp+$p_user["statuses_count"];
		}
		else
			return 0;
	}

	public function updateUser()
	{
		$mysql = new SaeMysql();
		$sql_uid = $mysql->escape($this->ssUid);
		$sql_location = $mysql->escape($this->ssLocation);
		$sql_profileImage = $mysql->escape($this->ssProfileImage);
		$sql_json = $mysql->escape($this->ssJsonInfo);
		$sql_create = $mysql->escape($this->ssCreatedAt);
		
		$sql_followers = intval($this->ssFollowersCount);
        $sql_friends = intval($this->ssFriendsCount);
        $sql_tweets = intval($this->ssTweetCount);
        $sql_verified = intval($this->ssVerified);
		
		$sql = "select 'abc' from users where uid = '" . $sql_uid . "'";
		$data = $mysql->getLine( $sql );
		if( $mysql->errno() != 0 )
    	{
        	die( "Error:" . $mysql->errmsg() );
    	}
    	
		$uidExisted = false;
		foreach($data as $ret)
		{
			if($ret == "abc")
				$uidExisted = true;
		}
		if($uidExisted)
		{
			$sql = sprintf("update users" .
					" set followersCount = %u," .
					" friendsCounr = %u," .
					" tweetCount = %u," .
					" location = '%s'," .
					" profileImage = '%s'," .
					" verified = %u," .
					" json_info ='%s'," .
					" login_times = login_times + 1" .
					" where uid = '%s'",
        	$sql_followers,
        	$sql_friends,
        	$sql_tweets,
        	$sql_location,
        	$sql_profileImage,
        	$sql_verified,
        	$sql_json,
        	$sql_uid);
		}
		else
		{
			$sql = "insert into users(uid,followersCount,friendsCounr,tweetCount,location,createAt," .
					" profileImage,verified,json_info,login_times)values" .
					" ('". $sql_uid . "'," . $sql_followers . ",". $sql_friends ."," . $sql_tweets . ",'". $sql_location ."'," .
            		"'" . $sql_create . "', '".$sql_profileImage."',".$sql_verified.",'".$sql_json."',1)";
		}
		//echo $sql; 
		$mysql->runSql( $sql );
		if( $mysql->errno() != 0 )
	    {
	       	die( "Error:" . $mysql->errmsg() );
	       	$this->takeLog("update user failed:$sql");
	    }
    	$mysql->closeDb();
	}
	
	public function getFriendIDs($p_c, $p_uid = NULL)
	{
		/*array(3) 
		 *{
		 *	 ["ids"]=> array(2) { [0]=> int(1651556533) [1]=> int(2315899420) }
		 *	 ["next_cursor"]=> int(10) 
		 *	 ["previous_cursor"]=> int(0) 
		 *}
		 *friends_ids( $cursor = NULL , $count = 500 , $uid_or_name = NULL )
		 */		
		//检查上一次存储的信息，如果大于两个小时则刷新数据
		$mysql = new SaeMysql();
		if ( $p_uid != NULL )
		{
			$uid = $p_uid;
		}
		else
		{
			$uid = $mysql->escape($this->ssUid);
		}
		$sql = "select now() > DATE_ADD(upd_dt, INTERVAL 2 HOUR) from relative_tree where followerBy = '". $uid ."' LIMIT 1";
		echo $sql;
		$data = $mysql->getLine( $sql );
		if( $mysql->errno() != 0 )
    	{
        	die( "Error:" . $mysql->errmsg() );
    	}
    	//var_dump($data);
    	$check_result = $data["now() > DATE_ADD(upd_dt, INTERVAL 2 HOUR)"];
    	//echo $check_result;
    	if($check_result=== NULL || $check_result==1)
    	{
			$getFriendIDs_cursor = -1;
			while($getFriendIDs_cursor != 0)
			{
				$tempids = $p_c->friends_ids($getFriendIDs_cursor,500,$uid);
				if ($tempids === false || $tempids === null){
					echo "Error occured";
					return false;
				}
				if (isset($tempids['error_code']) && isset($tempids['error'])){
					echo ('Error_code: '.$tempids['error_code'].';  Error: '.$tempids['error'] );
					return false;
				}
				$getFriendIDs_cursor = $tempids["next_cursor"];
				$sql=" DELETE FROM relative_tree WHERE followerBy = '". $uid ."';" ;
				$mysql->runSql( $sql );
				if( $mysql->errno() != 0 )
			    {
			       	die( "Error:" . $mysql->errmsg() );
			       	return false;
			    }
				$sql=" insert into relative_tree (uid, followerBy) values ";
				foreach($tempids["ids"] as $fid)
				{
					$sql=$sql . "('". $mysql->escape($fid) ."','". $uid ."'),";
					$this->ssFriendArray[]=$fid;
				}
				$sql=substr($sql,0,strlen($sql)-1);
				//echo $sql;
				$mysql->runSql( $sql );
				if( $mysql->errno() != 0 )
			    {
			       	die( "Error:" . $mysql->errmsg() );
			       	return false;
			    }
			}
			$this->takeLog("friend ids set up successfully.");
    	}
    	else
		{
			$this->takeLog("friend ids hit in Mysql cache.");
		}
    	$mysql->closeDb();
    	
    	return true;
	}
	
	public function getFollowerIDs($p_c, $p_uid = NULL)
	{
		/*array(3) 
		 *{
		 *	 ["ids"]=> array(2) { [0]=> int(1651556533) [1]=> int(2315899420) }
		 *	 ["next_cursor"]=> int(10) 
		 *	 ["previous_cursor"]=> int(0) 
		 *}
		 *friends_ids( $cursor = NULL , $count = 500 , $uid_or_name = NULL )
		 */		
		//检查上一次存储的信息，如果大于两天则刷新数据
		$mysql = new SaeMysql();
		if ( $p_uid != NULL )
		{
			$uid = $p_uid;
		}
		else
		{
			$uid = $mysql->escape($this->ssUid);
		}
		$sql = "select now() > DATE_ADD(upd_dt, INTERVAL 2 DAY) from relative_tree where uid = '". $uid ."' LIMIT 1";
		echo $sql;
		$data = $mysql->getLine( $sql );
		if( $mysql->errno() != 0 )
    	{
        	die( "Error:" . $mysql->errmsg() );
    	}
    	//var_dump($data);
    	$check_result = $data["now() > DATE_ADD(upd_dt, INTERVAL 2 HOUR)"];
    	//echo $check_result;
    	if($check_result=== NULL || $check_result==1)
    	{
			$getFollowerIDs_cursor = -1;
			while($getFollowerIDs_cursor != 0)
			{
				$tempids = $p_c->followers_ids($getFollowerIDs_cursor,500,$uid);
				if ($tempids === false || $tempids === null){
					echo "Error occured";
					return false;
				}
				if (isset($tempids['error_code']) && isset($tempids['error'])){
					echo ('Error_code: '.$tempids['error_code'].';  Error: '.$tempids['error'] );
					return false;
				}
				$getFriendIDs_cursor = $tempids["next_cursor"];
				$sql=" DELETE FROM relative_tree WHERE uid = '". $uid ."';" ;
				$mysql->runSql( $sql );
				if( $mysql->errno() != 0 )
			    {
			       	die( "Error:" . $mysql->errmsg() );
			       	return false;
			    }
				$sql=" insert into relative_tree (uid, followerBy) values ";
				foreach($tempids["ids"] as $fid)
				{
					$sql=$sql . "('". $uid ."','". $mysql->escape($fid) ."'),";
					if ( $p_uid == NULL )
					{
						$this->ssFollowerArray[] = $fid;
					}
				}
				$sql=substr($sql,0,strlen($sql)-1);
				//echo $sql;
				$mysql->runSql( $sql );
				if( $mysql->errno() != 0 )
			    {
			       	die( "Error:" . $mysql->errmsg() );
			       	return false;
			    }
			}
			$this->takeLog("follower ids set up successfully.");
    	}
    	else
		{
			$this->takeLog("follower ids hit in Mysql cache.");
		}
    	$mysql->closeDb();
    	
    	return true;
	}

	public function findPotentialFriends($p_c)
	{
		//获取Friends的F_Friends，如果我的Followers在F_Friends中，但是不再我的Friends中，则推荐。
		$friArray = $this->ssFriendArray;
		if($this->ssFriendArray===NULL)
		{
			$mysql = new SaeMysql();
			
			$mysql->closeDb();
		}
		foreach($friArray as $f)
		{
			$this->getFriendIDs($p_c,$f);
		}
	}
	
	public function takeLog($msglog)
	{
		if($this->ssUid == 1620231873)
		{
			echo ("<p>$msglog</p>");
		}
	}
	
	//获取Friend详细信息
	function getFriendsInfo($p_c)
	{
		//获取Friends ID等
		$go = true;
		//$this->ssFriendArray = array("a" => 0, "b" => 0, "c" => 0, "d" => 0, "e" => 0, 
		//							"f"=>0, "g"=>0, "h"=>0, "i"=>0, "j"=>0, "k"=>0);
		//将自己加入到Array中。
		$this->ssFriendArray=array("@". $this->ssName =>$this->index);
		$nextcursor = -1;
		while($nextcursor != 0)
		{
			$u_id = $this->ssUid;
			$msg = $p_c->friends($nextcursor, 50, $u_id);
			if ($msg === false || $msg === null){
				echo "Error occured";
				return false;
			}
			if (isset($msg['error_code']) && isset($msg['error'])){
				echo ('Error_code: '.$msg['error_code'].';  Error: '.$msg['error'] );
				return false;
			}			
			foreach($msg as $users)
			{
				if(is_array($users))
				{
					foreach ($users as $friend)
					{
						/*
						 * 只保留TOP10用户
						//$friends[] = new SSUser($friend);
						arsort($this->ssFriendArray);
						$temp_index = $this->getUserIndex($friend);
						//echo $friend["id"] . "指数：".$temp_index.";<br/>";
						if(end($this->ssFriendArray)<$temp_index)
						{
							array_pop($this->ssFriendArray);
							$this->ssFriendArray["@".$friend["name"]] = $temp_index;
							//echo $friend["id"]. $friend["name"] . " added ".$temp_index.";<br/>";
						}
						*/
						//保留所有用户，全部放在array中
						$temp_index = $this->getUserIndex($friend);
						$this->ssFriendArray["@".$friend["name"]] = $temp_index;
						//echo $friend["id"]. $friend["name"] . " --- ".$temp_index.";<br/>";
					}
				}
				else
				{
					if(!empty($users["id"]))
					{
						//$friends[] = new SSUser($users);
					}
				}
			}
			$nextcursor = $msg["next_cursor"];
		}
		arsort($this->ssFriendArray);
		return $this->ssFriendArray;
	}
	
	
		//获取Follower详细信息
	function getFollowersInfo($p_c)
	{
		//获取Friends ID等
		$go = true;
		$this->ssFollowerArray = array("a" => 0, "b" => 0, "c" => 0, "d" => 0, "e" => 0, 
									"f"=>0, "g"=>0, "h"=>0, "i"=>0, "j"=>0, "k"=>0);
		$nextcursor = -1;
		while($nextcursor != 0)
		{
			$u_id = $this->ssUid;
			$msg = $p_c->followers($nextcursor, 100, $u_id);
			if ($msg === false || $msg === null){
				echo "Error occured";
				return false;
			}
			if (isset($msg['error_code']) && isset($msg['error'])){
				echo ('Error_code: '.$msg['error_code'].';  Error: '.$msg['error'] );
				return false;
			}
			//$jsmsg = json_encode($msg);
			//$recodemsg = json_decode($jsmsg,true);
			
			foreach($msg as $users)
			{
				if(is_array($users))
				{
					foreach ($users as $follower)
					{
						arsort($this->ssFollowerArray);
						$temp_index = $this->getUserIndex($follower);
						//echo $follower["id"] . "指数：".$temp_index.";<br/>";
						if(end($this->ssFollowerArray)<$temp_index)
						{
							array_pop($this->ssFollowerArray);
							$this->ssFollowerArray["@".$follower["name"]] = $temp_index;
							//echo $follower["id"]. $follower["name"] . " added ".$temp_index.";<br/>";
						}
					}
				}
				else
				{
					if(!empty($users["id"]))
					{
						//$friends[] = new SSUser($users);
					}
				}
			}
			$nextcursor = $msg["next_cursor"];
		}
		arsort($this->ssFollowerArray);
		return $this->ssFollowerArray;
	}
	
	
	/*
	 *     <?php
    $mysql = new SaeMysql();
     
    $sql = "SELECT * FROM `user` LIMIT 10";
    $data = $mysql->getData( $sql );
    $name = strip_tags( $_REQUEST['name'] );
    $age = intval( $_REQUEST['age'] );
    $sql = "INSERT  INTO `user` ( `name` , `age` , `regtime` ) VALUES ( '"  . $mysql->escape( $name ) . "' , '" . intval( $age ) . "' , NOW() ) ";
    $mysql->runSql( $sql );
    if( $mysql->errno() != 0 )
    {
        die( "Error:" . $mysql->errmsg() );
    }
     
    $mysql->closeDb();
    ?>

	 * 
	 * */
	
} 
 
?>
