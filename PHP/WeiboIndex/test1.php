<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Insert title here</title>
</head>
<body>
<?php

// Set the content-type
header('Content-Type: image/png');

// Create the image
$im = imagecreatetruecolor(200, 230);

// Create some colors
$white = imagecolorallocate($im, 255, 255, 255);
$grey = imagecolorallocate($im, 128, 128, 128);
$black = imagecolorallocate($im, 0, 0, 0);
imagefilledrectangle($im, 0, 0, 199, 229, $white);

// The text to draw
$text = '@我们爱讲冷笑话	19434';
// Replace path by your own font path
//$font = SAE_Font_MicroHei;
//$font = 'arial.ttf';
$font = 'simsun.ttc';

// Add some shadow to the text
//imagettftext($im, 20, 0, 11, 21, $grey, $font, $text);

// Add the text
//image, size, angle, x, y, color, fontfile, text

for( $i = 1; $i<= 11; $i++)
{
	imagettftext($im, 12, 0, 10, 20*$i, $black, $font, $text);
}

//imagettftext($im, 14, 0, 10, 20, $black, $font, $text);
//imagettftext($im, 14, 0, 10, 40, $black, $font, $text);

// Using imagepng() results in clearer text compared with imagejpeg()
$file = 'test.png';
if(file_exists($file))
{
	rename($file,'test.bak.png');
	echo "file existed.";
}
imagepng($im);
//imagedestroy($im);

echo "<img src='test.png'>";
echo "done";
echo "<div><textarea name='ttcontent' rows='3' cols='40' id='twt'>谁是微博控？http://apps.weibo.com/complex </textarea></div>";
?>
</body>
</html>