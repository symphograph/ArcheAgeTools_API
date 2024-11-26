<?php

namespace App;

class UserStorage
{
    public static self $storage;
    public array $oldNicks = [];

    public static function getSelf(): UserStorage
    {
        if(!isset(self::$storage)){
            self::$storage = new self();
        }
        return self::$storage;
    }
}