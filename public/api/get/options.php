<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use Symphograph\Bicycle\Api\Response;
use Symphograph\Bicycle\Errors\AccountErr;
use App\User\{ProfLvls, Server};

$Servers = Server::getList()
    or throw new AccountErr('servers is lost', 'Серверы не найдены');
$ProfLvls = ProfLvls::getList()
    or throw new AccountErr('ProfLvls is lost', 'Профессии не найдены');

Response::data(['Servers' => $Servers, 'ProfLvls' => $ProfLvls]);