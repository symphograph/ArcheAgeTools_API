<?php

namespace App\User\Member;


use App\Item\{Item};
use App\Price\Price;
use App\User\AccSets;
use PDO;
use Symphograph\Bicycle\Errors\AppErr;
use Symphograph\Bicycle\Logs\Log;
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
     * @var int[]
     */
    private array $followMasters = [];


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

    public function initLastPricedItem(int $serverGroupId): static
    {
        $Price = Price::getLastMemberPrice($this->accountId, $serverGroupId);
        if (!$Price) {
            return $this;
        }

        $Item = Item::byId($Price->itemId)->initData();

        $Item->Price = $Price;
        $this->LastPricedItem = $Item;

        return $this;
    }

    public static function newInstance(int $accountId): Member
    {
        $member = new self();
        $member->accountId = $accountId;
        return $member;
    }

    public static function byId(int $accountId, int $serverGroup): self
    {
        $member = new self();
        $member->accountId = $accountId;
        $member->initFollowMasters($serverGroup);
        return $member;
    }

    public function initFollowMasters(int $serverGroup): static
    {
        $sql = "
            select master 
            from uacc_follows 
            where follower = :follower 
                and serverGroupId = :serverGroupId";

        $params = ['follower' => $this->accountId, 'serverGroupId' => $serverGroup];

        $this->followMasters= qwe($sql,$params)->fetchAll(PDO::FETCH_COLUMN) ?? [];
        return $this;
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