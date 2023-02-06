<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use App\Api;
use App\User\{ProfLvls, Server};

$Servers = Server::getList() or die(Api::errorMsg('Серверы не найдены'));
$ProfLvls = ProfLvls::getList();
echo Api::resultData(['Servers' => $Servers, 'ProfLvls' => $ProfLvls]);