<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';
use App\User\{Account, Sess};
use App\Api;

$Account = Account::byToken()
or die(Api::errorMsg('badToken'));
$Account->initOAuthUserData();
$Account->initAvatar();
$Account->initMember();
$Account->AccSets->initProfs();
echo Api::resultData(['curAccount'=>$Account]);