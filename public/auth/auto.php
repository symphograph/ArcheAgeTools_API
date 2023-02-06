<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';
use App\User\Account;

$Account = Account::auth();

$Account->Sess->goToClient();