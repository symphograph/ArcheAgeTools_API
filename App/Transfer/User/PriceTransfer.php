<?php

namespace App\Transfer\User;

use App\DTO\ItemDTO;
use App\DTO\PriceDTO;
use Symphograph\Bicycle\Logs\ErrorLog;
use Symphograph\Bicycle\Logs\Log;

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