<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\User\{Account, Prof};
use App\Api;
use App\Craft\AccountCraft;
use App\Errors\AppErr;
use App\Errors\MyErrors;
use App\Errors\ValidationErr;

$Account = Account::byToken();

try {
    $profId = intval($_POST['profId'] ?? 0)
        or throw new ValidationErr('profId', 'Ошибка данных');

    $lvl = intval($_POST['lvl'] ?? 0)
        or throw new ValidationErr('lvl', 'Ошибка данных');

    Prof::saveLvl($Account->id, $profId, $lvl)
        or throw new AppErr('saveLvl err', 'Ошибка при сохранении');

    AccountCraft::clearAllCrafts();
} catch (MyErrors $err) {
    Api::errorResponse($err->getResponseMsg(), $err->getHttpStatus());
}
Api::resultResponse();