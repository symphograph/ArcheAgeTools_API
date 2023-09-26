<?php

namespace App;

class UserStorage
{
    public array $oldNicks = [];

    public static function getSelf()
    {
        global $UserStorage;
        if(!isset($UserStorage)){
            $UserStorage = new self();
        }
        return $UserStorage;
    }
}