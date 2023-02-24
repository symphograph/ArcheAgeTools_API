<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use Symphograph\Bicycle\Api\Response;
use App\Craft\AccountCraft;
use Symphograph\Bicycle\Errors\MyErrors;
use Symphograph\Bicycle\Errors\ValidationErr;
use App\User\Account;

$Account = Account::byToken();
$craftId = intval($_POST['craftId'] ?? 0)
or throw new ValidationErr('craftId', 'Ошибка данных');

AccountCraft::delUBest($Account->id, $craftId);
AccountCraft::clearAllCrafts();

Response::success();