<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';
use App\Debug;
use App\Transfer\User\MailruOldUser;
use App\Transfer\User\MailRuUserTransfer;

$start = microtime(true);
echo Debug::header();

//$List = MailruOldUser::getList();
MailRuUserTransfer::importUsers(1000000);

echo Debug::footer($start);