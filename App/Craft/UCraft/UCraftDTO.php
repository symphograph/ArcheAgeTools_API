<?php

namespace App\Craft\UCraft;

use App\Craft\Craft\CraftDTO;
use Symphograph\Bicycle\DTO\DTOTrait;
use Symphograph\Bicycle\Errors\AppErr;
use Symphograph\Bicycle\PDO\DB;

class UCraftDTO
{
    use DTOTrait;
    const string tableName = 'uacc_crafts';

    public ?int           $accountId;
    public ?int           $serverGroupId;
    public ?int           $craftId;
    public ?int           $itemId;
    public ?bool          $isBest;
    public ?int           $craftCost;
    public ?string        $datetime;
    public float|int|null $laborTotal;
    public ?int           $spmu;
    public ?string        $allMats;

    protected function beforePut(): void
    {
        $this->datetime = date('Y-m-d H:i:s');
    }

    public static function newInstance(
        int            $accountId,
        int            $serverGroupId,
        int            $craftId,
        int            $itemId,
        int            $isBest,
        int            $craftCost,
        int|float|null $laborTotal,
        int            $spmu,
        ?string        $allMats
    ): static
    {
        $Craft = new static();
        $Craft->accountId = $accountId;
        $Craft->serverGroupId = $serverGroupId;
        $Craft->craftId = $craftId;
        $Craft->itemId = $itemId;
        $Craft->isBest = $isBest;
        $Craft->craftCost = $craftCost;
        $Craft->laborTotal = $laborTotal ?? 0;
        $Craft->spmu = $spmu;
        $Craft->allMats = $allMats;

        return $Craft;
    }

    public static function setUBest(int $accountId, int $craftId): void
    {
        $craft = CraftDTO::byId($craftId);
        $sql = "
            replace into uacc_bestCrafts 
                (accountId, itemId, craftId) 
            VALUES 
                (:accountId, :itemId, :craftId)";

        $params = ['accountId' => $accountId, 'itemId' => $craft->resultItemId, 'craftId' => $craftId];
        DB::qwe($sql,$params) or throw new AppErr('error on Replace uBestCraft', 'Не сохранилось');
    }
}