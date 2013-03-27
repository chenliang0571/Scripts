<?php
session_start();

include_once( 'config.php' );
include_once( 'saet2.ex.class.php' );
include_once( 'model.php' );

//从POST过来的signed_request中提取oauth2信息
if(!empty($_REQUEST["signed_request"])){
	$o = new SaeTOAuth( WB_AKEY , WB_SKEY  );
	$data=$o->parseSignedRequest($_REQUEST["signed_request"]);
	if($data=='-2'){
		 die('签名错误!');
	}else{
		$_SESSION['oauth2']=$data;
	}
}
//判断用户是否授权
if (empty($_SESSION['oauth2']["user_id"])) {
		include "auth.php";
		exit;
} else {
		$c = new SaeTClient( WB_AKEY , WB_SKEY ,$_SESSION['oauth2']['oauth_token'] ,'' );
} 

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<script src="http://tjs.sjs.sinajs.cn/t35/apps/opent/js/frames/client.js" language="JavaScript"></script>
<script src="ss.js" language="JavaScript"></script>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="see.css" />
<title>授权后的页面</title>
</head>
<body>

<?php
$msg = $c->verify_credentials();
if ($msg === false || $msg === null){
	echo "Error occured in verify_credentials";
	return false;
}
if (isset($msg['error_code']) && isset($msg['error'])){
	echo ('Error_code: '.$msg['error_code'].';  Error: '.$msg['error'] );
	return false;
}
if (isset($msg['name'])){
	$reg_user = new SSUser($msg);
	echo $reg_user->showBasicInfo();
}
// update connected user info.
if($_SESSION['views'] != "updated")
{
	$reg_user->updateUser();
	$_SESSION['views'] = "updated";
}
$_SESSION["LogonUser"] = $msg;
/*
$fri_arr=$reg_user->getFriendsInfo($c);
		$fri_keys=array_keys($fri_arr);
		$fri_values=array_values($fri_arr);
		$count = (count($fri_arr)>50)?50:count($fri_arr);
		echo "draw". count($fri_arr)."count:".$count;
		echo "<table><tr><td>好友</td><td>微博控指数</td></tr>";
		for($i=0; $i<10;$i++)
		{
			echo ("<tr>");
				echo ("<td>"."$fri_keys[$i]"."</td>");
				echo ("<td>"."$fri_values[$i]"."</td>");
			echo ("<tr/>");
		}
	    echo "</table>";
	    echo ("<div id='postmsg1'>&nbsp;&nbsp;<input type='image' src='share_button_m.gif' onclick='postTweet()'/></div>");
*/
//$foll_arr=$reg_user->getFollowersInfo($c);
//$foll_keys=array_keys($foll_arr);
//$foll_values=array_values($foll_arr);

?>
<br/>
<p align="center">
	<input type="button" id="friBtn" value="查看好友微博控指数" onclick="getFriendIndex()" />
	<input type="button" id="folBtn" value="查看粉丝微博控指数" onclick="getFollowerIndex()" />
	<div id="status"></div>
</p>
<!--
<hr/><br/>
<p>
	<h2>发送新微博</h2>
	<form action="" >
		<input type="text" name="text" style="width:300px" />
		&nbsp;<input type="submit" text="发送" />
	</form>
</p>
-->
<br/>


<?php
/*
if( isset($_REQUEST['text']) )
{
$c->update( $_REQUEST['text'] );
// 发送微博
echo "<p>发送完成</p>";
}
*/

//$reg_user->getFriendIDs($c);
//$reg_user->getFollowerIDs($c);


if($reg_user->getUid() == "1620231873")
{
	$rate = $c->rate_limit_status();
	if ($rate === false || $rate === null){
		echo "Error occured rate_limit_status: ";
		var_dump($rate);
		return false;
	}
	if (isset($rate['error_code']) && isset($rate['error'])){
		echo ('Error_code: '.$rate['error_code'].';  Error: '.$rate['error'] );
		return false;
	}
	if (isset($rate['hourly_limit']) )
	{
		echo ("<br/><br/><br/><b> API Limit </b><br/>");
		echo ("hourly_limit ".$rate["hourly_limit"]."<br/>");
		echo ("reset_time_in_seconds ".$rate["reset_time_in_seconds"]."<br/>");
		echo ("reset_time ".$rate["reset_time"]."<br/>");
		echo ("remaining_hits ".$rate["remaining_hits"]."<br/>");
	}
}
?>

</body>
</html>
