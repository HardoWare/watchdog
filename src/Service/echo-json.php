<?php

namespace Symfony\Component\HttpFoundation;

use Symfony\Flex\Response;
use Symfony\Component\Routing\RequestContext;


$r = file_get_contents('php://input');

$token = "yOJ9KvRtQrTUaCPyRc22OJPMSmIrub9PUzMFUZEHMgXcd1fRWP7pBfosdSDFLzOF";

$get = $_GET["token"] ?? null;
$get === $token ? file_put_contents("JSON", var_export($r, true)) : false;

echo file_get_contents("JSON");

/* {
    "data_wyslania":"2023-12-24 11:50:13",
    "token":"987654321",
    "status":"1",
    "logi":"~body"
}   */