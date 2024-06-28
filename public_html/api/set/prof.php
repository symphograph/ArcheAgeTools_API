<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\User\{AccSets, Prof};
use Symphograph\Bicycle\Api\Response;
use App\Craft\AccountCraft;
use Symphograph\Bicycle\Errors\AppErr;
use Symphograph\Bicycle\Errors\ValidationErr;

$AccSets = AccSets::byJwt();

$profId = intval($_POST['profId'] ?? 0)
or throw new ValidationErr('profId', 'Ошибка данных');

$lvl = intval($_POST['lvl'] ?? 0)
or throw new ValidationErr('lvl', 'Ошибка данных');

Prof::saveLvl($AccSets->accountId, $profId, $lvl);

AccountCraft::clearAllCrafts();

Response::success();