<?php

namespace Item;

use Craft\{AccountCraft, BufferSecond};
use PDO;
use Symphograph\Bicycle\DB;
use User\Account;

class Price
{
    public int     $accountId   = 1;
    public int     $itemId      = 0;
    public int     $price       = 0;
    public int     $serverGroup = 0;
    public string  $datetime    = '';
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
        int     $serverGroup = 2,
        string  $datetime = '',
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
        global $Account;
        return match ($Account->AccSets->mode) {
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
        global $Account;
        $qwe = qwe("select * from uacc_prices
            where itemId = :itemId
            and serverGroup = :serverGroup
            order by datetime desc 
            limit 1",
        ['itemId'=>$itemId, 'serverGroup'=> $Account->AccSets->serverGroup]
        );
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        $Price = $qwe->fetchObject(self::class);
        $Price->method = 'byAny';
        return $Price;
    }

    private static function bySolo(int $itemId): self|bool
    {
        global $Account;
        if($Price = self::byAccount($itemId, $Account->id, $Account->AccSets->serverGroup)){
            $Price->method = 'bySolo';
            //$Price->label = date('d.m.Y H:i', strtotime($Price->datetime))  . ' Ваша цена';
            return $Price;
        }
        //return false;
        return self::byToNPC($itemId);
    }

    private static function byFriends(int $itemId) : self|bool
    {
        global $Account;
        if(empty($Account->Member)){
            $Account->initMember();
        }
        $members = $Account->Member->getFollowMasters();
        if(empty($members)){
            return self::bySolo($itemId);
        }
        $members[] = $Account->id;

        $Price = self::byMemberList($itemId, $Account->AccSets->serverGroup, $members);
        if(!$Price){
            return false;
        }
        $Price->method = 'byFriends';
        //$Price->label = date('d.m.Y H:i', strtotime($Price->datetime))  . ' Цена друга';

        if($Price->accountId === $Account->id){
            $Price->method = 'bySolo';
            //$Price->label = date('d.m.Y H:i', strtotime($Price->datetime))  . ' Ваша цена';
        }
        return $Price;
    }

    private static function byWellKnown(int $itemId)
    {
        global $env, $Account;
        $serverGroup = $Account->AccSets->serverGroup;

        $adminAccount = Account::byId($env->adminAccountId);
        $adminAccount->AccSets->serverGroup = $serverGroup;
        $adminAccount->initMember();
        $members = $adminAccount->Member->getFollowMasters();
        $members[] = $adminAccount->id;
        $Price = self::byMemberList($itemId, $serverGroup, $members);
        if(!$Price){
            return false;
        }
        $Price->method = 'byWellKnown';
        return $Price;
    }

    private static function byMemberList(int $itemId, int $serverGroup, array $members)
    {
        $stringMembers = implode(',', $members);
        $qwe = qwe("
            select * 
            from uacc_prices
            where uacc_prices.accountId in ( $stringMembers )
            and itemId = :itemId
            and serverGroup = :serverGroup
            order by datetime desc 
            limit 1",
            ['itemId'=>$itemId, 'serverGroup'=>$serverGroup]
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
                and serverGroup = :serverGroup",
            ['accountId'  => $accountId,
             'itemId'     => $itemId,
             'serverGroup' => $serverGroup]
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
            $Price->price = $toNPC;
            $Price->method = 'byToNPC';
            return $Price;
        }
        return false;
    }

    public static function byInput(int $accountId, int $itemId, int $serverGroup, int $price): self|bool
    {
        $Price = new self();
        $Price->accountId = $accountId;
        $Price->itemId = $itemId;
        $Price->serverGroup = $serverGroup;
        $Price->price = $price;
        return $Price;
    }

    public static function getLastMemberPrice(int $accountId, int $serverGroup): self|false
    {
        $privateItems = Item::privateItems();
        $privateItemsStr = '(' . implode(',', $privateItems) . ')';
        $qwe = qwe("            
                    select * from uacc_prices 
                    where accountId = :accountId
                    and serverGroup = :serverGroup
                    and price > 0
                    and itemId NOT IN " . $privateItemsStr . "
                    order by datetime desc 
                    limit 1",
            ['accountId'=> $accountId, 'serverGroup'=> $serverGroup]
        );
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        $Price = $qwe->fetchObject(self::class);
        unset($Price->method, $Price->label);
        return $Price;
    }

    /**
     * @return array<self>|false
     */
    public static function memberPriceList(int $accountId, int $serverGroup): array|false
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
               and serverGroup = :serverGroup",
            ['accountId' => $accountId, 'serverGroup' => $serverGroup]
        );
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        return $qwe->fetchAll(PDO::FETCH_CLASS, self::class);
    }

    /**
     * @return array<self>
     */
    public static function basedList(): array
    {

        $qwe = qwe("select * from basedItems");
        $qwe = $qwe->fetchAll(PDO::FETCH_COLUMN);
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
            where id = :item_id and priceToNPC",
            ['itemId'=>$itemId]
        );
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        return $qwe->fetchAll(PDO::FETCH_COLUMN)[0];
    }

    public static function byCraft(int $itemId): self|false
    {
        global $Account;
        $qwe = qwe("
        select uc.*,
               if(ubC.craftId, 1, 0) as isUBest
        from uacc_crafts uc
                 left join uacc_bestCrafts ubC 
                     on uc.craftId = ubC.craftId
                     and uc.accountId = ubC.accountId
         where uc.itemId = :itemId 
           and uc.accountId = :accountId
           and serverGroup = :serverGroup
           order by isUBest desc, isBest desc, spmu, craftCost
            limit 1",
        ['itemId'=>$itemId, 'accountId'=>$Account->id, 'serverGroup' => $Account->AccSets->serverGroup]
        );
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        $CraftData = $qwe->fetchObject(AccountCraft::class);
        $Price = new self();
        $Price->itemId = $itemId;
        $Price->price = $CraftData->craftCost;
        $Price->accountId = $Account->id;
        $Price->method = 'byCraft';
        return $Price;
    }

    public static function byBuffer(int $itemId): self|false
    {
        global $Account;
        $bufferData = BufferSecond::byItemId($itemId);
        if(!$bufferData){
            return false;
        }
        $Price = new self();
        $Price->itemId = $bufferData->resultItemId;
        $Price->price = $bufferData->craftCost;
        $Price->accountId = $Account->id;
        $Price->method = 'byBuffer';
        return $Price;
    }

    public function initItemProps(): void
    {
        if($this->method === 'empty'){
            return;
        }
        $item = Item::byId($this->itemId);
        $this->name = $item->name;
        $this->grade = $item->grade;
        $this->icon = $item->icon;
        $Author = Account::byId($this->accountId);
        $this->author = $Author->AccSets->publicNick;

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

    /**
     * @return array<self>
     */
    public static function getOldList(int $oldUserId): array
    {
        $qwe = qwe("
            select  item_id, 
                    auc_price as price, 
                    server_group as serverGroup,
                    time as datetime
            from old_prices
            where user_id = :user_id
            ",['user_id'=> $oldUserId]
        );
        if(!$qwe || !$qwe->rowCount()){
            return [];
        }
        return $qwe->fetchAll(PDO::FETCH_CLASS,self::class);
    }

    public function putToDB(): bool
    {
        if(empty($this->datetime)){
            $this->datetime = date('Y-m-d H:i:s');
        }
        $params = [
            'accountId'  => $this->accountId,
            'serverGroup' => $this->serverGroup,
            'itemId'     => $this->itemId,
            'price'       => $this->price,
            'datetime'    => $this->datetime
        ];
        return DB::replace('uacc_prices', $params);
    }

    /**
     * @param array<int> $lost
     * @return array<self>
     */
    public static function lostList(array $lost): array
    {
        $items = Item::searchList($lost);
        $Prices = [];
        foreach ($items as $item){
            $Prices[] = self::byParams(
                itemId: $item->id,
                name: $item->name,
                grade: $item->grade,
                icon: $item->icon
            );
        }
        return $Prices;
    }

    public function initAuthor(): void
    {
        $Author = Account::byId($this->accountId);
        $this->author = $Author->AccSets->publicNick;
    }

}