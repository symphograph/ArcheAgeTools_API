<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';
use Symphograph\Bicycle\Api\Response;
use Symphograph\Bicycle\Errors\MyErrors;

$file = MyErrors::getLogFilename();
$rows = file($file);
$arr = [];
foreach ($rows as $row){
    $row = json_decode($row);
    $arr[] = (object) $row;
}
Response::data($arr);