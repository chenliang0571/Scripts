<?php
/*
 * Created on 2011-9-7
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
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
//echo "12356";
if (!empty($_SESSION["LogonUser"]))
{
	//echo $_REQUEST["type"];
	$msg = $_SESSION["LogonUser"];
	$reg_user = new SSUser($msg);
	$reg_user->setNow();
	if ($_REQUEST["type"] == "friend")
	{
		$fri_arr=$reg_user->getFriendsInfo($c);
		$fri_keys=array_keys($fri_arr);
		$fri_values=array_values($fri_arr);
		$count = (count($fri_arr)>18)?18:count($fri_arr);
		echo "<table><tr><td>";
		echo "<table><tr><td><b>好友</b></td><td><b>微博控指数</b></td></tr>";
		for($i=0; $i<$count;$i++)
		{
			echo ("<tr>");
				echo ("<td>"."$fri_keys[$i]"."</td>");
				echo ("<td>"."$fri_values[$i]"."</td>");
			echo ("<tr/>");
		}
	    echo "</table>" .
	    		"</td><td>&nbsp;&nbsp;</td><td valign='top'>";
	    echo "<div><textarea name='ttcontent' rows='3' cols='50' id='twt'>谁是微博控？http://apps.weibo.com/complex </textarea></div><br/>";
	    echo ("<div id='postmsg1'>&nbsp;&nbsp;<input type='image' src='share_button_m.gif' onclick='postTweetFir()'/></div>");
	    echo "</td></tr></table>";
	    $_SESSION["FriendsInfo"] = $fri_arr;
	}
	else if($_REQUEST["type"] == "follower")
	{	
		$foll_arr=$reg_user->getFollowersInfo($c);
		$foll_keys=array_keys($foll_arr);
		$foll_values=array_values($foll_arr);
		echo "<table><tr><td>";
		echo "<table><tr><td><b>粉丝</b></td><td><b>微博控指数</b></td></tr>";
		for($i=0; $i<count($foll_arr);$i++)
		{
			echo ("<tr>");
				echo ("<td>"."$foll_keys[$i]"."</td>");
				echo ("<td>"."$foll_values[$i]"."</td>");
			echo ("<tr/>");
		}
	    echo "</table>" .
	    		"</td><td>&nbsp;&nbsp;</td><td valign='top'>";
	    echo "<div><textarea name='ttcontent' rows='3' cols='50' id='twt'>谁是微博控？http://apps.weibo.com/complex </textarea></div><br/>";
	    echo ("<div id='postmsg2'><input type='image' name='submit' src='share_button_m.gif' onclick='postTweetFoll()'/></div>");
	    echo "</td></tr></table>";
	    $_SESSION["FollowersInfo"] = $foll_arr;
	}
	else if($_REQUEST["type"] == "postTweetFriend")
	{
		$tweet = $_REQUEST["tweet"];
		if( isset($tweet) )
		{
			$fri_arr= $_SESSION["FriendsInfo"];
			$count = (count($fri_arr)>18)?18:count($fri_arr);
			// Create the image
			$im = imagecreatetruecolor(250, $count*20+50);		
			$white = imagecolorallocate($im, 255, 255, 255);
			$black = imagecolorallocate($im, 0, 0, 0);
			$font = SAE_Font_MicroHei;
			imagefilledrectangle($im, 0, 0, 249, $count*20+49, $white);
			imagettftext($im, 12, 0, 10, 20, $black, $font, '好友 → 微博控指数'. $count1);
			imagettftext($im, 12, 0, 10, 40, $black, $font, '-----------------'. $count1);
			$i=0;
			foreach ($fri_arr as $key => $value)
		    {
		    	if($i >= $count)
				{
					break;
				}
				imagettftext($im, 12, 0, 10, 40+20*($i+1), $black, $font, $key . ' → ' . $value);
				$i++;
			}
			ob_start(); 
			imagepng($im);
			$ss = ob_get_contents();
			imagedestroy($im);
			ob_end_clean();
			$s = new SaeStorage();
			$pngurl=$s->write( 'img' , $reg_user->getUid().'.png' , $ss);
			//echo $pngurl . '<br/>';
			$msg=$c->upload( $tweet, $pngurl);
			if ($msg === false || $msg === null){
				echo "Error occured";
				return false;
			}
			if (isset($msg['error_code']) && isset($msg['error'])){
				echo ('Error_code: '.$msg['error_code'].';  Error: '.$msg['error'] );
				return false;
			} 
			echo "发送成功。";
			//echo($msg['id']." : ".$msg['text']." ".$msg["created_at"]);
		}
		else
		{
			echo "内容不能为空！";
		}
	}
	else if($_REQUEST["type"] == "postTweetFollow")
	{
		$tweet = $_REQUEST["tweet"];
		if( isset($tweet) )
		{
			
			$foll_arr=$_SESSION["FollowersInfo"];
			$count = count($foll_arr);		
			$foll_keys=array_keys($foll_arr);
			$foll_values=array_values($foll_arr);		
			// Create the image
			$im = imagecreatetruecolor(250, $count*20+50);		
			$white = imagecolorallocate($im, 255, 255, 255);
			$grey = imagecolorallocate($im, 128, 128, 128);
			$black = imagecolorallocate($im, 0, 0, 0);
			$font = SAE_Font_MicroHei;				
			imagefilledrectangle($im, 0, 0, 249, $count*20+49, $white);
			imagettftext($im, 12, 0, 10, 20, $black, $font, '粉丝 → 微博控指数');
			imagettftext($im, 12, 0, 10, 40, $black, $font, '-----------------');
			for($i=0; $i<$count;$i++)
			{
				imagettftext($im, 12, 0, 10, 40+20*($i+1), $black, $font, $foll_keys[$i] . ' → ' . $foll_values[$i]);
			}
			ob_start(); 
			imagepng($im);
			$ss = ob_get_contents();
			imagedestroy($im);
			ob_end_clean();		
			
			$s = new SaeStorage();
			$pngurl=$s->write( 'img' , $reg_user->getUid().'.png' , $ss);
			
			$msg=$c->upload( $tweet, $pngurl );
			//$msg=$c->upload( $tweet, "http://seesaw.sinaapp.com/share_button_m.gif" );
			if ($msg === false || $msg === null){
				echo "Error occured";
				return false;
			}
			if (isset($msg['error_code']) && isset($msg['error'])){
				echo ('Error_code: '.$msg['error_code'].';  Error: '.$msg['error'] );
				return false;
			} 
			//echo($msg['id']." : ".$msg['text']." ".$msg["created_at"]);
			echo "发送成功。";
		}
		else
		{
			echo "内容不能为空！";
		}
	}	
} 
?>
