<?php

namespace App\Prof;

use App\Craft\UCraft\UCraft;
use App\User\AccSets;
use App\User\User;
use JetBrains\PhpStorm\NoReturn;
use Symphograph\Bicycle\Api\Response;
use Symphograph\Bicycle\HTTP\Request;

class ProfCTRL
{

    #[NoReturn] public static function setLvl(): void
    {
        User::auth();
        Request::checkEmpty(['profId', 'lvl']);

        Prof::saveLvl(AccSets::$current->accountId, $_POST['profId'], $_POST['lvl']);
        UCraft::clearAllCrafts();

        Response::success();
    }
}