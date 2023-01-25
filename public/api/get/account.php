<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']).'/includes/config.php';
use App\User\{Account, Sess};
use App\Api;

$Account = Account::byToken($_POST['token'] ?? '')
or die(Api::errorMsg('badToken'));
$Account->initOAuthUserData();
$Account->initAvatar();
$Account->initMember();
$Account->AccSets->initProfs();
echo Api::resultData(['curAccount'=>$Account]);