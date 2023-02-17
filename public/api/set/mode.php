<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\Api;
use App\Craft\AccountCraft;
use App\User\Account;

$Account = Account::byToken();

$Account->AccSets->mode = intval($_POST['mode'] ?? 0)
or die(Api::errorMsg('Ой!'));

$Account->AccSets->putToDB()
or die(Api::errorMsg('Ошибка при сохранении'));

AccountCraft::clearAllCrafts();

echo Api::resultMsg();