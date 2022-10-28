<?php
    use Psr\Http\Message\ResponseInterface as Response;
	use Psr\Http\Message\ServerRequestInterface as Request;
	use Slim\Factory\AppFactory;
	use ReallySimpleJWT\Token;

	/**
	 * @OA\Get(
	 * 	path="/Product/{product_id}",
	 * 	summary="Used to list the data from the product",
	 * 	tags={"Product_Function"},
	 * 	@OA\Parameter(
	 * 		name="product_id",
	 * 		in="path",
	 * 		required=true,
	 * 		description="The ID of the product to fetch",
	 * 		@OA\Schema(
	 * 			type="integer",
	 * 			example="1"
	 * 		)
	 * 	),
	 * 	@OA\Response(response="200", description="OK"),
	 *  @OA\Response(response="404", description="The ID was not found"))
	 */
    $app->get("/Product/{product_id}", function (Request $request, Response $response, $args) {
		//connect to the authentication
		require "controller/require_authentication.php";

		$product_id = $args["product_id"];

		$product = get_product($product_id);

		if ($product) {
			output_message($product);
		}
		else if (is_string($product)) {
			error($product, 500);
		}
		else {
			error("The ID "  . $product_id . " was not found.", 404);
		}
		return $response;
	});

	/**
	 * @OA\Get(
	 * 	path="/Products",
	 * 	summary="Used to list all products from Database",
	 * 	tags={"Product_Function"},
	 * 	@OA\Parameter(
	 * 		name="Products",
	 * 		in="path",
	 * 		required=true,
	 * 		description="You need to write Products in the parameter, to list all data",
	 * 		@OA\Schema(
	 * 			type="string",
	 * 			example="Products"
	 * 		)
	 * 	),
	 * 	@OA\Response(response="200", description="OK"),
	 *  @OA\Response(response="500", description="An error occurred while fetching the Products."))
	 */
    $app->get("/Products", function (Request $request, Response $response, $args) {
		require "controller/require_authentication.php";

		$product = get_all_products();

		if (is_string($product)) {
			error($product, 500);
		}
		else {
			output_message($product);
		}

		return $response;
	});

	/**
	 * @OA\Post(
     *     path="/Product",
     *     summary="Used to create new products",
     *     tags={"Product_Function"},
     *     requestBody=@OA\RequestBody(
     *         request="/Product",
     *         required=true,
     *         description="The data are passed to the server via the request body",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="name", type="string", example="Watch"),
     *                 @OA\Property(property="active", type="integer", example=1)
     *             )
     *         )
     *     ),
	 * 	   @OA\Response(response="201", description="The Category was successfuly created."),
	 * 	   @OA\Response(response="400", description="The (name) field must not be empty."))
	 */
    $app->post("/Product", function (Request $request, Response $response, $args) {

		require "controller/require_authentication.php";

		$request_body_string = file_get_contents("php://input");

		$request_data = json_decode($request_body_string, true);

        $sku = strip_tags(addslashes($request_data["sku"]));
		$active = intval($request_data["active"]);
        $name = strip_tags(addslashes($request_data["name"]));
        $image = strip_tags(addslashes($request_data["image"]));
        $description = strip_tags(addslashes($request_data["description"]));
        $price = intval($request_data["price"]);
        $stock = intval($request_data["stock"]);

        if (empty($sku)) {
			error("Sku cannot be empty.", 400);
		}
		if (strlen($sku) > 100) {
			error("The name is too long. Please enter less than 100 letters.", 400);
		}
		//The name can not be empty
		if (empty($name)) {
			error("Name cannot be empty.", 400);
		}
		//Limit the length of the name.
		if (strlen($name) > 500) {
			error("The name is too long. Please enter less than 500 letters.", 400);
		}
        if (empty($image)) {
			error("Image cannot be empty.", 400);
		}
		if (strlen($image) > 1000) {
			error("The name is too long. Please enter less than 500 letters.", 400);
		}
        if (empty($description)) {
			error("The description cannot be empty.", 400);
		}
		//The active have to be an integer
		if (!isset($request_data["active"]) || !is_numeric($request_data["active"])) {
			error("Please provide an integer number for the (active) field.", 400);
		}
        if (!isset($request_data["price"]) || !is_numeric($request_data["price"])) {
			error("Please provide an integer number for the (price) field.", 400);
		}
        if (!isset($request_data["stock"]) || !is_numeric($request_data["stock"])) {
			error("Please provide an integer number for the (stock) field.", 400);
		}
		//Limit the active nummber
		if ($active < 0 || $active > 1) {
			error("The active must either 0 or 1.", 400);
		}
        if ($price < 0 || $price > 65) {
			error("The price must between 0 and 65.", 400);
		}
        if ($stock < 0 || $stock > 11) {
			error("The stock must between 0 or 11.", 400);
		}
		//checking if allthing was good
		if (create_new_product($sku, $active, $name, $image, $description, $price, $stock) === true) {
			message("The product was successfuly created.", 201);
		}
		//an server error
		else {
			error("An error while saving the product.", 500);
		}
		return $response;		
	});

	/**
     * @OA\Put(
     *     path="/Product/{product_id}",
     *     summary="(Used to update anything from database",
     *     tags={"Product_Function"},
     *     @OA\Parameter(
     *         name="product_id",
     *         in="path",
     *         required=true,
     *         description="The ID of the product to update",
     *         @OA\Schema(
     *             type="integer",
     *             example="1"
     *         )
     *     ),
     *     requestBody=@OA\RequestBody(
     *         request="/Product/{product_id}",
     *         required=true,
     *         description="Write the new data in Body to update it",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="name", type="string", example="Car"),
     *                 @OA\Property(property="price", type="integer", example="50")
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="The product_data were successfully updated"),
	 * 	   @OA\Response(response="400", description="The name is too long. Please enter less than 500 letters."),
	 * 	   @OA\Response(response="404", description="No product found for the ID"),
	 * 	   @OA\Response(response="500", description="An error occurred while saving the product data.")
     * )
	 */
    $app->put("/Product/{product_id}", function (Request $request, Response $response, $args) {

		require "controller/require_authentication.php";
		
		$product_id = intval($args["product_id"]);
		
		$product = get_product($product_id);
		
		if (!$product) {
			error("No Product found for the ID " . $product_id . ".", 404);
		}
		
		$request_body_string = file_get_contents("php://input");
		
		$request_data = json_decode($request_body_string, true);

		if (isset($request_data["sku"])) {
			$sku = strip_tags(addslashes($request_data["sku"]));
		
			if (strlen($sku) > 100) {
				error("The sku is too long. Please enter less than 100 letters.", 400);
			}
		
			$product["sku"] = $sku;
		}

        if (isset($request_data["name"])) {
			$name = strip_tags(addslashes($request_data["name"]));
		
			if (strlen($name) > 500) {
				error("The name is too long. Please enter less than 500 letters.", 400);
			}
		
			$product["name"] = $name;
		}

        if (isset($request_data["image"])) {
			$image = strip_tags(addslashes($request_data["image"]));
		
			if (strlen($image) > 1000) {
				error("The sku is too long. Please enter less than 1000 letters.", 400);
			}
		
			$product["image"] = $image;
		}

        if (isset($request_data["description"])) {
			$description = strip_tags(addslashes($request_data["description"]));
		
			if (strlen($description) > 1000) {
				error("The sku is too long. Please enter less than 1000 letters.", 400);
			}
		
			$product["description"] = $description;
		}

		if (isset($request_data["active"])) {
			if (!is_numeric($request_data["active"])) {
				error("Please provide an integer number for the (active) field.", 400);
			}
		
			$active = intval($request_data["active"]);
		
			if ($active < 0 || $active > 200) {
				error("The active must be either 0 or 1.", 400);
			}
		
			$product["active"] = $active;
		}

        if (isset($request_data["category_id"])) {
			if (!is_numeric($request_data["category_id"])) {
				error("Please provide an integer number for the (category_id) field.", 400);
			}
		
			$category_id = intval($request_data["category_id"]);

			if (!$request_data[$category_id]) {
				error("No category found for the ID " . $category_id . ".", 404);
			}
		
			$product["category_id"] = $category_id;
		}
		
        if (isset($request_data["price"])) {
			if (!is_numeric($request_data["price"])) {
				error("Please provide an integer number for the (price) field.", 400);
			}
		
			$price = intval($request_data["price"]);
		
			if ($price < 0 || $price > 65) {
				error("The price must be between 0 and 65.", 400);
			}
		
			$product["price"] = $price;
		}

        if (isset($request_data["stock"])) {
			if (!is_numeric($request_data["stock"])) {
				error("Please provide an integer number for the (stock) field.", 400);
			}
		
			$stock = intval($request_data["stock"]);
		
			if ($stock < 0 || $stock > 11) {
				error("The stock must be between 0 or 11.", 400);
			}
		
			$product["stock"] = $stock;
		}
		
		if (update_product($product_id, $product["name"], $product["active"], $product["sku"], $product["category_id"], $product["image"], $product["description"], $product["price"], $product["stock"])) {
			message("The productdata were successfully updated", 200);
		}
		else {
			error("An error occurred while saving the product data.", 500);
		}
		
		return $response;
	});

	/**
     * @OA\Delete(
     *     path="/Product{product_id}",
     *     summary="Used to delete the products",
     *     tags={"Product_Function"},
     *     @OA\Parameter(
     *         name="product_id",
     *         in="path",
     *         required=true,
     *         description="The ID of the product to delete",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(response="200", description="The product was succsessfuly deleted."),
	 * 	   @OA\Response(response="404", description="No product found for the ID category_id"))
	 */
    $app->delete("/Product/{product_id}", function (Request $request, Response $response, $args) {
		
		require "controller/require_authentication.php";
		
		$product_id = intval($args["product_id"]);
		
		$result = delete_product($product_id);
		
		if (!$result) {
			error("No product found for the ID " . $product_id . ".", 404);
		}
		else {
			message("The product was succsessfuly deleted.", 200);
		}
		
		return $response;
	});
?>