<?php

//Returns an array
function getDetailResourceURI($uri){
    $detail_pattern = '{[^\/]+}';
    if (preg_match_all($detail_pattern, $uri, $matches)){
        array_shift($matches[0]);
        return $matches[0];
    }
    return [];
}
?>
