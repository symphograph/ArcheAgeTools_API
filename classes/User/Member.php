<?php

namespace User;

use Item\Item;
use PDO;

class Member
{
    public int $account_id;
    public bool $isFollow;
    public int $pricesCount;
    public int $followersCount;

    /**
     * @var array<int>
     */
    public array $followMasters;

    public function __set(string $name, $value): void
    {
    }

    /**
     * @return array<int>
     */
    public static function getFollowMasters(int $follower): array
    {
        $qwe = qwe("select master from uacc_follows where follower = :follower");
        if(!$qwe || !$qwe->rowCount()){
            return [];
        }
        return $qwe->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * @return array<self>
     */
    public static function getList(int $account_id, int $serverGroup): array
    {
        $privateItemsStr = implode(',', Item::privateItems());
        $qwe = qwe("
            select
            uAcc.id as account_id,
            pricesCount,
            lastPriceTime,
            if(uf.master > 0,1,0) as isFollow,
            if(flwt.flws, flwt.flws, 0) as followersCount
            from
            (
                select account_id, COUNT(*) as pricesCount, max(datetime) as lastPriceTime
                from uacc_prices
                where serverGroup = :serverGroup1
                  and item_id not in ( $privateItemsStr )
                group by account_id
                order by lastPriceTime desc
            ) as tmp
            inner join user_accounts uAcc 
                on uAcc.id = tmp.account_id
                and uAcc.authTypeId > 1
            left join uacc_follows uf
                on uf.master = uAcc.id
                and uf.follower = :account_id
                and uf.serverGroup = :serverGroup2
            left join
            (
                select count(*) as flws, 
                       max(uf.follower) as follower, 
                       max(uf.master) as master
                from uacc_follows uf
                where serverGroup = :serverGroup3
                group by uf.master
            ) as flwt
            ON uAcc.id = flwt.master
            order by isFollow desc, 
                     YEAR(lastPriceTime) desc, 
                     MONTH(lastPriceTime) desc, 
                     WEEK(lastPriceTime,1) desc, 
                     (pricesCount>50) desc, 
                     lastPriceTime desc
            LIMIT 100
        ", ['serverGroup1' => $serverGroup,
            'account_id'   => $account_id,
            'serverGroup2' => $serverGroup,
            'serverGroup3' => $serverGroup]
        );
        if(!$qwe || !$qwe->rowCount()){
            return [];
        }
        return $qwe->fetchAll(PDO::FETCH_CLASS,self::class);
    }

    public static function setFollow(int $follower, int $master, int $serverGroup): bool
    {
        $qwe = qwe("
            insert into uacc_follows 
                (follower, master, serverGroup) 
            VALUES 
                (:follower, :master, :serverGroup)",
            ['follower' => $follower, 'master' => $master, 'serverGroup'=> $serverGroup]
        );
        return boolval($qwe);
    }

    public static function unsetFollow(int $follower, int $master, int $serverGroup): bool
    {
        $qwe = qwe("
            delete from uacc_follows 
            where follower = :follower
                and master = :master
                and serverGroup = :serverGroup",
            ['follower' => $follower, 'master' => $master, 'serverGroup'=> $serverGroup]
        );
        return boolval($qwe);
    }

}