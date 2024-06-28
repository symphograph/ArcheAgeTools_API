<?php

namespace App\Price;

use App\Craft\{AccountCraft, BufferSecond};
use App\Item\Item;
use App\User\AccSets;
use App\User\Member;
use PDO;
use Symphograph\Bicycle\DTO\ModelTrait;
use Symphograph\Bicycle\Env\Env;
use Symphograph\Bicycle\Errors\AppErr;
use Symphograph\Bicycle\PDO\DB;

class Price extends PriceDTO
{
    use ModelTrait;

    private const array methods = [
        'bySolo', 'byAccount', 'byToNPC', 'byFriends', 'byWellKnown', 'byAny'
    ];
    public int     $accountId     = 1;
    public int     $itemId        = 0;
    public int     $price         = 0;
    public int     $serverGroupId = 0;
    public string  $updatedAt     = '';
    public string  $method        = 'empty';
    public Method $Method;
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
        $privateItemsStr = '(' . implode(',', Item::privateItems()) . ')';
        if ($serverGroupId) {
            $sql = "select * from uacc_prices 
                    where accountId = :accountId
                    and serverGroupId = :serverGroupId
                    and itemId not in $privateItemsStr
                    order by updatedAt desc 
                    limit 1";
            $params = ['accountId' => $accountId, 'serverGroupId' => $serverGroupId];
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
     * @throws AppErr
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

    public static function bySaved(int $itemId): self|bool
    {
        return match (AccSets::curMode()) {
            1 => self::byMode1($itemId),
            2 => self::byMode2($itemId),
            3 => self::byMode3($itemId),
            default => false
        };
    }

    private static function byMode1(int $itemId): self|bool
    {
        if (in_array($itemId, Item::privateItems())) {
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

    private static function bySolo(int $itemId): self|bool
    {
        if ($Price = self::byAccount($itemId, AccSets::curId(), AccSets::curServerGroupId())) {
            $Price->setMethod(Method::bySolo);
            return $Price;
        }

        return self::byToNPC($itemId);
    }

    private static function byAccount(int $itemId, int $accountId, int $serverGroup): self|false
    {
        $sql = "
            select * from uacc_prices 
            where accountId = :accountId 
                and itemId = :itemId 
                and serverGroupId = :serverGroupId";

        $params = ['accountId'     => $accountId,
                   'itemId'        => $itemId,
                   'serverGroupId' => $serverGroup];

        $Price = DB::qwe($sql,$params)->fetchObject(self::class);
        if (!$Price) return false;
        $Price->setMethod(Method::byAccount);
        return $Price;
    }

    private static function byToNPC(int $itemId): self|bool
    {
        if (!in_array($itemId, Item::privateItems())) {
            return false;
        }

        if (self::isCurrency($itemId)) {
            return false;
        }

        //Можно ли продать NPC?
        $price = self::toNPC($itemId);
        if(!$price) return false;

        $Price = new self();
        $Price->accountId = 1;
        $Price->itemId = $itemId;
        $Price->price = $price;
        $Price->setMethod(Method::byToNPC);
        return $Price;
    }

    private static function isCurrency(int $itemId): bool
    {
        $qwe = qwe("select * from currency where id = :itemId", ['itemId' => $itemId]);
        return ($qwe && $qwe->rowCount());
    }

    private static function toNPC(int $itemId): int|false
    {
        $sql = "
            select priceToNPC from items 
            where id = :itemId and priceToNPC";
        $params = compact('itemId');
        return qwe($sql, $params)
                   ->fetchAll(PDO::FETCH_COLUMN)[0] ?? false;
    }

    private static function byFriends(int $itemId): self|bool
    {
        $accountId = AccSets::curId();
        $serverGroupId = AccSets::curServerGroupId();
        $Member = Member::byId($accountId, $serverGroupId);

        $members = $Member->getFollowMasters();
        if (empty($members)) {
            return self::bySolo($itemId);
        }
        $members[] = $accountId;

        $Price = self::byMemberList($itemId, $serverGroupId, $members);
        if (!$Price) {
            return false;
        }
        $Price->setMethod(Method::byFriends);

        if ($Price->accountId === $accountId) {
            $Price->setMethod(Method::bySolo);
        }
        return $Price;
    }

    private static function byMemberList(int $itemId, int $serverGroupId, array $members)
    {
        $stringMembers = implode(',', $members);
        $qwe = qwe("
            select * 
            from uacc_prices
            where uacc_prices.accountId in ( $stringMembers )
            and itemId = :itemId
            and serverGroupId = :serverGroupId
            order by updatedAt desc 
            limit 1",
            ['itemId' => $itemId, 'serverGroupId' => $serverGroupId]
        );
        if (!$qwe || !$qwe->rowCount()) {
            return false;
        }
        return $qwe->fetchObject(self::class);
    }

    private static function byWellKnown(int $itemId)
    {
        $serverGroupId = AccSets::curServerGroupId();
        $Member = Member::byId(Env::getAdminAccountId(), $serverGroupId);
        $members = $Member->getFollowMasters();
        $members[] = $Member->accountId;
        $Price = self::byMemberList($itemId, $serverGroupId, $members);
        if (!$Price) {
            return false;
        }
        $Price->setMethod(Method::byWellKnown);
        return $Price;
    }

    private static function byAny(int $itemId): self|false
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
        if (!$Price) return false;

        $Price->setMethod(Method::byAny);
        return $Price;
    }

    private static function byAnyServer(int $itemId)
    {
        $qwe = qwe("select * from uacc_prices
            where itemId = :itemId
            order by updatedAt desc 
            limit 1",
            ['itemId' => $itemId]
        );
        if (!$qwe || !$qwe->rowCount()) {
            return false;
        }
        $Price = $qwe->fetchObject(self::class);
        $Price->setMethod(Method::byAnyServer);
        return $Price;
    }

    private static function byMode2(int $itemId): self|bool
    {
        if (in_array($itemId, Item::privateItems())) {
            if ($Price = self::bySolo($itemId)) {
                return $Price;
            }
        }
        return self::byFriends($itemId);
    }

    private static function byMode3(int $itemId): self|bool
    {
        return self::bySolo($itemId);
    }

    public function initItemProps(): void
    {
        $item = Item::byId($this->itemId)->initData();
        $this->name = $item->name;
        $this->grade = $item->basicGrade;
        $this->icon = $item->icon;
        $AuthorAccSets = AccSets::byId($this->accountId)
        or throw new AppErr("Author $this->accountId is missed", 'Автор цены не найден');
        $this->author = $AuthorAccSets->publicNick;

        //self::initLabel();
    }

    public function initLabel(): void
    {
        //$this->label = PriceEnum::label($this);
        $this->label = $this->Method->label($this);
    }

    public static function byCraft(int $itemId): self|false
    {
        $CraftData = AccountCraft::byResultItemId($itemId);
        if (!$CraftData) return false;

        $Price = new self();
        $Price->itemId = $itemId;
        $Price->price = $CraftData->craftCost;
        $Price->accountId = AccSets::curId();
        $Price->setMethod(Method::byCraft);
        return $Price;
    }

    public static function byBuffer(int $itemId): self|false
    {
        $bufferData = BufferSecond::byItemId($itemId);
        if (!$bufferData) {
            return false;
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
        $items = Item::searchList($lost);
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

    public function initAuthor(): void
    {
        $AuthorAccSets = AccSets::byId($this->accountId);
        $this->author = $AuthorAccSets->publicNick;
    }

    public function setMethod(Method $Method): void
    {
        $this->Method = $Method;
        $this->method = $Method->value;
    }
}