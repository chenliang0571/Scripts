<?php
/*
 * Created on 2011-10-6
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
*/
// Set the content-type
header('Content-Type: image/png');
//session_start();
if($_REQUEST["type"] == "friend")
{
	//if(!empty($_SESSION["FriendsInfo"]))
	{
		//print_r($_SESSION["FriendsInfo"]);
		$fri_arr= $_SESSION["FriendsInfo"];
		//$fri_arr= array("@東南努力變超人" => 3919.5, "@梁小蒜" => 3772, "@胡蓓蓓小妞" => 3295);
		$count = (count($fri_arr)>18)?18:count($fri_arr);
		//$count = 3;

		// Create the image
		$im = imagecreatetruecolor(250, $count*20+30);		
		$white = imagecolorallocate($im, 255, 255, 255);
		//$grey = imagecolorallocate($im, 128, 128, 128);
		$black = imagecolorallocate($im, 0, 0, 0);
		$font = SAE_Font_MicroHei;

		imagefilledrectangle($im, 0, 0, 249, $count*20+29, $white);
		imagettftext($im, 12, 0, 10, 20, $black, $font, '好友 → 微博控指数'. $count1);
		$i=0;
		foreach ($fri_arr as $key => $value)
	    {
			imagettftext($im, 12, 0, 10, 20+20*($i+1), $black, $font, $key . ' → ' . $value);
			$i++;
		}
		$ss=imagepng($im);
		
		imagedestroy($im);
	}
}
/*
else if($_REQUEST["type"] == "folllow")
{
	//if(!empty($_SESSION["FollowersInfo"]))
	{
		$foll_arr=$_SESSION["FollowersInfo"];
		$count = count($foll_arr);		
		$foll_keys=array_keys($foll_arr);
		$foll_values=array_values($foll_arr);		
		// Create the image
		$im = imagecreatetruecolor(250, $count*20+30);		
		$white = imagecolorallocate($im, 255, 255, 255);
		$grey = imagecolorallocate($im, 128, 128, 128);
		$black = imagecolorallocate($im, 0, 0, 0);
		$font = SAE_Font_MicroHei;				
		imagefilledrectangle($im, 0, 0, 249, $count*20+29, $white);
		imagettftext($im, 12, 0, 10, 20, $black, $font, '粉丝 → 微博控指数');
		for($i=0; $i<$count;$i++)
		{
			imagettftext($im, 12, 0, 10, 20+20*($i+1), $black, $font, $fri_keys[$i] . ' → ' . $fri_values[$i]);
		}
		imagepng($im);
		imagedestroy($im);
	}
}*/
?>
