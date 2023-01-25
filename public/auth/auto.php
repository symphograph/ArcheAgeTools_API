<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/includes/config.php';
use App\User\Account;

$Account = Account::auth();

$Account->Sess->goToClient();