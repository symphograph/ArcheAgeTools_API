<?php

namespace App\User;


use Symphograph\Bicycle\DB;
use Symphograph\Bicycle\Errors\AppErr;
use App\Item\{Item, Price};
use PDO;

class Member
{
    public int $accountId;
    public int $serverGroup;
    public ?bool $isFollow;
    public ?int $pricesCount;
    public ?int $followersCount;
    public ?string $publicNick;
    public ?string $avaFileName;
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
        $privateItemsStr = '(' . implode(',', Item::privateItems()) . ')';
        //$serverGroup = 100;
        if($serverGroup === 100){
            throw new AppErr('Server not defined', 'Сервер не выбран');
        }
        $qwe = qwe("
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
                       serverGroup,
                       COUNT(*) as pricesCount, 
                       max(updatedAt) as lastPriceTime
                from uacc_prices
                where serverGroup = :serverGroup
                and itemId not in $privateItemsStr
                group by accountId, serverGroup
                order by lastPriceTime desc
            ) as tmp
            inner join uacc_settings sets 
                on sets.accountId = tmp.accountId
                and sets.authType != 'default'
            left join uacc_follows uf
                on uf.master = sets.accountId
                and uf.follower = :accountId
                and uf.serverGroup = tmp.serverGroup
            left join
            (
                select count(*) as flws, 
                       serverGroup,
                       max(uf.follower) as follower, 
                       max(uf.master) as master
                from uacc_follows uf
                /*where serverGroup = tmp.serverGroup*/
                group by uf.master, serverGroup
            ) as flwt
            ON sets.accountId = flwt.master
            and tmp.serverGroup  = flwt.serverGroup
            order by isFollow desc, 
                     YEAR(lastPriceTime) desc, 
                     MONTH(lastPriceTime) desc, 
                     WEEK(lastPriceTime,1) desc, 
                     (pricesCount>50) desc, 
                     lastPriceTime desc
            LIMIT 100
        ", ['serverGroup' => $serverGroup,
            'accountId'    => $accountId,
            /*'serverGroup3' => $serverGroup*/]
        );

        $list = $qwe->fetchAll(PDO::FETCH_CLASS,self::class)
            or throw new AppErr('memberList is empty', 'Нет данных');;
        return $list;
    }

    public static function setFollow(int $follower, int $master, int $serverGroup): void
    {
        $params = [
            'follower'    => $follower,
            'master'      => $master,
            'serverGroup' => $serverGroup
        ];
        DB::replace('uacc_follows', $params);

    }

    public static function unsetFollow(int $follower, int $master, int $serverGroup): void
    {
        qwe("
            delete from uacc_follows 
            where follower = :follower
                and master = :master
                and serverGroup = :serverGroup",
            ['follower' => $follower, 'master' => $master, 'serverGroup'=> $serverGroup]
        ) or throw new AppErr('unsetFollow err', 'Ошибка при сохранении');
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

    public function initIsFollow(): void
    {
        $AccSets = AccSettings::byGlobal();
        $qwe = qwe("
            select * from uacc_follows 
            where follower = :follower
                and master = :master
                and serverGroup = :serverGroup",
            [
                'follower'    => $AccSets->accountId,
                'master'      => $this->accountId,
                'serverGroup' => $AccSets->serverGroup
            ]
        );
        if(!$qwe || !$qwe->rowCount()){
            $this->isFollow = false;
            return;
        }
        $this->isFollow = true;
    }

    public function initAccData(): void
    {
        $AccSets = AccSettings::byId($this->accountId);
        $this->avaFileName = $AccSets->avaFileName;
        $this->publicNick = $AccSets->publicNick;
    }

}