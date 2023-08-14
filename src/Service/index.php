<?php

namespace App\Service;

use Symfony\Flex\Response;

//class IndexService
//{
//    public function index(Request $request): Response
//    {
//
//        return true;
//    }
//}


$input = file_get_contents('php://input');
$req = $_REQUEST;
$ee=null;
$tt = urldecode($input);
$ee = json_decode($input, true);
file_put_contents("C:\Users\TwardyDyskus\Desktop\log-api\src\Service\JSON", var_export($ee, true));

echo file_get_contents("C:\Users\TwardyDyskus\Desktop\log-api\src\Service\JSON");

/* {
    "data_wyslania":"2023-12-24 11:50:13",
    "token":"987654321",
    "status":"1",
    "logi":"~body"
}   */