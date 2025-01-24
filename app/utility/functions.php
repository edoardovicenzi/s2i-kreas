<?php
function dd($variable){
    var_dump($variable);
}

function recursiveGetObjectProperty($obj, $path = []){
    $prevElement = $obj;
    foreach ($path as $step) {
        if(isset($prevElement->{$step})){
            $prevElement = $prevElement->{$step};
        }
        else {
            throw new Exception($step);
        }
    }
    return $prevElement;
}
?>
