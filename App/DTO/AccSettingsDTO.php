<?php

namespace App\DTO;

use Symphograph\Bicycle\DB;

class AccSettingsDTO extends DTO
{
    const tableName = 'uacc_settings';
    public int    $accountId   = 0;
    public int    $serverId    = 9;
    public string $publicNick  = 'Никнейм';
    public int    $grade       = 1;
    public int    $mode        = 1;
    public ?int   $old_id;
    public bool   $siol        = false;
    public ?string $authType = 'default';
    public ?string $avaFileName;

    public static function byId(int $accountId): self|bool
    {
        $qwe = qwe("
            select * from uacc_settings 
            where accountId = :accountId",
            ['accountId'=>$accountId]
        );
        return $qwe->fetchObject(self::class);
    }

    public function putToDB(): void
    {
        $params = DB::initParams($this);
        DB::replace(self::tableName, $params);
    }
}