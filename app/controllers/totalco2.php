<?php

//This router handles the tblOrderProducts which also hanldes tblOrder and tblProduct
include_once("config.php");
require_once(__DIR__ . "/../utility/pathRegex.php");
require_once(__DIR__ . "/../UnitOfWork/UnitOfWork.php");
require_once(__DIR__ . "/../DTO/DTOOrderProduct.php");
require_once(__DIR__ . "/../DTO/DTOProduct.php");

//Set the response headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Request-With");

//default is resource not found
http_response_code(404);

//Get request information
$uri = parse_url($_SERVER['REQUEST_URI'])['path'];
$method = $_SERVER['REQUEST_METHOD'];
$requestBody = json_decode(file_get_contents("php://input"));

//get uow instance
$uow = UnitOfWork::getInstance();

//Response object
$data = null;
$response = array();

//Set some utility

$resource = getDetailResourceURI($uri);

//This endpoint will read aggregate functions from the "viewOrderProducts" view
try {
    switch ($method) {

        case 'GET':

            http_response_code(200);
            //Get all entries for "/totalco2"
            if (count($resource) == 0) {
                $results = $uow::$totalCO2->getTotal();
                //Cast to int
                if ($results) {
                    $results['saved_co2'] = (int)$results['saved_co2'];
                }
                $response['data'] = $results;
            } else {
                throw new CustomHttpException("Unsupported endpoint try '/totalco2'", 400);
            }

            break;

        default:
            throw new Exception('Unsupported method. Please review your request.');
            break;
    }
    //Common error handling
} catch (CustomHttpException $e) {
    http_response_code($e->getHttpStatusCode());
    $response['message'] = $e->getMessage();
    //never return data
    unset($response['data']);
}

//Send response
echo json_encode($response);
