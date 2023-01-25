<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']).'/includes/config.php';

use App\Api;
use App\Craft\AccountCraft;
use App\User\Account;

$Account = Account::byToken($_POST['token'] ?? '')
or die(Api::errorMsg('Обновите страницу'));

$craftId = intval($_POST['craftId'] ?? 0)
or die(Api::errorMsg('craftId'));

AccountCraft::delUBest($Account->id, $craftId)
or die(Api::errorMsg('Ошибка при удалении'));

AccountCraft::clearAllCrafts();

echo Api::resultMsg();