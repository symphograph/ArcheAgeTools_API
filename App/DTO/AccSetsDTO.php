<?php

namespace App\DTO;

use Symphograph\Bicycle\DTO\DTOTrait;

class AccSetsDTO
{
    use DTOTrait;

    const string tableName = 'uacc_settings';
    const string colId     = 'accountId';

    public int     $accountId     = 0;
    public int     $serverGroupId = 100;
    public string  $publicNick    = 'Никнейм';
    public int     $grade         = 1;
    public int     $mode          = 1;
    public ?int    $old_id;
    public bool    $siol          = false;
    public ?string $authType      = 'default';
    public ?string $avaFileName;

}