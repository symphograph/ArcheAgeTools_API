<?php

namespace App\User;

use App\Auth\Mailru\MailruUserClient;
use App\Craft\UCraft\UCraft;
use App\Transfer\User\MailruOldUser;
use JetBrains\PhpStorm\NoReturn;
use Symphograph\Bicycle\Api\Response;
use Symphograph\Bicycle\Errors\AccountErr;
use Symphograph\Bicycle\Errors\ValidationErr;
use Symphograph\Bicycle\Helpers\Arr;
use Symphograph\Bicycle\HTTP\Request;
use Symphograph\Bicycle\Token\AccessTokenData;

class AccSetsCTRL extends AccSets
{
    #[NoReturn] public static function get(): void
    {
        self::tryByJwt();
        self::tryByOld();
        self::createByToken();
    }

    private static function tryByJwt(): void
    {

        try {
            $AccSets = AccSets::byJwt();
            if(!$AccSets) return;
        } catch (\Throwable) {
            return;
        }

        if(!empty($AccSets->old_id)){
            $oldUser = MailruOldUser::byId($AccSets->old_id);
            $oldUser->updateIfExist();
        }
        Response::data($AccSets);
    }

    private static function tryByOld(): void
    {
        if(AccessTokenData::authType() !== 'mailru'){
            return;
        }
        $accountId = AccessTokenData::accountId();
        $MailruUser = MailruUserClient::byAccountId($accountId)
            or throw new AccountErr("account $accountId is error");

        $AccSets = AccSets::byOldServer($accountId, $MailruUser->email);
        if(!$AccSets){
            return;
        }
        $AccSets->avaFileName = AccessTokenData::avaFileName();
        $AccSets->putToDB();

        Response::data($AccSets);
    }

    #[NoReturn] private static function createByToken(): void
    {
        $AccSets = self::getDefault(AccessTokenData::accountId());
        $AccSets->initData();
        $AccSets->avaFileName = AccessTokenData::avaFileName();
        $AccSets->authType = AccessTokenData::authType();
        $AccSets->putToDB();
        Response::data($AccSets);
    }

    public static function list(): void
    {
        User::auth();
        Request::checkEmpty(['ids']);

        Arr::isArrayIntList($_POST['ids'] ?? []) or throw new ValidationErr();

        $list = [];
        foreach ($_POST['ids'] as $accountId){
            $list[] = AccSets::byId($accountId);
        }
        Response::data($list);
    }

    #[NoReturn] public static function setNick(): void
    {
        User::auth();
        Request::checkEmpty(['nick']);
        Request::checkSet(['save']);

        $pubNick = new PublicNick($_POST['nick']);
        $AccSets = AccSets::$current;

        if ($pubNick->nick === $AccSets->publicNick) {
            Response::success();
        }

        $pubNick->validation(AccSets::$current);

        if($_POST['save'] === true){
            $AccSets->publicNick = $pubNick->nick;
            $AccSets->putToDB();
        }

        Response::success();
    }

    #[NoReturn] public static function setServerGroup(): void
    {
        User::auth();
        Request::checkEmpty(['serverGroupId']);

        $AccSets = AccSets::$current;
        $AccSets->serverGroupId = $_POST['serverGroupId'];
        $AccSets->putToDB();

        Response::success();
    }

    #[NoReturn] public static function setMode(): void
    {
        User::auth();
        Request::checkEmpty(['mode']);

        $AccSets = AccSets::$current;
        $AccSets->mode = $_POST['mode'];
        $AccSets->putToDB();

        UCraft::clearAllCrafts();
        Response::success();
    }


}