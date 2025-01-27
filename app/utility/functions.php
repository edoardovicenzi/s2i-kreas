<?php
function dd($variable)
{
    var_dump($variable);
}

function recursiveGetObjectProperty($obj, $path = [])
{
    $prevElement = $obj;
    foreach ($path as $step) {
        if (isset($prevElement->{$step})) {
            $prevElement = $prevElement->{$step};
        } else {
            throw new Exception($step);
        }
    }
    return $prevElement;
}

function parseQueryStringParameters()
{
    //Parse query parameters
    if (!$_GET) {
        return array();
    }
    return array_filter($_GET, function ($element) {
        return $element != null;
    });
}
