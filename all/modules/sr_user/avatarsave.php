<?php

if (isset($GLOBALS["HTTP_RAW_POST_DATA"])) {

	$data = $GLOBALS["HTTP_RAW_POST_DATA"];
	$uid = $_GET['uid'];
	$avatar = $_GET['avatar'];
	$savevars = explode("||", $_GET['savedir']);
	$savedir = $savevars[0];
	$redir = $savevars[1];

	$img = imagecreatefromstring($data);
	
	if ($img !== false && strlen($uid) > 0 && strlen($avatar) > 0 && strlen($savedir) > 0) {
		// save images
		$imgL = $savedir . "avatar-L__" . $uid . ".jpg";
		$imgS = $savedir . "avatar-S__" . $uid . ".jpg";
		
    // large
		imagejpeg($img, $imgL, 100);
    
		// small
    $thumb = imagecreatetruecolor(65, 65);
    imagecopyresampled($thumb, $img, 0, 0, 0, 0, 65, 65, imagesx($img), imagesy($img));
    imagejpeg($thumb, $imgS, 100);
    
    imagedestroy($img);
    
    // forward avatar hex data to drupal
    header('Location: ' . $redir . '/' . $avatar);
	}

}