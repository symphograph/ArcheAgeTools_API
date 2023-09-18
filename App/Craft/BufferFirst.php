<?php

namespace App\Craft;

use App\AppStorage;
use App\DTO\CraftDTO;
use App\User\AccSettings;
use PDO;
use Symphograph\Bicycle\Helpers;

class BufferFirst
{
    const sortArgs = [
        'isUBest' => 'desc',
        'spmu' => 'asc',
        'craftCost' => 'asc',
        'resultAmount' => 'desc'
    ];
    public int $accountId;
    public int $craftId;
    public int $craftCost;
    public int $matSPM;

    public ?int $resultItemId;
    public ?string $itemName;
    public ?int $categId;
    public ?int $resultAmount;
    public ?string $doodName;
    public ?bool $isUBest;
    public ?int $spm;
    public ?int $spmu;
    public ?int $kry;
    public ?int $deep;

    public function __set(string $name, $value): void
    {
    }

    public static function clearStorage(): void
    {
        AppStorage::getSelf()->CraftsFirst = [];
    }

    public static function clearDB(): void
    {
        $AccSets = AccSettings::byGlobal();
        qwe("
            delete from craftBuffer 
                   where accountId = :accountId",
            ['accountId' => $AccSets->accountId]
        );
    }

    public static function putToDB(int $craftId, int $craftCost, int $matSPM): void
    {
        $AccSets = AccSettings::byGlobal();
        qwe("
            replace into craftBuffer 
                (accountId, craftId, craftCost, matSPM) 
            VALUES 
                (:accountId, :craftId, :craftCost, :matSPM)", [
                'accountId' => $AccSets->accountId,
                'craftId'   => $craftId,
                'craftCost' => $craftCost,
                'matSPM'    => $matSPM
            ]
        );
    }

    public static function putToStorage(Craft $craft, int $craftCost, int $matSPM): void
    {
        $bufferFirst = new self();
        $bufferFirst->craftId = $craft->id;
        $bufferFirst->craftCost = $craftCost;
        $bufferFirst->matSPM = $matSPM;
        $bufferFirst->spm = $craft->spm;
        $bufferFirst->resultItemId = $craft->resultItemId;
        $bufferFirst->deep = $craft->deep;
        $bufferFirst->resultAmount = $craft->resultAmount;
        $bufferFirst->isUBest = Craft::getUBest($craft->resultItemId) === $craft->id;
        $bufferFirst->initSPMU();
        AppStorage::getSelf()->CraftsFirst[] = $bufferFirst;
    }

    private function initSPMU(): void
    {
        $kry = $this->getKRY();
        $spmu = sqrt($kry);
        $this->spmu = round($spmu);
    }

    private function getKRY(): int
    {
        $buyOnlyItems = AppStorage::getSelf()->buyOnlyItems;
        if(in_array($this->resultItemId,$buyOnlyItems)){
            return $this->craftCost;
        }
        $spm = $this->spm - $this->matSPM;
        $kry = $spm + $this->matSPM;
        $kry = sqrt($kry);
        $kry = round($kry);
        $kry *= $this->craftCost;
        $kry += $this->craftCost;
        return abs($kry);
    }

    /**
     * @return array<self>|false
     */
    public static function getCounted(): array|false
    {

        $list = AppStorage::getSelf()->CraftsFirst;
        $list = Helpers::sortMultiArrayByProp($list, self::sortArgs);

        self::clearStorage();


        return $list;
    }

}