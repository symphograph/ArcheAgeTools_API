<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/includes/config.php';

use User\{ProfLvls, Server};

$Servers = Server::getList() or die(Api::errorMsg('Серверы не найдены'));
$ProfLvls = ProfLvls::getList();
echo Api::resultData(['Servers' => $Servers, 'ProfLvls' => $ProfLvls]);