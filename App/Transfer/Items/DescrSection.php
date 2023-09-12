<?php

namespace App\Transfer\Items;

use Symphograph\Bicycle\DTO\DTOTrait;

class DescrSection
{
    use DTOTrait;

    const tableName = 'itemDescriptions';
    const colId = 'itemId';

    public int    $itemId;
    public int    $sectionTypeId;
    public string $sectionTypeName;
    public string $content;

}