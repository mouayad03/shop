<?php
    function error($message, $code) {
		//JSON error message
		$error = array("message" => $message);
		echo json_encode($error);

		//response code
		http_response_code($code);

		//End
		die();
	}
?>