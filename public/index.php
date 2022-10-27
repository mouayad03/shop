<?php
    //Content type = application/json.
	header("Content-Type: application/json");

	//Import all methodes.
	use Psr\Http\Message\ResponseInterface as Response;
	use Psr\Http\Message\ServerRequestInterface as Request;
	use Slim\Factory\AppFactory;
	use ReallySimpleJWT\Token;


	//require from another docs.
	require __DIR__ . "/../vendor/autoload.php";
	require "model/functions_category.php";
	require "model/functions_product.php";
	require_once "config/config.php";

	$app = AppFactory::create();

	/**
		 * @OA\Info(title="Shop", version="0.1")
	 */

	require "controller/endpoints_category.php";
	require "controller/endpoints_product.php";

	$app->run();
?>