<?php

namespace App\CTRL;

use App\Item\Category;
use App\Item\Item;
use App\Packs\Zone;
use App\ServerList;
use App\User\AccSets;
use App\User\ProfLvls;
use App\User\Server;
use JetBrains\PhpStorm\NoReturn;
use Symphograph\Bicycle\Api\Response;
use Symphograph\Bicycle\Errors\AccountErr;
use Symphograph\Bicycle\Errors\AppErr;

class OptionsCTRL
{

    public static function getMain(): void
    {
        $Servers = Server::getList()
        or throw new AccountErr('servers is lost', 'Серверы не найдены');
        $ServerGroupList = ServerList::getServerGroups();
        $ProfLvls = ProfLvls::getList()
        or throw new AccountErr('ProfLvls is lost', 'Профессии не найдены');

        Response::data([
            'Servers'         => $Servers,
            'ProfLvls'        => $ProfLvls,
            'ServerGroupList' => $ServerGroupList
        ]);
    }

    #[NoReturn] public static function getZones(): void
    {
        AccSets::byJwt();
        $zonesFrom = Zone::getFromsGroupBySide();

        $allZonesTo = [];
        foreach ($zonesFrom as $side) {
            foreach ($side as $zoneFrom){
                foreach ($zoneFrom->ZonesTo as $zoneTo){
                    $allZonesTo[$zoneFrom->side][$zoneTo->id] = $zoneTo;
                }
            }
        }
        sort($allZonesTo[1]);
        sort($allZonesTo[2]);
        sort($allZonesTo[3]);

        Response::data(['zonesFrom' => $zonesFrom, 'allZonesTo' => $allZonesTo]);
    }

    #[NoReturn] public static function getCategories(): void
    {
        $List = Category::getTree();
        Response::data($List);
    }

    public static function getSearchList(): void
    {
        $List = Item::searchList()
        or throw new AppErr('searchList is empty', 'Предметы не найдены');

        Response::data($List);
    }
}