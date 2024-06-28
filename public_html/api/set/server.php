<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\User\AccSets;
use Symphograph\Bicycle\Api\Response;
use Symphograph\Bicycle\Errors\{ValidationErr};


$AccSets = AccSets::byJwt();
$AccSets->serverGroupId = $_POST['serverGroupId']
    ?? throw new ValidationErr('serverGroupId');

$AccSets->putToDB();

Response::success();