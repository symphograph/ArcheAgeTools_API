<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\User\{Account, Prof};
use App\Api;
use App\Craft\AccountCraft;

$Account = Account::byToken();

$profId = intval($_POST['profId'] ?? 0)
    or die(Api::errorMsg('profId'));

$lvl = intval($_POST['lvl'] ?? 0)
    or die(Api::errorMsg('lvl'));

Prof::saveLvl($Account->id, $profId, $lvl)
    or die(Api::errorMsg('Ошибка при сохранении'));

AccountCraft::clearAllCrafts();

echo Api::resultMsg();