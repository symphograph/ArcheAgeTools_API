<?php

namespace App\Zone\Repo;

use App\Zone\Zone;

class RepoMemory implements RepoITF
{
    static array $zones = [];
    static array $sides = [];

    static function get(int $id): ?Zone
    {
        return static::$zones[$id] ?? null;
    }

    static function set(Zone $zone): void
    {
        static::$zones[$zone->id] = $zone;
    }


    static function all(): array
    {
        return static::$zones;
    }

    static function setAll(array $zones): void
    {
        static::$zones = [];
        foreach ($zones as $zone) {
            static::$zones[$zone->id] = $zone;
        }
    }
}