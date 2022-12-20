<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']).'/includes/config.php';
use User\{Account, Sess};

$Account = Account::byToken($_POST['token'] ?? '')
or die(Api::errorMsg('badToken'));
$Account->initOAuthUserData();
$Account->initMember();
$Account->AccSets->initProfs();
echo Api::resultData(['curAccount'=>$Account]);