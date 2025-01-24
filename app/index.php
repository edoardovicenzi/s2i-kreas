<?php

# Routing

$uri = parse_url($_SERVER['REQUEST_URI'])['path'];

$routes = [
    '/' => 'controllers/documentation.php',
    '/orders' => 'controllers/orders.php',
    '/products' => 'controllers/products.php',
    '/totalco2' => 'controllers/totalco2.php'
];

$get_pattern = '{(/\w+)(/\d+)*}';
//automatically select the right route
if (preg_match($get_pattern, $uri, $matches)) {
    if (array_key_exists($matches[1], $routes)) {
        require($routes[$matches[1]]);
    } else {
        //TODO: FEAT: better default 404 response
        http_response_code(404);
    }
}
