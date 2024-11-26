<?php

namespace App\User\Member;

use App\User\AccSets;
use App\User\User;
use JetBrains\PhpStorm\NoReturn;
use Symphograph\Bicycle\Api\Response;
use Symphograph\Bicycle\HTTP\Request;

class MemberCTRL
{

    #[NoReturn] public static function list(): void
    {
        User::auth();
        Request::checkSet(['serverGroupId']);

        $accountId = AccSets::$current->accountId;
        $members = MemberList::priceMasters($accountId, $_POST['serverGroupId'])
            ->initLastPricedItem($_POST['serverGroupId'])
            ->getHavingLastPrice();

        Response::data($members);
    }

    public static function get(): void
    {
    }

    #[NoReturn] public static function followToggle(): void
    {
        User::auth();
        Request::checkSet(['isFollow']);
        Request::checkEmpty(['serverGroupId', 'master']);

        $accountId = AccSets::$current->accountId;
        $_POST['isFollow']
            ? Member::setFollow($accountId, $_POST['master'], $_POST['serverGroupId'])
            : Member::unsetFollow($accountId, $_POST['master'], $_POST['serverGroupId']);

        Response::success();
    }
}