<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\Api;
use App\Craft\AccountCraft;
use App\User\Account;

$Account = Account::byToken();

$craftId = intval($_POST['craftId'] ?? 0)
or die(Api::errorMsg('craftId'));

AccountCraft::delUBest($Account->id, $craftId)
or die(Api::errorMsg('Ошибка при удалении'));

AccountCraft::clearAllCrafts();

echo Api::resultMsg();