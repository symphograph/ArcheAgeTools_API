<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/includes/config.php';
use User\{Pers, Server};

$Perses = Pers::byUser(1);
$Servers = Server::getList() or die(Api::errorMsg('Серверы не найдены'));;
echo Api::resultData(['Perses'=> $Perses, 'Servers' => $Servers]);