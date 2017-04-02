<?php
	$id = $_POST['id'];
	$chartData = $_POST['chartData'];
	$user = $_SERVER['PHP_AUTH_USER'];
	$result = file_put_contents(getcwd() . '/data/' . $id . '.txt', $chartData);
	if($result !== false) {
		//success
		header("Status: 200");
		echo json_encode(array(
        	result => "Data saved!",
        	code => 200
    	));
	} else {
		// fail
		echo json_encode(array(
        	result => "I can't write to that file.",
        	code => 502
    	));
	}
?>