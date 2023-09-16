<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\User\AccSettings;
use Symphograph\Bicycle\Api\Response;
use App\Craft\AccountCraft;
use Symphograph\Bicycle\Errors\ValidationErr;


$AccSets = AccSettings::byJwt();
$craftId = intval($_POST['craftId'] ?? 0)
or throw new ValidationErr('craftId', 'Ошибка данных');

AccountCraft::delUBest($AccSets->accountId, $craftId);
AccountCraft::clearAllCrafts();

Response::success();