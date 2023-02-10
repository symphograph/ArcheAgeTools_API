<?php

namespace App\User;

use App\Item\{Item, Price};
use PDO;

class Member
{
    public int $accountId;
    public ?bool $isFollow;
    public ?int $pricesCount;
    public ?int $followersCount;
    public ?string $publicNick;
    public ?Avatar $Avatar;
    public ?int $oldId;


    /**
     * @var array<int>
     */
    private array $followMasters = [];
    public ?Item $LastPricedItem;

    public function __set(string $name, $value): void
    {
    }

    public static function byId(int $accountId, int $serverGroup)
    {
        $member = new self();
        $member->accountId = $accountId;
        $member->initFollowMasters($serverGroup);
        return $member;
    }

    /**
     * @return array<int>
     */
    public function getFollowMasters(): array
    {
        return $this->followMasters;
    }

    private function initFollowMasters(int $serverGroup): void
    {
        $qwe = qwe("
            select master from uacc_follows 
              where follower = :follower and serverGroup = :serverGroup",
            ['follower' => $this->accountId, 'serverGroup' => $serverGroup]
        );
        if(!$qwe || !$qwe->rowCount()){
            return;
        }
        $this->followMasters = $qwe->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * @return array<self>
     */
    public static function getList(int $accountId, int $serverGroup): array
    {
        //$privateItemsStr = implode(',', Item::privateItems());
        $qwe = qwe("
            select
            uAcc.id as accountId,
            pricesCount,
            lastPriceTime,
            if(uf.master > 0,1,0) as isFollow,
            if(flwt.flws, flwt.flws, 0) as followersCount
            from
            (
                select accountId, 
                       COUNT(*) as pricesCount, 
                       max(datetime) as lastPriceTime
                from uacc_prices
                where serverGroup = :serverGroup1
                group by accountId
                order by lastPriceTime desc
            ) as tmp
            inner join user_accounts uAcc 
                on uAcc.id = tmp.accountId
                and uAcc.authTypeId > 1
            left join uacc_follows uf
                on uf.master = uAcc.id
                and uf.follower = :accountId
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
            'accountId'   => $accountId,
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

    public function initLastPricedItem(int $serverGroup): bool
    {
        $Price = Price::getLastMemberPrice($this->accountId, $serverGroup);
        if(!$Price){
            return false;
        }
        if(!$Item = Item::byId($Price->itemId)){
            return false;
        }

        $Item->Price = $Price;
        $this->LastPricedItem = $Item;
        return true;
    }

    public function initAccData(): void
    {
        $memberAccount = Account::byId($this->accountId);
        $memberAccount->initAvatar();
        $this->Avatar = $memberAccount->Avatar;
        $this->publicNick = $memberAccount->AccSets->publicNick;
        if(!empty($memberAccount->AccSets->old_id)){
            $this->oldId = $memberAccount->AccSets->old_id;
        }
    }

    public function initIsFollow(): void
    {
        $Account = Account::getSelf();;
        $qwe = qwe("
            select * from uacc_follows 
            where follower = :follower
                and master = :master
                and serverGroup = :serverGroup",
            [
                'follower'    => $Account->id,
                'master'      => $this->accountId,
                'serverGroup' => $Account->AccSets->serverGroup
            ]
        );
        if(!$qwe || !$qwe->rowCount()){
            $this->isFollow = false;
            return;
        }
        $this->isFollow = true;
    }

}