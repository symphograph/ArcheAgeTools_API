<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/includes/config.php';

use User\{Account};

$Account = Account::byToken($_POST['token'] ?? '')
or die(Api::errorMsg('badToken'));

$Accounts = Account::getList($Account->user_id);
echo Api::resultData(['curAccount'=>$Account,'Accounts' => $Accounts]);