<?php

use App\User\AccSets;
use Symphograph\Bicycle\Api\Response;
use Symphograph\Bicycle\Errors\ValidationErr;
use App\User\PublicNick;

require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

$AccSets = AccSets::byJwt();
if (empty($_POST['nick'])){
    throw new ValidationErr('nick', 'Ой!');
}

$pubNick = new PublicNick($_POST['nick']);

if ($pubNick->nick === $AccSets->publicNick) {
    Response::success();
}

$pubNick->validation($AccSets);

if ($_POST['save'] ?? false) {
    $AccSets->publicNick = $pubNick->nick;
    $AccSets->putToDB();
}

Response::success();