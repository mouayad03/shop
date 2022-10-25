<?php
    use Psr\Http\Message\ResponseInterface as Response;
	use Psr\Http\Message\ServerRequestInterface as Request;
	use Slim\Factory\AppFactory;
	use ReallySimpleJWT\Token;

    //Authenticate function for the authentication
    $app->post("/Authenticate", function (Request $request, Response $response, $args) {
		global $api_username;
		global $api_password;

		//Read request body input string.
		$request_body_string = file_get_contents("php://input");

		//Parse the JSON string.
		$request_data = json_decode($request_body_string, true);

		$username = $request_data["username"];
		$password = $request_data["password"];

		//If either the username or the password is incorrect, return a 401 error.
		if ($username != $api_username || $password != $api_password) {
			error("Invalid credentials.", 401);
		}

		//Generate the access token and store it in the cookies.
		$token = Token::create($username, $password, time() + 3600, "localhost");

		setcookie("token", $token);

		//Echo true for a successful response.
		echo "true";

		return $response;
	});

	$app->get('/Categorys', function (Request $request, Response $response) {
		$Categorys = get_all_registrations();

		if (is_string($Categorys)) {
			//Error while fetching.
			error($Categorys, 500);
		}
		else {
			//Success.
			echo json_encode($Categorys);
		}

		return $response;
	   });
?>