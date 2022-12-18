<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/includes/config.php';
use User\{Server};

$Servers = Server::getList() or die(Api::errorMsg('Серверы не найдены'));;
echo Api::resultData(['Servers' => $Servers]);