<?php

namespace Item;

use PDO;
use Symphograph\Bicycle\DB;
use User\Account;

class Price
{
    public int    $accountId  = 1;
    public int    $itemId     = 0;
    public int    $price       = 0;
    public int    $serverGroup = 0;
    public string $datetime    = '';
    public string $method      = 'empty';
    public string $label = 'Цена не найдена';

    private const methods = [
        'bySolo', 'byAccount', 'byToNPC', 'byFriends', 'byWellKnown', 'byAny'
    ];


    public function __set(string $name, $value): void{}

    public static function getPrice(int $itemId, int $mode): self|bool
    {
        return match ($mode) {
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

    public static function bySolo(int $itemId): self|bool
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
            SELECT * 
            FROM uacc_prices
            WHERE uacc_prices.accountId in ( $stringMembers )
            AND itemId = :itemId
            AND serverGroup = :serverGroup
            ORDER BY datetime DESC 
            LIMIT 1",
            ['itemId'=>$itemId, 'serverGroup'=>$serverGroup]
        );
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        return $qwe->fetchObject(self::class);
    }

    public static function byAccount(int $itemId, int $accountId, int $serverGroup): self|bool
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
        $qwe = qwe("            select * from uacc_prices 
             where accountId = :accountId
             and serverGroup = :serverGroup
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

    public function initLabel(): void
    {
        $this->label = PriceEnum::label($this);
    }

    public static function isCurrency(int $itemId): bool
    {
        $qwe = qwe("select * from valutas where id = :itemId", ['item_id'=>$itemId]);
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
        $params = [
            'accountId'  => $this->accountId,
            'serverGroup' => $this->serverGroup,
            'itemId'     => $this->itemId,
            'price'       => $this->price,
            'datetime'    => $this->datetime ?? date('Y-m-d H:i:s')
        ];
        return DB::replace('uacc_prices', $params);
    }

}