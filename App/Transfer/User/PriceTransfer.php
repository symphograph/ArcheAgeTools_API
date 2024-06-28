<?php

namespace App\Transfer\User;

use Symphograph\Bicycle\Logs\ErrorLog;

class PriceTransfer
{


    public static function byId(int $accountId, int $oldId): void
    {
        try{
            $oldMailUser = MailruOldUser::byId($oldId);
            $oldMailUser->importPrices($accountId);
        } catch (\Throwable $err) {
            ErrorLog::writeToLog($err);
        }
    }
}