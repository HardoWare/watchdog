<?php

namespace App\Service;


$input = file_get_contents('php://input');
$ee=null;
$js = urldecode($input);
file_put_contents("C:\Users\Kacper\Desktop\log-api\src\Service\JSON", var_export($js, true));

$arr = json_decode($input, true);
file_put_contents("C:\Users\Kacper\Desktop\log-api\src\Service\ARRAY", var_export($arr, true));

echo file_get_contents("C:\Users\Kacper\Desktop\log-api\src\Service\JSON");