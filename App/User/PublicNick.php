<?php

namespace App\User;

use Symphograph\Bicycle\Errors\ValidationErr;
use Symphograph\Bicycle\Helpers;

class PublicNick
{
    const minLen = 3;
    const maxLen = 20;

    public function __construct(public string $nick)
    {
        $this->nick = Helpers::sanitazeName($nick);
    }

    public function validation(AccSettings $AccSets): void
    {
        $errText = match (false) {
            self::validMinLen() => 'Не менее ' . self::minLen,
            self::validMaxLen() => 'Не более ' . self::minLen,
            self::isFree($AccSets) => 'Ник занят',
            default => ''
        };
        if(!empty($errText)){
            throw new ValidationErr($errText, $errText);
        }
    }

    private function validMinLen(): bool
    {
        return mb_strlen($this->nick) >= self::minLen;
    }

    private function validMaxLen(): bool
    {
        return mb_strlen($this->nick) <= self::maxLen;
    }

    private function isFree(AccSettings $AccSets): bool
    {
        if (mb_strtolower($this->nick) === mb_strtolower($AccSets->publicNick)){
            return true;
        }
        return !$AccSets::isNickExist($this->nick);
    }
}