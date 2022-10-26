<?php
    use Psr\Http\Message\ResponseInterface as Response;
	use Psr\Http\Message\ServerRequestInterface as Request;
	use Slim\Factory\AppFactory;
	use ReallySimpleJWT\Token;

    /**
     * @OA\Post(
     *     path="/Authenticate",
     *     summary="Used to authenticate the user",
     *     tags={"General"},
     *     requestBody=@OA\RequestBody(
     *         request="/Authenticate",
     *         required=true,
     *         description="The credentials are passed to the server via the request body",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="username", type="string", example="name"),
     *                 @OA\Property(property="password", type="integer", example="password")
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Succsessfuly authenticated")),
	 *	   @OA\Response(response="401", description="Unauthorized")),
	 *     @OA\Response(response="500", description="Internal Server error"))
     * )
	 */
    $app->post("/Authenticate", function (Request $request, Response $response, $args) {
		global $api_username;
		global $api_password;

		//Read request body input string
		$request_body_string = file_get_contents("php://input");

		//Parse the JSON string
		$request_data = json_decode($request_body_string, true);

		$username = $request_data["username"];
		$password = $request_data["password"];

		//If username or password false = error
		if ($username != $api_username || $password != $api_password) {
			error("Invalid credentials.", 401);
		}

		//Generate the access token and store it in the cookies.
		$token = Token::create($username, $password, time() + 3600, "localhost");

		setcookie("token", $token);

		//succsessfuly loggin
		echo "true";

		return $response;
	});

	/**
	 * @OA\Get(
	 * 	path="/Category/{category_id}",
	 * 	summary="Used to list the data from the category",
	 * 	tags={"Function"},
	 * 	@OA\Parameter(
	 * 		name="category_id",
	 * 		in="/Category",
	 * 		required=true,
	 * 		description="The ID of the category to fetch",
	 * 		@OA\Schema(
	 * 			type="integer",
	 * 			example="1"
	 * 		)
	 * 	),
	 * 	@OA\Response(response="200", description="The list"))
	 *  @OA\Response(response="404", description="The ID was not found"))
	 */
	$app->get("/Category/{category_id}", function (Request $request, Response $response, $args) {
		//connect to the authentication
		require "controller/require_authentication.php";

		$category_id = $args["category_id"];

		$category = get_category($category_id);

		if ($category) {
			echo json_encode($category);
		}
		else if (is_string($category)) {
			error($category, 500);
		}
		else {
			error("The ID "  . $category_id . " was not found.", 404);
		}

		return $response;
	});

	/**
     * @OA\Post(
     *     path="/Category",
     *     summary="Used to create new categorys",
     *     tags={"Function"},
     *     requestBody=@OA\RequestBody(
     *         request="/Category",
     *         required=true,
     *         description="The data are passed to the server via the request body",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="name", type="string", example="Watch"),
     *                 @OA\Property(property="active", type="integer", example="1")
     *             )
     *         )
     *     ),
	 * 	   @OA\Response(response="201", description="The Category was succsessfuly created."))
	 * 	   @OA\Response(response="400", description="The (name) field must not be empty."))
	 *     @OA\Response(response="500", description="An error while saving the category."))
     * )
	 */

	$app->post("/Category", function (Request $request, Response $response, $args) {

		require "controller/require_authentication.php";

		$request_body_string = file_get_contents("php://input");

		$request_data = json_decode($request_body_string, true);

		$name = strip_tags(addslashes($request_data["name"]));
		$active = intval($request_data["active"]);

		//The name can not be empty
		if (empty($name)) {
			error("The (name) field must not be empty.", 400);
		}
		//Limit the length of the name.
		if (strlen($name) > 500) {
			error("The name is too long. Please enter less than 500 letters.", 400);
		}
		//The active have to be an integer
		if (!isset($request_data["active"]) || !is_numeric($request_data["active"])) {
			error("Please provide an integer number for the (active) field.", 400);
		}
		//Limit the active nummber
		if ($active < 0 || $active > 1) {
			error("The active must either 0 or 1.", 400);
		}
		//checking if allthing was good
		if (create_new_category($active, $name) === true) {
			error("The Category was succsessfuly created.", 201);
		}
		//an server error
		else {
			error("An error while saving the category.", 500);
		}

		return $response;		
	});

	$app->put("/Category/{category_id}", function (Request $request, Response $response, $args) {

		require "controller/require_authentication.php";

		$category_id = intval($args["category_id"]);

		$category_id = update_category($category_id);

		return $response;
	});

	$app->delete("/Category/{category_id}", function (Request $request, Response $response, $args) {

		require "controller/require_authentication.php";

		$category_id = intval($args["category_id"]);

		//Delete the entity.
		$result = delete_category($category_id);

		if (!isset($request_data["category_id"]) || !is_numeric($request_data["category_id"])) {
			error("Please provide an integer number for the (active) field.", 400);
		}
		else {
			error("The category was succsessfuly deleted.", 200);
		}

		return $response;
	});
?>