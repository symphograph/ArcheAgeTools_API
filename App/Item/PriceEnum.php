<?php

namespace App\Item;

use App\User\Account;

class PriceEnum
{
    public static function label(Price $Price): string
    {
        return match ($Price->method){
            'bySolo', 'byAccount' => self::bySolo($Price),
            'byToNPC' => self::byToNPC(),
            'byFriends' => self::byFriends($Price),
            'byWellKnown', 'byAny' => self::byAny($Price),
            'byFromNPC' => self::byFromNPC(),
            'byCraft' => self::byCraft(),
            default => ''
        };
    }

    private static function bySolo(Price $Price): string
    {
        return self::dateFormat($Price->datetime) . ' - Ваша цена';
    }

    private static function byToNPC(): string
    {
        return 'Если продать NPC';
    }

    private static function byFriends(Price $Price): string
    {
        $Author = Account::byId($Price->accountId);
        return self::dateFormat($Price->datetime) . ' - ' . $Author->AccSets->publicNick;
    }

    private static function byAny(Price $Price): string
    {
        $Author = Account::byId($Price->accountId);
        return self::dateFormat($Price->datetime) . ' - ' . $Author->AccSets->publicNick;
    }

    private static function dateFormat(string $datetime): string
    {
       return date('d.m.Y H:i', strtotime($datetime));
    }

    private static function byFromNPC(): string {
        return 'Куплено у NPC';
    }
    private static function byCraft(): string {
        return 'Себестоимость (крафт)';
    }
}