<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\User\{Account};
use App\Api;
use App\Env\Env;
use App\Errors\AccountErr;
use App\Errors\MyErrors;

$Account = Account::byToken();

try {
    $Account->initOAuthUserData();
    $Account->initAvatar();
    $Accounts = Account::getList($Account->user_id);
} catch (MyErrors $err) {
    Api::errorResponse($err->getResponseMsg());
}

Api::dataResponse(['curAccount'=>$Account,'Accounts' => $Accounts]);