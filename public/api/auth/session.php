<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\User\{Account};
use Symphograph\Bicycle\Api\Response;
use Symphograph\Bicycle\Errors\MyErrors;

$Account = Account::byToken();
$Account->initOAuthUserData();
$Account->initAvatar();
$Accounts = Account::getList($Account->user_id);

Response::data(['curAccount'=>$Account,'Accounts' => $Accounts]);