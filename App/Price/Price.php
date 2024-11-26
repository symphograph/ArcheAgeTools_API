<?php

namespace App\Price;

use App\User\Member\Repo\MemberRepo;
use App\Craft\{BufferSecond, UCraft\Repo\UCraftRepo};
use App\Currency\Repo\CurrencyRepo;
use App\Item\ItemList;
use App\Item\Repo\ItemRepo;
use App\Price\Repo\PriceRepo;
use App\User\AccSets;
use PDO;
use Symphograph\Bicycle\DTO\ModelTrait;
use Symphograph\Bicycle\Env\Env;
use Symphograph\Bicycle\Errors\AppErr;
use Symphograph\Bicycle\PDO\DB;

class Price extends PriceDTO
{
    use ModelTrait;

    public int     $accountId     = 1;
    public int     $itemId        = 0;
    public int     $price         = 0;
    public int     $serverGroupId = 0;
    public string  $updatedAt     = '';
    public string  $method        = 'empty';
    public Method  $Method;
    public ?string $label;
    public ?string $name;
    public ?string $author;
    public ?int    $grade;
    public ?string $icon;
    public ?bool   $craftable;
    public ?bool   $buyOnly;

    public static function byInput(int $accountId, int $itemId, int $serverGroupId, int $price): self|bool
    {
        $Price = new self();
        $Price->accountId = $accountId;
        $Price->itemId = $itemId;
        $Price->serverGroupId = $serverGroupId;
        $Price->price = $price;
        return $Price;
    }

    public static function getLastMemberPrice(int $accountId, int $serverGroupId = 0): self|false
    {
        $privateIds = ItemRepo::getPrivateIds();
        if ($serverGroupId) {
            $sql = "select * from uacc_prices 
                    where accountId = :accountId
                    and serverGroupId = :serverGroupId
                    and itemId not in (:privateIds)
                    order by updatedAt desc 
                    limit 1";
            $params = ['accountId' => $accountId, 'serverGroupId' => $serverGroupId, 'privateIds' => $privateIds];
        } else {
            $sql = "select * from uacc_prices 
                    where accountId = :accountId
                    order by updatedAt desc 
                    limit 1";
            $params = ['accountId' => $accountId];
        }
        $qwe = qwe($sql, $params);

        if (!$qwe || !$qwe->rowCount()) {
            return false;
        }
        $Price = $qwe->fetchObject(self::class);
        unset($Price->method, $Price->label);
        return $Price;
    }

    /**
     * @return self[]
     */
    public static function memberPriceList(int $accountId, int $serverGroupId): array
    {
        $sql = "
            select up.*, 
                   items.name,
                   items.craftable,
                   if(items.basicGrade,items.basicGrade,1) as grade,
                   items.icon,
                   if(ubO.itemId, 1, 0) as buyOnly
            from uacc_prices up
             inner join items on up.itemId = items.id 
                and items.onOff
            left join uacc_buyOnly ubO on items.id = ubO.itemId
            and ubO.accountId = up.accountId
             where up.accountId = :accountId 
               and serverGroupId = :serverGroupId";
        $params = compact('accountId', 'serverGroupId');
        return qwe($sql, $params)
            ->fetchAll(PDO::FETCH_CLASS, self::class);
    }

    /**
     * @return self[]
     */
    public static function basedList(): array
    {

        $qwe = qwe("select * from basedItems");
        $qwe = $qwe->fetchAll(PDO::FETCH_COLUMN)
        or throw new AppErr('basedList is empty', 'Предметы не найдены');
        $List = [];
        foreach ($qwe as $id) {
            $price = self::bySaved($id);
            if (!$price) {
                $price = new Price();
                $price->itemId = $id;
            }
            $price->initItemProps();
            $List[] = $price;
        }
        return $List;
    }

    public static function bySaved(int $itemId): self|false
    {
        return match (AccSets::curMode()) {
            1 => self::byMode1($itemId),
            2 => self::byMode2($itemId),
            3 => self::byMode3($itemId),
            default => false
        };
    }

    private static function byMode1(int $itemId): self|false
    {
        if (in_array($itemId, ItemRepo::getPrivateIds())) {
            if ($Price = self::bySolo($itemId)) {
                return $Price;
            }
        }

        if ($Price = self::byFriends($itemId)) {
            return $Price;
        }

        if ($Price = self::byWellKnown($itemId)) {
            return $Price;
        }

        if ($Price = self::byAny($itemId)) {
            return $Price;
        }
        return false;
    }

    private static function bySolo(int $itemId): ?self
    {
        if ($Price = PriceRepo::byAccount($itemId, AccSets::curId(), AccSets::curServerGroupId())) {
            $Price->setMethod(Method::bySolo);
            return $Price;
        }

        return self::byToNPC($itemId);
    }

    private static function byToNPC(int $itemId): ?self
    {
        if (!in_array($itemId, ItemRepo::getPrivateIds())) {
            return null;
        }

        if (self::isCurrency($itemId)) {
            return null;
        }

        //Можно ли продать NPC?
        $price = self::toNPC($itemId);
        if(!$price) return null;

        $Price = new self();
        $Price->accountId = 1;
        $Price->itemId = $itemId;
        $Price->price = $price;
        $Price->setMethod(Method::byToNPC);
        return $Price;
    }

    private static function isCurrency(int $itemId): bool
    {
        $ids = CurrencyRepo::getIds();
        return in_array($itemId, $ids);
    }

    private static function toNPC(int $itemId): ?int
    {
        return ItemRepo::byId($itemId)->priceToNPC ?? null;
    }

    private static function byFriends(int $itemId): ?self
    {


        $accountId = AccSets::curId();
        $serverGroupId = AccSets::curServerGroupId();
        $member = MemberRepo::get($accountId, $serverGroupId);

        $masters = $member->getFollowMasters();
        if (empty($masters)) {
            return self::bySolo($itemId);
        }
        $masters[] = $accountId;

        $Price = self::byMemberList($itemId, $serverGroupId, $masters);
        if (!$Price) {
            return null;
        }
        $Price->setMethod(Method::byFriends);

        if ($Price->accountId === $accountId) {
            $Price->setMethod(Method::bySolo);
        }
        return $Price;
    }

    private static function byMemberList(int $itemId, int $serverGroupId, array $members): ?self
    {
        $sql = "
            select * 
            from uacc_prices
            where uacc_prices.accountId in (:members)
            and itemId = :itemId
            and serverGroupId = :serverGroupId
            order by updatedAt desc 
            limit 1";
        $params = ['members' => $members, 'itemId' => $itemId, 'serverGroupId' => $serverGroupId];
        return DB::qwe($sql, $params)->fetchObject(self::class) ?: null;
    }

    private static function byWellKnown(int $itemId): ?Price
    {
        $serverGroupId = AccSets::curServerGroupId();
        $member = MemberRepo::get(Env::getAdminAccountId(), $serverGroupId);
        $members = $member->getFollowMasters();
        $members[] = $member->accountId;

        $Price = self::byMemberList($itemId, $serverGroupId, $members);
        if (!$Price) {
            return null;
        }
        $Price->setMethod(Method::byWellKnown);
        return $Price;
    }

    private static function byAny(int $itemId): ?self
    {
        $serverGroupId = AccSets::curServerGroupId();
        if ($serverGroupId === 100) {
            return self::byAnyServer($itemId);
        }

        $sql = "select * from uacc_prices
            where itemId = :itemId
            and serverGroupId = :serverGroupId
            order by updatedAt desc 
            limit 1";
        $params = compact('itemId', 'serverGroupId');

        $Price = qwe($sql, $params)
            ->fetchObject(self::class);
        if (!$Price) return null;

        $Price->setMethod(Method::byAny);
        return $Price;
    }

    private static function byAnyServer(int $itemId): ?self
    {
        $qwe = DB::qwe("select * from uacc_prices
            where itemId = :itemId
            order by updatedAt desc 
            limit 1",
            ['itemId' => $itemId]
        );
        if (!$qwe || !$qwe->rowCount()) {
            return null;
        }
        $Price = $qwe->fetchObject(self::class);
        $Price->setMethod(Method::byAnyServer);
        return $Price;
    }

    private static function byMode2(int $itemId): ?self
    {
        if (in_array($itemId, ItemRepo::getPrivateIds())) {
            if ($Price = self::bySolo($itemId)) {
                return $Price;
            }
        }
        return self::byFriends($itemId);
    }

    private static function byMode3(int $itemId): ?self
    {
        return self::bySolo($itemId);
    }

    public function initItemProps(): static
    {
        $item = ItemRepo::byId($this->itemId);
        $this->name = $item->name;
        $this->grade = $item->basicGrade;
        $this->icon = $item->icon;

        $AuthorAccSets = AccSets::byId($this->accountId)
        or throw new AppErr("Author $this->accountId is missed", 'Автор цены не найден');
        $this->author = $AuthorAccSets->publicNick;
        return $this;
        //self::initLabel();
    }

    public function initLabel(): void
    {
        //$this->label = PriceEnum::label($this);
        $this->label = $this->Method->label($this);
    }

    public static function byCraft(int $itemId): ?self
    {
        $uCraft = UCraftRepo::getBest($itemId);
        if (!$uCraft) return null;

        $Price = new self();
        $Price->itemId = $itemId;
        $Price->price = $uCraft->craftCost;
        $Price->accountId = AccSets::$current->accountId;
        $Price->setMethod(Method::byCraft);
        return $Price;
    }

    public static function byBuffer(int $itemId): ?self
    {
        $bufferData = BufferSecond::byItemId($itemId);
        if (!$bufferData) {
            return null;
        }
        $Price = new self();
        $Price->itemId = $bufferData->resultItemId;
        $Price->price = $bufferData->craftCost;
        $Price->accountId = AccSets::curId();
        $Price->setMethod(Method::byBuffer);
        return $Price;
    }

    /**
     * @param int[] $lost
     * @return self[]
     */
    public static function lostList(array $lost): array
    {
        $items = ItemList::byIds($lost)->getList();
        $Prices = [];
        foreach ($items as $item) {
            $Prices[] = self::byParams(
                itemId: $item->id,
                name: $item->name,
                grade: $item->basicGrade,
                icon: $item->icon
            );
        }
        return $Prices;
    }

    public static function byParams(
        int     $itemId,
        int     $accountId = 1,
        int     $price = 0,
        int     $serverGroupId = 100,
        string  $updatedAt = '',
        string  $method = '',
        ?string $label = null,
        ?string $name = null,
        ?string $author = null,
        ?int    $grade = 1,
        ?string $icon = null,
        ?bool   $craftable = null,
        ?bool   $buyOnly = null

    ): self
    {
        $props = get_defined_vars();
        return Price::byBind($props);

    }

    public static function delFromDB(int $accountId, int $itemId, int $serverGroupId): void
    {
        parent::del($accountId, $itemId, $serverGroupId);
    }

    public function __set(string $name, $value): void
    {
    }

    private function beforePut(): void
    {
        if (empty($this->updatedAt)) {
            $this->updatedAt = date('Y-m-d H:i:s');
        }
    }

    public function initAuthor(): static
    {
        $AuthorAccSets = AccSets::byId($this->accountId);
        $this->author = $AuthorAccSets->publicNick;
        return $this;
    }

    public function setMethod(Method $Method): void
    {
        $this->Method = $Method;
        $this->method = $Method->value;
    }

    public static function createEmpty(int $itemId): static
    {
        $price = new Price();
        $price->itemId = $itemId;
        $price->author = 'Не найдено';
        return $price;
    }
}