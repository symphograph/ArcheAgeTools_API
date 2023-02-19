<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';
use App\User\{Account, Sess};
use App\Api;
use App\Errors\MyErrors;

$Account = Account::byToken();
try{
    $Account->initOAuthUserData();
    $Account->initAvatar();
    $Account->initMember();
    $Account->AccSets->initProfs();
} catch (MyErrors $err) {
    Api::errorResponse($err->getResponseMsg());
}
Api::dataResponse(['curAccount'=>$Account]);