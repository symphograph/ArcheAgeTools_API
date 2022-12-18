<?php

namespace Item;

use PDO;
use Symphograph\Bicycle\DB;

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
        'bySolo', 'byAccount', 'byToNPC'
    ];

    public function __set(string $name, $value): void{}

    public static function bySolo(int $itemId): self|bool
    {
        global $Account;
        if($Price = self::byAccount($itemId, $Account->id, $Account->AccSets->serverGroup)){
            $Price->method = 'bySolo';
            $Price->label = date('d.m.Y H:i', strtotime($Price->datetime))  . ' Ваша цена';
            return $Price;
        }
        //return false;
        return self::byToNPC($itemId);
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

    public static function byToNPC(int $itemId): self|bool
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