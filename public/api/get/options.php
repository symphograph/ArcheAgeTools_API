<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\Api;
use App\Errors\AccountErr;
use App\User\{ProfLvls, Server};

try {
    $Servers = Server::getList()
    or throw new AccountErr('servers is lost', 'Серверы не найдены');
    $ProfLvls = ProfLvls::getList()
    or throw new AccountErr('ProfLvls is lost', 'Профессии не найдены');
} catch (AccountErr $err){
    Api::errorResponse($err->getMessage());
}
Api::dataResponse(['Servers' => $Servers, 'ProfLvls' => $ProfLvls]);