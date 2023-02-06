<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\User\{Account};
use App\Api;

$Account = Account::byToken($_POST['token'] ?? '')
or die(Api::errorMsg('badToken'));
$Account->initOAuthUserData();
$Account->initAvatar();
$Accounts = Account::getList($Account->user_id);
echo Api::resultData(['curAccount'=>$Account,'Accounts' => $Accounts]);