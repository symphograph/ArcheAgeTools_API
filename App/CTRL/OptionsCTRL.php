<?php

namespace App\CTRL;

use App\Item\Category;
use App\Item\ItemList;
use App\Prof\Lvl\ProfLvlList;
use App\Server\ServerList;
use App\User\User;
use App\Zone\Zone;
use JetBrains\PhpStorm\NoReturn;
use Symphograph\Bicycle\Api\Response;

class OptionsCTRL
{

    #[NoReturn] public static function getMain(): void
    {
        $Servers = ServerList::all()->getList();
        $ServerGroupList = ServerList::all()->getServerGroups();
        $ProfLvls = ProfLvlList::all()->initLabel()->getList();

        Response::data([
            'Servers'         => $Servers,
            'ProfLvls'        => $ProfLvls,
            'ServerGroupList' => $ServerGroupList
        ]);
    }

    #[NoReturn] public static function getZones(): void
    {
        User::auth();
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
        //User::auth();
        $tree = Category::getTree();

        Response::data($tree);
    }

    #[NoReturn] public static function getSearchList(): void
    {
        $List = ItemList::allOn()->getList();

        Response::data($List);
    }
}