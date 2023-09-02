<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\User\AccSettings;
use Symphograph\Bicycle\Api\Response;
use Symphograph\Bicycle\Errors\{AppErr, ValidationErr};


$AccSets = AccSettings::byJwt();
$AccSets->serverId = intval($_POST['server'] ?? 9)
or throw new ValidationErr('server', 'Ошибка данных');

$AccSets->putToDB();

Response::success();