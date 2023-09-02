<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\User\AccSettings;
use Symphograph\Bicycle\Api\Response;
use App\Craft\AccountCraft;
use Symphograph\Bicycle\Errors\ValidationErr;


$AccSets = AccSettings::byJwt();

$AccSets->mode = intval($_POST['mode'] ?? 0)
or throw new ValidationErr('mode', 'Ошибка данных');

$AccSets->putToDB();

AccountCraft::clearAllCrafts();

Response::success();