<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use Symphograph\Bicycle\Api\Response;
use App\Craft\AccountCraft;
use Symphograph\Bicycle\Errors\AppErr;
use Symphograph\Bicycle\Errors\MyErrors;
use Symphograph\Bicycle\Errors\ValidationErr;
use App\User\Account;

$Account = Account::byToken();

$Account->AccSets->mode = intval($_POST['mode'] ?? 0)
or throw new ValidationErr('mode', 'Ошибка данных');

$Account->AccSets->putToDB()
or throw new AppErr('putToDB err','Ошибка при сохранении');

AccountCraft::clearAllCrafts();

Response::success();