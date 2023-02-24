<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\User\{Account, Prof};
use Symphograph\Bicycle\Api\Response;
use App\Craft\AccountCraft;
use Symphograph\Bicycle\Errors\AppErr;
use Symphograph\Bicycle\Errors\MyErrors;
use Symphograph\Bicycle\Errors\ValidationErr;

$Account = Account::byToken();

$profId = intval($_POST['profId'] ?? 0)
or throw new ValidationErr('profId', 'Ошибка данных');

$lvl = intval($_POST['lvl'] ?? 0)
or throw new ValidationErr('lvl', 'Ошибка данных');

Prof::saveLvl($Account->id, $profId, $lvl)
or throw new AppErr('saveLvl err', 'Ошибка при сохранении');

AccountCraft::clearAllCrafts();

Response::success();