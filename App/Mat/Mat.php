<?php

namespace App\Mat;

use App\Craft\GroupCraft;
use App\Item\Item;
use App\Item\Repo\ItemRepo;
use App\Price\Price;
use PDO;

class Mat extends MatDTO
{
    public int    $id;
    public ?int   $resultItemId;
    public ?int   $grade;
    public ?bool  $craftable;
    public ?Item  $Item;
    public ?bool  $isBuyOnly;
    public ?Price $Price;
    public bool   $isCounted;


    public static function byIds(int $matId, int $craftId): self|bool
    {
        $qwe = qwe("
            select i.id,
                   cm.craftId,
                   c.resultItemId,
                   cm.matGrade as grade, 
                   cm.need,
                   i.craftable
            from craftMaterials cm
            inner join items i 
                on cm.itemId = i.id
            inner join crafts c 
                on cm.craftId = c.id
                and c.onOff
            where craftId = :craftId and i.id = :matId",
            ['craftId' => $craftId, 'matId' => $matId]
        );
        if (!$qwe || !$qwe->rowCount()) {
            return false;
        }
        $mat = $qwe->fetchObject(self::class);
        $mat->initItem();
        return $mat;
    }

    public function initItem(): void
    {
        $this->Item = ItemRepo::byId($this->id);
        $this->craftable = $this->Item->craftable;
    }

    /**
     * @return int[]
     */
    public static function getCraftMatIDs(int $craftId): array
    {
        $qwe = qwe("
            select itemId
            from craftMaterials
            where craftId = :craftId",
            ['craftId' => $craftId]
        );
        if (!$qwe || !$qwe->rowCount()) {
            return [];
        }
        return $qwe->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * @return int[]
     */
    public static function allPotentialIds(int $itemId, array &$matIDsArr = []): array
    {
        $mats = MatList::counted($itemId)->getList();

        foreach ($mats as $mat) {
            if (in_array($mat->id, $matIDsArr)) {
                continue;
            }

            $matIDsArr[] = $mat->id;
            if ($mat->craftable) {
                $matIDsArr = self::allPotentialIds($mat->id, $matIDsArr);
            }
        }
        return $matIDsArr;
    }

    public function initPrice(): bool
    {
        if ($this->id === 500) {
            $this->Price = Price::byParams(itemId: 500, price: 1);
            return true;
        }

        $this->initIsByOnly();

        if ($this->isBuyOnly) {
            if ($this->initPriceBySaved()) {
                return true;
            }

            if (GroupCraft::byCraftId($this->craftId)) {
                return self::initPriceByCraft();
            }
            return false;
        }

        if ($this->need < 0) {
            if (GroupCraft::byCraftId($this->craftId)) {
                return self::initPriceByCraft();
            }
            if ($this->craftable && self::initPriceByCraft()) {
                return true;
            }
            return self::initPriceBySaved();
        }

        if ($this->Item->isTradeNPC && !$this->Item->craftable) {
            return self::initPriceFromNPC();
        }

        if ($this->Item->craftable) {
            return self::initPriceByCraft();
        }

        return self::initPriceBySaved();
    }

    private function initIsByOnly(): void
    {
        $this->Item->initIsBuyOnly();
        $this->isBuyOnly = $this->Item->isBuyOnly;
    }

    private function initPriceBySaved(): bool
    {
        if ($Price = Price::bySaved($this->id)) {
            $this->Price = $Price;
            return true;
        }

        return false;
    }

    private function initPriceByCraft(): bool
    {
        if ($Price = Price::byCraft($this->id)) {
            $this->Price = $Price;
            return true;
        }
        if ($Price = Price::byBuffer($this->id)) {
            $this->Price = $Price;
            return true;
        }
        return false;
    }

    private function initPriceFromNPC(): bool
    {
        if ($this->Item->currencyId === 500) {
            $this->Price = Price::byParams(
                itemId: $this->id,
                price: $this->Item->priceFromNPC,
                method: 'byFromNPC'
            );
            return true;
        }

        $Price = Price::bySaved($this->id);
        if ($Price) {
            $this->Price = $Price;
            return true;
        }

        $vPrice = Price::bySaved($this->Item->currencyId);
        if ($vPrice) {
            $this->Price = Price::byParams(
                itemId: $this->id,
                price: $vPrice->price * $this->Item->priceFromNPC,
                method: 'byFromNPC'
            );
            return true;
        }
        return false;
    }
}