<?php

namespace App\CTRL;

use App\Auth\Mailru\MailruUserClient;
use App\Transfer\User\MailruOldUser;
use App\Transfer\User\PriceTransfer;
use App\User\AccSettings;
use App\User\User;
use Symphograph\Bicycle\Api\Response;
use Symphograph\Bicycle\Errors\AccountErr;
use Symphograph\Bicycle\Errors\ValidationErr;
use Symphograph\Bicycle\Helpers;
use Symphograph\Bicycle\Token\AccessTokenData;

class AccSettingsCTRL extends AccSettings
{

    public static function get(): void
    {
        self::tryByJwt();
        self::tryByOld();
        self::byDefault();
        throw new AccountErr('Settings is miss', 'Настройки не загрузились');
    }

    private static function tryByJwt(): void
    {
        $AccSets = self::byJwt();
        if(!$AccSets) return;
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

        $AccSets = AccSettings::byOldServer($accountId, $MailruUser->email);
        if(!$AccSets){
            return;
        }
        $AccSets->avaFileName = AccessTokenData::avaFileName();
        $AccSets->putToDB();

        Response::data($AccSets);
    }

    private static function byDefault(): void
    {
        $AccSets = self::getDefault(AccessTokenData::accountId());
        $AccSets->initData();
        $AccSets->avaFileName = AccessTokenData::avaFileName();
        $AccSets->putToDB();
        Response::data($AccSets);
    }

    public static function list(): void
    {
        User::auth();
        $ids = $_POST['ids'] ?? throw new ValidationErr();
        Helpers::isArrayIntList($_POST['ids'] ?? []) or throw new ValidationErr();
        $list = [];
        foreach ($_POST['ids'] as $accountId){
            $list[] = AccSettings::byId($accountId);
        }
        Response::data($list);
    }


}