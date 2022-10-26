<?php
    //Content type = application/json.
	header("Content-Type: application/json");

	//Import all methodes.
	use Psr\Http\Message\ResponseInterface as Response;
	use Psr\Http\Message\ServerRequestInterface as Request;
	use Slim\Factory\AppFactory;
	use ReallySimpleJWT\Token;

	/**
	* @OA\Info(title="Shop", version="1")
	*/

	//require from another docs.
	require __DIR__ . "/../vendor/autoload.php";
	require "model/functions.php";
	require_once "config/config.php";

	$app = AppFactory::create();

	require "controller/endpoints.php";

	$app->run();
?>