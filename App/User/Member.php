<?php

namespace App\User;


use App\Item\{Item};
use App\Price\Price;
use PDO;
use Symphograph\Bicycle\Errors\AppErr;
use Symphograph\Bicycle\PDO\DB;

class Member
{
    public int     $accountId;
    public int     $serverGroupId;
    public ?bool   $isFollow;
    public ?int    $pricesCount;
    public ?int    $followersCount;
    public ?string $publicNick;
    public ?string $avaFileName;
    public ?int    $oldId;
    public ?Item  $LastPricedItem;
    /**
     * @var array<int>
     */
    private array $followMasters = [];

    /**
     * @return array<self>
     */
    public static function getList(int $accountId, int $serverGroupId): array
    {
        $privateItemsStr = DB::implodeIntIn(Item::privateItems());
        //$serverGroupId = 100;
        if ($serverGroupId === 100) {
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
                       serverGroupId,
                       COUNT(*) as pricesCount, 
                       max(updatedAt) as lastPriceTime
                from uacc_prices
                where serverGroupId = :serverGroupId
                and itemId not in $privateItemsStr
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
        ", ['serverGroupId' => $serverGroupId,
            'accountId'     => $accountId,
            /*'serverGroup3' => $serverGroup*/]
        );

        $list = $qwe->fetchAll(PDO::FETCH_CLASS, self::class)
        or throw new AppErr('memberList is empty', 'Нет данных');
        return $list;
    }

    public static function setFollow(int $follower, int $master, int $serverGroupId): void
    {
        $params = [
            'follower'      => $follower,
            'master'        => $master,
            'serverGroupId' => $serverGroupId
        ];
        DB::replace('uacc_follows', $params);

    }

    public static function unsetFollow(int $follower, int $master, int $serverGroupId): void
    {
        qwe("
            delete from uacc_follows 
            where follower = :follower
                and master = :master
                and serverGroupId = :serverGroupId",
            ['follower' => $follower, 'master' => $master, 'serverGroupId' => $serverGroupId]
        ) or throw new AppErr('unsetFollow err', 'Ошибка при сохранении');
    }

    public function __set(string $name, $value): void
    {
    }

    /**
     * @return array<int>
     */
    public function getFollowMasters(): array
    {
        return $this->followMasters;
    }

    public function initLastPricedItem(int $serverGroupId): bool
    {
        $Price = Price::getLastMemberPrice($this->accountId, $serverGroupId);
        if (!$Price) {
            return false;
        }
        if (!$Item = Item::byId($Price->itemId)) {
            return false;
        }
        $Item->initData();
        $Item->Price = $Price;
        $this->LastPricedItem = $Item;
        return true;
    }

    public static function byId(int $accountId, int $serverGroup): self
    {
        $member = new self();
        $member->accountId = $accountId;
        $member->initFollowMasters($serverGroup);
        return $member;
    }

    private function initFollowMasters(int $serverGroup): void
    {
        $qwe = qwe("
            select master from uacc_follows 
              where follower = :follower and serverGroupId = :serverGroupId",
            ['follower' => $this->accountId, 'serverGroupId' => $serverGroup]
        );
        if (!$qwe || !$qwe->rowCount()) {
            return;
        }
        $this->followMasters = $qwe->fetchAll(PDO::FETCH_COLUMN);
    }

    public function initIsFollow(): void
    {
        $sql = "
            select * from uacc_follows 
            where follower = :follower
                and master = :master
                and serverGroupId = :serverGroupId";

        $params = [
            'follower'      => AccSets::curId(),
            'master'        => $this->accountId,
            'serverGroupId' => AccSets::curServerGroupId()];

        $qwe = qwe($sql, $params);

        if (!$qwe || !$qwe->rowCount()) {
            $this->isFollow = false;
            return;
        }
        $this->isFollow = true;
    }

    public function initAccData(): void
    {
        $AccSets = AccSets::byId($this->accountId);
        $this->avaFileName = $AccSets->avaFileName;
        $this->publicNick = $AccSets->publicNick;
        $this->oldId = $AccSets->old_id ?? null;
    }

}