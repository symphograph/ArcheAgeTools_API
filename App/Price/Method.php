<?php

namespace App\Price;

use App\User\AccSets;

enum Method: string
{
    case byBuffer = 'byBuffer';
    case bySolo = 'bySolo';
    case byAccount = 'byAccount';
    case byAny = 'byAny';
    case byAnyServer = 'byAnyServer';
    case byCraft = 'byCraft';
    case byFriends = 'byFriends';
    case byToNPC = 'byToNPC';
    case byWellKnown = 'byWellKnown';
    case byFromNPC = 'byFromNPC';

    public function label(Price $Price): string
    {
        return match ($this){
            self::bySolo, self::byAccount => self::bySoloLabel($Price),
            self::byToNPC => self::byToNPCLabel(),
            self::byFriends => self::byFriendsLabel($Price),
            self::byWellKnown, self::byAny => self::byAnyLabel($Price),
            self::byFromNPC => self::byFromNPCLabel(),
            self::byCraft => self::byCraftLabel(),
            default => ''
        };
    }

    private static function bySoloLabel(Price $Price): string
    {
        return self::dateFormat($Price->updatedAt) . ' - Ваша цена';
    }

    private static function byToNPCLabel(): string
    {
        return 'Если продать NPC';
    }

    private static function byFriendsLabel(Price $Price): string
    {
        $AuthorAccSets = AccSets::byId($Price->accountId);
        return self::dateFormat($Price->updatedAt) . ' - ' . $AuthorAccSets->publicNick;
    }

    private static function byAnyLabel(Price $Price): string
    {
        $AuthorAccSets = AccSets::byId($Price->accountId);
        return self::dateFormat($Price->updatedAt) . ' - ' . $AuthorAccSets->publicNick;
    }

    private static function dateFormat(string $datetime): string
    {
        return date('d.m.Y H:i', strtotime($datetime));
    }

    private static function byFromNPCLabel(): string {
        return 'Куплено у NPC';
    }

    private static function byCraftLabel(): string {
        return 'Себестоимость (крафт)';
    }
}
