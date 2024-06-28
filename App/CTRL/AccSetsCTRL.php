<?php

namespace App\CTRL;

use App\Auth\Mailru\MailruUserClient;
use App\Transfer\User\MailruOldUser;
use App\Transfer\User\PriceTransfer;
use App\User\AccSets;
use App\User\User;
use JetBrains\PhpStorm\NoReturn;
use Symphograph\Bicycle\Api\Response;
use Symphograph\Bicycle\Errors\AccountErr;
use Symphograph\Bicycle\Errors\ValidationErr;
use Symphograph\Bicycle\Helpers;
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
            $AccSets = self::byJwt();
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
        $ids = $_POST['ids'] ?? throw new ValidationErr();
        Helpers::isArrayIntList($ids ?? []) or throw new ValidationErr();
        $list = [];
        foreach ($_POST['ids'] as $accountId){
            $list[] = AccSets::byId($accountId);
        }
        Response::data($list);
    }


}