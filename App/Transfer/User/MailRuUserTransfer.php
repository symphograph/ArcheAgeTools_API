<?php

namespace App\Transfer\User;

use App\AppStorage;
use App\Auth\Mailru\MailruUserClient;
use App\User\AccSettings;
use Symphograph\Bicycle\Logs\Log;

class MailRuUserTransfer
{
    public static function importUsers($limit = 10): bool
    {
        $List = MailruOldUser::getList();
        if (!$List) {
            Log::msg("List is empty");
            return false;
        }
        AppStorage::getSelf()->oldNicks = array_column($List, 'user_nick');
        printr(AppStorage::getSelf()->oldNicks);
        die();

        foreach ($List as $oldMailUser) {
            if ($limit < 1) break;
            Log::msg("$oldMailUser->email started");

            qwe("START TRANSACTION");
            if ($oldMailUser->updateIfExist()) {
                Log::msg("$oldMailUser->email updated");
                continue;
            }
            if(!$oldMailUser->import()){
                Log::msg("Import $oldMailUser->email is Error");
                qwe("ROLLBACK");
                continue;
            }
            $limit--;
            qwe("COMMIT");
            Log::msg("$oldMailUser->email Added");
        }
        return true;
    }






}