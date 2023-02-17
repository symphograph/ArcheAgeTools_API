<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';
use App\Api;

$rows = file(dirname($_SERVER['DOCUMENT_ROOT']) . '/logs/errors.log');
$arr = [];
foreach ($rows as $row){
    $row = json_decode($row, 4);
    $arr[] = $row;
}
Api::dataResponse($arr);
//echo json_encode(['result'=>'hgh', 'data'=>$arr]);