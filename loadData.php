<?php
	$id = $_POST['id'];
	$user = $_SERVER['PHP_AUTH_USER'];
	$file = getcwd() . '/data/' . $id . '.txt';
	if( file_exists($file) ) {
		echo htmlspecialchars( file_get_contents($file), ENT_QUOTES );
	}
?>