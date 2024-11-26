<?php

namespace App\Packs;

use App\DTO\PackRouteDTO;
use App\Zone\Repo\ZoneRepo;
use App\Zone\Zone;
use Symphograph\Bicycle\Errors\AppErr;
use Symphograph\Bicycle\Logs\Log;

class PackRoute extends PackRouteDTO
{

    public ?string $name;
    public Pack $Pack;
    public Zone $ZoneFrom;
    public Zone $ZoneTo;
    public Freshness $Freshness;
    public ?array $Mats;


    public function initData(): static
    {
        $this->initZones();
        $this->initPack();
        $this->initFreshness();
        return $this;
    }

    public function initProfit(): void
    {
        $this->Pack->initCraftData();
    }

    private function initZones(): void
    {
        $this->ZoneFrom = ZoneRepo::get($this->zoneFromId);
        $this->ZoneTo = ZoneRepo::get($this->zoneToId);
    }

    private function initPack(): void
    {
        $this->Pack = Pack::byIds($this->itemId, $this->zoneFromId)
        or throw new AppErr(
            "Pack $this->itemId from $this->zoneFromId does not exist",
            'Пак не найден'
        );
        $this->name = $this->Pack->name;
    }

    private function initFreshness(): void
    {
        $Freshness = Freshness::byId($this->Pack->freshId);
        $this->Freshness = $Freshness;
    }

}