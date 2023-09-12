<?php

namespace App\Transfer\Items;

use Symphograph\Bicycle\DTO\DTOTrait;

class DescrSectionType
{
    use DTOTrait;
    const tableName = 'itemDescrSectionTypes';
    public int $id;
    public string $name;
    public bool $isVisible;

    public static function byName(string $name): self
    {
        $qwe = qwe("
            select * from itemDescrSectionTypes 
            where name = :name",
            ['name' => $name]
        );
        return $qwe->fetchObject(self::class);
    }

    public static function getIdByName(string $name): int
    {
        $Self = self::byName($name);
        return $Self->id;
    }
}