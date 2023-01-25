<?php

namespace App\Packs;

class PackType
{
    public int    $id;
    public string $name;
    public ?int   $passLabor;
    public ?int   $freshGroup;

    public static function getList()
    {
        $qwe = qwe("select * from packTypes");
    }
}