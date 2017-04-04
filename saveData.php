<?php
    header("Content-Type: application/json; charset=UTF-8");

	$id = $_POST['id'];
	$data = $_POST['data'];
	$user = $_SERVER['PHP_AUTH_USER'];
	if( json_decode($data) ) { //save only if valid JSON
		$result = file_put_contents(getcwd() . '/data/' . $id . '.txt', $data);
		if($result !== false) {
			//success
			echo json_encode(array(
	        	result => "Data saved!",
	        	code => 200
	    	));
		} else {
			// header("HTTP/1.1 500 Can't write to file");
			echo json_encode(array(
	        	result => "I can't write to that file.",
	        	code => 501
	    	));
		}
	} else {
		// fail
		// header("HTTP/1.1 500 Invalid JSON");
		echo json_encode(array(
        	result => "Invalid JSON",
        	code => 502
		));
	}
?>