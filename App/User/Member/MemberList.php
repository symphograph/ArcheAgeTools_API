<?php

namespace App\User\Member;

use App\Item\Repo\ItemRepo;
use Symphograph\Bicycle\DTO\AbstractList;
use Symphograph\Bicycle\Errors\AppErr;

class MemberList extends AbstractList
{
    /**
     * @var Member[]
     */
    protected array $list = [];

    public static function getItemClass(): string
    {
        return Member::class;
    }

    public static function priceMasters(int $accountId, int $serverGroupId): static
    {
        if ($serverGroupId === 100) {
            throw new AppErr('Server not defined', 'Сервер не выбран');
        }

        $privateIds = ItemRepo::getPrivateIds();

        $sql = "
            select
            sets.accountId,
            sets.avaFileName,
            sets.publicNick,
            pricesCount,
            lastPriceTime,
            if(uf.master > 0,1,0) as isFollow,
            if(flwt.flws, flwt.flws, 0) as followersCount
            from
            (
                select accountId, 
                       serverGroupId,
                       COUNT(*) as pricesCount, 
                       max(updatedAt) as lastPriceTime
                from uacc_prices
                where serverGroupId = :serverGroupId
                and itemId not in (:privateIds)
                group by accountId, serverGroupId
                order by lastPriceTime desc
            ) as tmp
            inner join uacc_settings sets 
                on sets.accountId = tmp.accountId
                and sets.authType != 'default'
            left join uacc_follows uf
                on uf.master = sets.accountId
                and uf.follower = :accountId
                and uf.serverGroupId = tmp.serverGroupId
            left join
            (
                select count(*) as flws, 
                       serverGroupId,
                       max(uf.follower) as follower, 
                       max(uf.master) as master
                from uacc_follows uf
                /*where serverGroupId = tmp.serverGroupId*/
                group by uf.master, serverGroupId
            ) as flwt
            ON sets.accountId = flwt.master
            and tmp.serverGroupId  = flwt.serverGroupId
            order by isFollow desc, 
                     YEAR(lastPriceTime) desc, 
                     MONTH(lastPriceTime) desc, 
                     WEEK(lastPriceTime,1) desc, 
                     (pricesCount>50) desc, 
                     lastPriceTime desc
            LIMIT 100
        ";
        $params = ['serverGroupId' => $serverGroupId,
                   'privateIds' => $privateIds,
                   'accountId'     => $accountId,
                   /*'serverGroup3' => $serverGroup*/];

        return static::bySql($sql, $params);
    }

    /**
     * @return Member[]
     */
    public function getHavingLastPrice(): array
    {
        return array_filter($this->list, fn($el) => !empty($el->LastPricedItem));
    }

    public function initLastPricedItem(int $serverGroupId): static
    {
        foreach ($this->list as $el) {
            $el->initLastPricedItem($serverGroupId);
        }
        return $this;
    }


    /**
     * @return Member[]
     */
    public function getList(): array
    {
        return $this->list;
    }

}