<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/includes/config.php';
use User\Account;
use User\Sess;

$Account = Account::auth();

$Account->Sess->goToClient();