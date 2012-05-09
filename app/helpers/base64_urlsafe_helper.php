<?php

function base64_url_encode ($str)
{
    $str = base64_encode($str);
    $str = str_replace('+', '-', $str);
    $str = str_replace('/', '_', $str);
    return $str;
}

function base64_url_decode ($str)
{
    $str = str_replace('-', '+', $str);
    $str = str_replace('_', '/', $str);
    return base64_decode($str);
}
