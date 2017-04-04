<?php
	$id = $_POST['id'];
	$file = getcwd() . '/data/' . $id . '.txt';
	if( file_exists($file) ) {
		echo json_encode(array(
        	code => 200,
        	result => file_get_contents($file)
    	));
	} else {
		echo json_encode(array(
        	code => 503,
        	result => "I can't find/open $file"
    	));
	}
?>