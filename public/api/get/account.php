<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']).'/includes/config.php';
use User\{Account, Sess};

$Account = Account::byToken($_POST['token'] ?? '', true)
or die(Api::errorMsg('badToken'));

echo Api::resultData(['curAccount'=>$Account]);