<?php

function resultCategory($result)
{
    $res = NULL;
    if($result >= 1000)
        $res = 'not suppressed';
    else if($result < 1000)
        $res = 'suppressed';
    return $res;
}



