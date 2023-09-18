<?php

namespace App\Item;

use App\Craft\{AccountCraft, BufferSecond};
use App\DTO\PriceDTO;
use App\User\AccSettings;
use App\User\Member;
use Symphograph\Bicycle\Env\Env;
use Symphograph\Bicycle\Errors\AppErr;
use PDO;

class Price extends PriceDTO
{
    public int     $accountId   = 1;
    public int     $itemId      = 0;
    public int    $price         = 0;
    public int    $serverGroupId = 0;
    public string $updatedAt     = '';
    public string  $method      = 'empty';
    public ?string $label;
    public ?string $name;
    public ?string $author;
    public ?int    $grade;
    public ?string $icon;
    public ?bool   $craftable;
    public ?bool   $buyOnly;

    private const methods = [
        'bySolo', 'byAccount', 'byToNPC', 'byFriends', 'byWellKnown', 'byAny'
    ];




    public function __set(string $name, $value): void{}

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
        $Price = new Price();
        foreach (get_defined_vars() as $k => $var){
            if($var === null)
                continue;
            $Price->$k = $var;
        }
        return $Price;
    }

    public static function bySaved(int $itemId): self|bool
    {
        $AccSets = AccSettings::byGlobal();
        return match ($AccSets->mode) {
            1 => self::byMode1($itemId),
            2 => self::byMode2($itemId),
            3 => self::byMode3($itemId),
            default => false
        };
    }

    private static function byMode1(int $itemId): self|bool
    {
        if(in_array($itemId,Item::privateItems())){
            if($Price = self::bySolo($itemId)){
                return $Price;
            }
        }

        if($Price = self::byFriends($itemId)){
            return $Price;
        }

        if($Price = self::byWellKnown($itemId)){
            return $Price;
        }

        if($Price = self::byAny($itemId)){
            return $Price;
        }
        return false;
    }

    private static function byMode2(int $itemId): self|bool
    {
        if(in_array($itemId,Item::privateItems())){
            if($Price = self::bySolo($itemId)){
                return $Price;
            }
        }
        return self::byFriends($itemId);
    }

    private static function byMode3(int $itemId): self|bool
    {
        return self::bySolo($itemId);
    }

    private static function byAny(int $itemId)
    {
        $AccSets = AccSettings::byGlobal();
        if($AccSets->serverGroupId === 100){
            return self::byAnyServer($itemId);
        }
        $qwe = qwe("select * from uacc_prices
            where itemId = :itemId
            and serverGroupId = :serverGroupId
            order by updatedAt desc 
            limit 1",
        ['itemId'=>$itemId, 'serverGroupId'=> $AccSets->serverGroupId]
        );
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        $Price = $qwe->fetchObject(self::class);
        $Price->method = 'byAny';
        return $Price;
    }

    private static function byAnyServer(int $itemId)
    {
        $qwe = qwe("select * from uacc_prices
            where itemId = :itemId
            order by updatedAt desc 
            limit 1",
            ['itemId'=>$itemId]
        );
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        $Price = $qwe->fetchObject(self::class);
        $Price->method = 'byAnyServer';
        return $Price;
    }

    private static function bySolo(int $itemId): self|bool
    {
        $AccSets = AccSettings::byGlobal();
        if($Price = self::byAccount($itemId, $AccSets->accountId, $AccSets->serverGroupId)){
            $Price->method = 'bySolo';
            return $Price;
        }
        //return false;
        return self::byToNPC($itemId);
    }

    private static function byFriends(int $itemId) : self|bool
    {
        $AccSets = AccSettings::byGlobal();
        $Member = Member::byId($AccSets->accountId, $AccSets->serverGroupId);

        $members = $Member->getFollowMasters();
        if(empty($members)){
            return self::bySolo($itemId);
        }
        $members[] = $AccSets->accountId;

        $Price = self::byMemberList($itemId, $AccSets->serverGroupId, $members);
        if(!$Price){
            return false;
        }
        $Price->method = 'byFriends';

        if($Price->accountId === $AccSets->accountId){
            $Price->method = 'bySolo';
        }
        return $Price;
    }

    private static function byWellKnown(int $itemId)
    {
        $AccSets = AccSettings::byGlobal();

        $Member = Member::byId(Env::getAdminAccountId(), $AccSets->serverGroupId);
        $members = $Member->getFollowMasters();
        $members[] = $Member->accountId;
        $Price = self::byMemberList($itemId, $AccSets->serverGroupId, $members);
        if(!$Price){
            return false;
        }
        $Price->method = 'byWellKnown';
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
            ['itemId'=>$itemId, 'serverGroupId'=>$serverGroupId]
        );
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        return $qwe->fetchObject(self::class);
    }

    private static function byAccount(int $itemId, int $accountId, int $serverGroup): self|bool
    {
        $qwe = qwe("
            select * from uacc_prices 
            where accountId = :accountId 
                and itemId = :itemId 
                and serverGroupId = :serverGroupId",
            ['accountId'  => $accountId,
             'itemId'     => $itemId,
             'serverGroupId' => $serverGroup]
        );
        if (!$qwe || !$qwe->rowCount()){
            return false;
        }
        $Price = $qwe->fetchObject(self::class);
        $Price->method = 'byAccount';
        return $Price;
    }

    private static function byToNPC(int $itemId): self|bool
    {

        if(!in_array($itemId,Item::privateItems())){
            return false;
        }

        if(self::isCurrency($itemId)){
            return false;
        }


        //Можно ли продать NPC?
        if($toNPC = self::toNPC($itemId)){
            $Price = new self();
            $Price->accountId = 1;
            $Price->itemId = $itemId;
            $Price->price = $toNPC;
            $Price->method = 'byToNPC';
            return $Price;
        }
        return false;
    }

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
        if($serverGroupId){
            $sql = "select * from uacc_prices 
                    where accountId = :accountId
                    and serverGroupId = :serverGroupId
                    and itemId not in $privateItemsStr
                    order by updatedAt desc 
                    limit 1";
            $params = ['accountId'=> $accountId, 'serverGroupId'=> $serverGroupId];
        }else{
            $sql = "select * from uacc_prices 
                    where accountId = :accountId
                    order by updatedAt desc 
                    limit 1";
            $params = ['accountId'=> $accountId];
        }
        $qwe = qwe($sql, $params);

        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        $Price = $qwe->fetchObject(self::class);
        unset($Price->method, $Price->label);
        return $Price;
    }

    /**
     * @return array<self>
     */
    public static function memberPriceList(int $accountId, int $serverGroupId): array
    {
        $qwe = qwe("
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
               and serverGroupId = :serverGroupId",
            ['accountId' => $accountId, 'serverGroupId' => $serverGroupId]
        );
        if(!$qwe || !$qwe->rowCount()){
            return [];
        }
        return $qwe->fetchAll(PDO::FETCH_CLASS, self::class);
    }

    /**
     * @return array<self>
     */
    public static function basedList(): array
    {

        $qwe = qwe("select * from basedItems");
        $qwe = $qwe->fetchAll(PDO::FETCH_COLUMN)
        or throw new AppErr('basedList is empty', 'Предметы не найдены');
        $List = [];
        foreach ($qwe as $id){
            $price = self::bySaved($id);
            if(!$price){
                $price = new Price();
                $price->itemId = $id;
            }
            $price->initItemProps();
            $List[] = $price;
        }
        return $List;
    }

    private static function toNPC(int $itemId): int|bool
    {
        $qwe = qwe("
            select priceToNPC from items 
            where id = :itemId and priceToNPC",
            ['itemId'=>$itemId]
        );
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        return $qwe->fetchAll(PDO::FETCH_COLUMN)[0];
    }

    public static function byCraft(int $itemId): self|false
    {
        $AccSets = AccSettings::byGlobal();
        $CraftData = AccountCraft::byResultItemId($itemId);
        if(!$CraftData) return false;

        $Price = new self();
        $Price->itemId = $itemId;
        $Price->price = $CraftData->craftCost;
        $Price->accountId = $AccSets->accountId;
        $Price->method = 'byCraft';
        return $Price;
    }

    public static function byBuffer(int $itemId): self|false
    {
        $AccSets = AccSettings::byGlobal();
        $bufferData = BufferSecond::byItemId($itemId);
        if(!$bufferData){
            return false;
        }
        $Price = new self();
        $Price->itemId = $bufferData->resultItemId;
        $Price->price = $bufferData->craftCost;
        $Price->accountId = $AccSets->accountId;
        $Price->method = 'byBuffer';
        return $Price;
    }

    public function initItemProps(): void
    {
        if($this->method === 'empty'){
            //return;
        }
        $item = Item::byId($this->itemId);
        $this->name = $item->name;
        $this->grade = $item->basicGrade;
        $this->icon = $item->icon;
        $AuthorAccSets = AccSettings::byId($this->accountId)
            or throw new AppErr("Author $this->accountId is missed", 'Автор цены не найден');
        $this->author = $AuthorAccSets->publicNick;

        self::initLabel();
    }

    public function initLabel(): void
    {
        $this->label = PriceEnum::label($this);
    }

    private static function isCurrency(int $itemId): bool
    {
        $qwe = qwe("select * from currency where id = :itemId", ['itemId'=>$itemId]);
        return ($qwe && $qwe->rowCount());
    }

    public function putToDB(): void
    {
        if(empty($this->updatedAt)){
            $this->updatedAt = date('Y-m-d H:i:s');
        }
        $ObjectDTO = parent::byBind($this);
        $ObjectDTO->putToDB();
    }

    /**
     * @param int[] $lost
     * @return self[]
     */
    public static function lostList(array $lost): array
    {
        $items = Item::searchList($lost);
        $Prices = [];
        foreach ($items as $item){
            $Prices[] = self::byParams(
                itemId: $item->id,
                name: $item->name,
                grade: $item->basicGrade,
                icon: $item->icon
            );
        }
        return $Prices;
    }

    public function initAuthor(): void
    {
        $AuthorAccSets = AccSettings::byId($this->accountId);
        $this->author = $AuthorAccSets->publicNick;
    }

    public static function delFromDB(int $accountId, int $itemId, int $serverGroupId): void
    {
        parent::del($accountId, $itemId, $serverGroupId);
    }

}