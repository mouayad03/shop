<?php
    function error($message, $code) {
		$error = array("message" => $message);

		echo json_encode($error);
		http_response_code($code);

		die();
	}
	function message($response_message, $code) {
		$response_message = array("message" => $response_message);
		echo json_encode($response_message);
		http_response_code($code);

		die();
	}
	function output_message($output_message) {
		$output_message = array($output_message);
		echo json_encode($output_message);

		die();
	}
?>