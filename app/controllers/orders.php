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

//This endpoint will read and write on the link table "tblOrderProducts"
//TODO: CONTINUA QUI: convert and use DTO even for the PDO Models
try {
    switch ($method) {

        case 'GET':

            http_response_code(200);
            //Get all entries for "/orders"
            if (count($resource) == 0) {
                $results = $uow::$orderProducts->getAll();

                //Check for empty set
                if (count($results) == 0) {
                    throw new CustomHttpException("No orders found", 404);
                }
                $formattedList = $uow::$orderProducts::formatGetAll($results);
                $response['data'] = $formattedList;
            }


            //Get a specific entry for "/orders/:id"
            elseif (count($resource) > 0) {
                //Check if id is an integer
                if (!intval($resource[0])) {
                    throw new CustomHttpException("Property 'id' must be a valid number greater then 0", 400);
                }
                //Perform query
                $results = $uow::$orderProducts->getByOrderId($resource[0]);

                //Check for empty set
                if (!$results) {
                    throw new CustomHttpException("Order not found", 404);
                }

                //If user wants to access a specific property then return it
                if (count($resource) > 1) {
                    // remove first element which is the id
                    array_shift($resource);

                    try {
                        $response['data'] = recursiveGetObjectProperty($results, $resource);
                    } catch (Exception $e) {
                        throw new CustomHttpException("Unable to find resource '" . $e->getMessage() . "'", 404);
                    }
                }
                //Otherwise return the full results
                else {
                    $response['data'] = $results;
                }
            }

            break;


        case 'POST':
            //Only add an entry for "/orders"
            if (count($resource) == 0) {
                http_response_code(201);
                //Create a new order
                if (!isset($requestBody->data->destination_country)) {
                    throw new CustomHttpException("Request malformed : missing 'destination_country' property.", 400);
                }
                if (!isset($requestBody->data->products) || gettype($requestBody->data->products) != "array") {
                    throw new CustomHttpException("Request malformed : missing 'products' property. Must be an array.", 400);
                    if (count($requestBody->data->products) == 0) {
                        throw new CustomHttpException("Request malformed : missing products. Add products by specification", 400);
                    }
                }

                $uow::$order->destination_country = $requestBody->data->destination_country;
                $newOrder = $uow::$order->addNew();
                //Prepare the order products

                //Merge duplicate products with same id
                $noIdDuplicates = array();
                foreach ($requestBody->data->products as $product) {
                    if (isset($noIdDuplicates[$product->product_id]['quantity'])) {
                        $noIdDuplicates[$product->product_id]['quantity'] += $product->quantity;
                    } else {
                        $noIdDuplicates[$product->product_id]['id'] = $product->product_id;
                        $noIdDuplicates[$product->product_id]['quantity'] = $product->quantity;
                    }
                }


                //Insert each product to the order in tblOrderProducts
                foreach ($noIdDuplicates as $product) {
                    $uow::$orderProducts->product_id = $product['id'];
                    $uow::$orderProducts->quantity = $product['quantity'];
                    $uow::$orderProducts->order_id = $newOrder->order_id;
                    $uow::$orderProducts->addNew();
                }

                //return the added products
                $newOrder = $uow::$orderProducts->getByOrderId($newOrder->order_id);
                $response['data'] = $newOrder;
                $response['message'] = 'Order placed successfully';
                $response['location'] = '/orders/' . $response['data']->order_id;
            }
            break;


        case 'PUT':

            // If we are creating a resource than return 201 otherwise 200 (with content) or 204 (no content)
            //Only perform the action for "/orders/:id"
            if (count($resource) > 0) {
                //Check if id is an integer
                if (!intval($resource[0])) {
                    throw new CustomHttpException("Resource with 'id' " . $resource[0] . "not found", 400);
                }
                //Perform query
                $result = $uow::$orderProducts->getByOrderId($resource[0]);

                //No results found = we need to add the resource
                if (!$result) {
                    http_response_code(201);
                    //Ensure requred fields exists
                    if (!isset($requestBody->data->destination_country)) {
                        throw new CustomHttpException("Request malformed : missing 'destination_country' property.", 400);
                    }
                    if (!isset($requestBody->data->products) || gettype($requestBody->data->products) != "array") {
                        throw new CustomHttpException("Request malformed : missing 'products' property.", 400);
                    }
                    //Create a new order
                    $uow::$order->destination_country = $requestBody->data->destination_country;
                    $newOrder = $uow::$order->addNew();
                    //Prepare the order products

                    //Merge duplicate products with same id
                    $distinctProductsList = array();
                    foreach ($requestBody->data->products as $product) {
                        if (isset($noIdDuplicates[$product->product_id]['quantity'])) {
                            $distinctProductsList[$product->product_id]['quantity'] += $product->quantity;
                        } else {
                            $distinctProductsList[$product->product_id]['id'] = $product->product_id;
                            $distinctProductsList[$product->product_id]['quantity'] = $product->quantity;
                        }
                    }

                    //Insert each product to the order in tblOrderProducts
                    foreach ($distinctProductsList as $product) {
                        $uow::$orderProducts->order_id = $newOrder->order_id;
                        $uow::$orderProducts->product_id = $product['id'];
                        $uow::$orderProducts->quantity = $product['quantity'];

                        $uow::$orderProducts->addNew();
                    }

                    //return the added products
                    $newOrderProduct = $uow::$orderProducts->getByOrderId($newOrder->order_id);
                    $response['data'] = $newOrderProduct;
                    $response['message'] = 'Order placed successfully';
                    $response['location'] = '/orders/' . $newOrder->order_id;
                }
                //Otherwise update the entry
                else {
                    http_response_code(200);

                    //Update the order general information
                    //If user passed a destination_country use it...
                    if (isset($requestBody->data->destination_country)) {
                        $uow::$order->destination_country = $requestBody->data->destination_country;
                    }
                    //... or default to the existing one
                    else {
                        $uow::$order->destination_country = $results["destination_country"];
                    }

                    //If user passed a sold_on use it...
                    if (isset($requestBody->data->sold_on)) {
                        $uow::$order->sold_on = $requestBody->data->sold_on;
                    }
                    //... or default to the existing one
                    else {
                        $uow::$order->destination_country = $results["sold_on"];
                    }

                    //Set the id to the existing resource
                    $uow::$order->id = $resource[0];
                    //Update the entry
                    $uow::$order->updateEntry();


                    //If user passed a products list then update all those (and add missing ones)
                    if (isset($requestBody->data->products) && gettype($requestBody->data->products) == "array") {
                        foreach ($requestBody->data->products as $key => $product) {
                            $uow::$orderProducts->product_id = $product->product_id;
                            if (!isset($product->quantity)) {
                                throw new CustomHttpException("Request malformed for product with 'product_id' = '" . $product->product_id . "' : missing 'quantity' property.", 400);
                                if ($product->quantity > 0) {
                                    throw new CustomHttpException("Request malformed for product with 'product_id' = '" . $product->product_id . "' :'quantity' property must be greater than 0.", 400);
                                }
                            }

                            $uow::$orderProducts->order_id = $resource[0];
                            $uow::$orderProducts->quantity = $product->quantity;
                            //If exists update quantity otherwise add it
                            if ($uow::$orderProducts->getByOrderIdAndProductId()) {
                                $uow::$orderProducts->updateEntry();
                            } else {
                                $uow::$orderProducts->addNew();
                            }
                        }
                    }

                    //Prepare the response
                    $rawResults = $uow::$orderProducts->getByOrderId($resource[0]);
                    $response['data'] = $rawResults;
                }
            }
            break;
        case 'DELETE':
            //Only perform the action for "/orders/:id"
            http_response_code(200);
            if (count($resource) > 0) {
                //Save the response first
                $response['data'] = $uow::$orderProducts->getByOrderId($resource[0]);;

                //delete just the order (OrderProducts will cascade)
                $uow::$order->deleteById($resource[0]);
                $response['message'] = 'Order was deleted successfully';
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
