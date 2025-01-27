<?php

//This router handles the tblOrderProducts which also hanldes tblOrder and tblProduct
include_once("config.php");
require_once(__DIR__ . "/../UnitOfWork/UnitOfWork.php");
require_once(__DIR__ . "/../utility/pathRegex.php");

#Set the response headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Request-With");

//default is valid response
http_response_code(202);

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

try {
    switch ($method) {
        case 'GET':
            //Get all entries for "/products"
            if (count($resource) == 0) {
                $results = $uow::$product->getAll();
                foreach ($results as $product) {
                    //remove unwanted quantity
                    unset($product->quantity);
                }
                if (!$results) {
                    throw new CustomHttpException("No product found", 404);
                }
                $response['data'] = $results;
            }
            //Get a specific entry for "/products/:id"
            else {
                //Check if id is an integer
                if (!intval($resource[0])) {
                    throw new CustomHttpException("Property 'id' must be a valid number greater then 0", 400);
                }
                //Perform query

                $results = $uow::$product->getById($resource[0]);

                //Check for empty set
                if (!$results) {
                    throw new CustomHttpException("Product not found", 404);
                }
                //remove unwanted quantity
                unset($results->quantity);
                $response['data'] = $results;
            }
            break;
        case 'POST':
            //Only add an entry for "/products"
            if (count($resource) == 0) {
                if (!isset($requestBody->data->product_name)) {
                    throw new CustomHttpException("Request malformed : missing 'name' property.", 400);
                }
                if (!isset($requestBody->data->saved_co2)) {
                    throw new CustomHttpException("Request malformed : missing 'saved_co2' property.", 400);
                }

                $uow::$product->name = $requestBody->data->product_name;
                $uow::$product->saved_co2 = $requestBody->data->saved_co2;
                $response['data'] = $uow::$product->addNew();
                unset($response['data']->quantity);

                $response['location'] = '/products/' . $response['data']->product_id;
                $response['message'] = 'Item added successfully';
            }
            break;
        case 'PUT':
            //Only perform the action for "/products/:id"
            if (count($resource) > 0) {

                //Check if id is a number
                if (!intval($resource[0])) {
                    throw new CustomHttpException("Property 'id' must be a valid number greater then 0", 400);
                }

                //Get the record if it exists
                $existingProduct = $uow::$product->getById($resource[0]);
                //If there are no records then we need to add it...
                if (!$existingProduct) {
                    http_response_code(201);
                    if (!isset($requestBody->data->product_name)) {
                        throw new CustomHttpException("Request malformed : missing 'name' property.", 400);
                    }

                    if (!isset($requestBody->data->saved_co2)) {
                        throw new CustomHttpException("Request malformed : missing 'saved_co2' property.", 400);
                    }
                    $uow::$product->name = $requestBody->data->product_name;
                    $uow::$product->saved_co2 = $requestBody->data->saved_co2;
                    //Prepare Response
                    $response['data'] = $uow::$product->addNew();
                    $response['message'] = 'Item Added successfully';
                    $response['location'] = '/products/' . $response['data']->product_id;
                }
                //... otherwise update the data we have
                else {
                    http_response_code(200);
                    // Set the name or default to the name we already have
                    if (isset($requestBody->data->product_name)) {
                        $uow::$product->name = $requestBody->data->product_name;
                    } else {
                        $uow::$product->name = $existingProduct->product_name;
                    }

                    // Set the saved_co2 or default to the saved_co2 we already have
                    if (isset($requestBody->data->saved_co2)) {
                        $uow::$product->saved_co2 = $requestBody->data->saved_co2;
                    } else {
                        $uow::$product->saved_co2 = $existingProduct->saved_co2;
                    }
                    $uow::$product->id = $resource[0];
                    //Prepare Response
                    $response['data'] = $uow::$product->updateEntry();
                    $response['message'] = 'Item Updated successfully';
                }
                unset($response['data']->quantity);
            }
            break;
        case 'DELETE':
            //Only perform the action for "/products/:id"
            if (count($resource) > 0) {
                $response['data'] = $uow::$product->deleteById($resource[0]);
                http_response_code(200);
                unset($response['data']->quantity);
                $response['message'] = 'Item was deleted successfully';
            }
            break;
        default:
            throw new CustomHttpException('Unsupported method. Please review your request.');
            break;
    }
} catch (CustomHttpException $e) {
    http_response_code($e->getHttpStatusCode());
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
