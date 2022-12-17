<?php

namespace Item;

use PDO;
use Symphograph\Bicycle\DB;

class Price
{
    public int    $account_id  = 1;
    public int    $item_id     = 0;
    public int    $price       = 0;
    public int    $serverGroup = 0;
    public string $datetime    = '';
    public string $method      = 'empty';
    public string $label = 'Цена не найдена';

    private const methods = [
        'bySolo', 'byAccount', 'byToNPC'
    ];

    public function __set(string $name, $value): void{}

    public static function bySolo(int $item_id): self|bool
    {
        global $Account;
        if($Price = self::byAccount($item_id, $Account->id, $Account->AccSets->server_group)){
            $Price->method = 'bySolo';
            $Price->label = date('d.m.Y H:i', strtotime($Price->datetime))  . ' Ваша цена';
            return $Price;
        }
        //return false;
        return self::byToNPC($item_id);
    }

    public static function byAccount(int $item_id, int $account_id, int $serverGroup): self|bool
    {
        $qwe = qwe("
            select * from uacc_prices 
            where account_id = :account_id 
                and item_id = :item_id 
                and serverGroup = :serverGroup",
            ['account_id'  => $account_id,
             'item_id'     => $item_id,
             'serverGroup' => $serverGroup]
        );
        if (!$qwe || !$qwe->rowCount()){
            return false;
        }
        $Price = $qwe->fetchObject(self::class);
        $Price->method = 'byAccount';
        return $Price;
    }

    public static function byToNPC(int $item_id): self|bool
    {

        if(!in_array($item_id,Item::privateItems())){
            return false;
        }

        if(self::isCurrency($item_id)){
            return false;
        }


        //Можно ли продать NPC?
        if($toNPC = self::toNPC($item_id)){
            $Price = new self();
            $Price->account_id = 1;
            $Price->price = $toNPC;
            $Price->method = 'byToNPC';
            return $Price;
        }
        return false;
    }

    public static function byInput(int $account_id, int $item_id, int $serverGroup, int $price): self|bool
    {
        $Price = new self();
        $Price->account_id = $account_id;
        $Price->item_id = $item_id;
        $Price->serverGroup = $serverGroup;
        $Price->price = $price;
        return $Price;
    }

    private static function toNPC(int $item_id): int|bool
    {
        $qwe = qwe("
            select priceToNPC from items 
            where item_id = :item_id and priceToNPC",
            ['item_id'=>$item_id]
        );
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        return $qwe->fetchAll(PDO::FETCH_COLUMN)[0];
    }

    public static function isCurrency(int $item_id): bool
    {
        $qwe = qwe("select * from valutas where id = :item_id", ['item_id'=>$item_id]);
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
            'account_id'  => $this->account_id,
            'serverGroup' => $this->serverGroup,
            'item_id'     => $this->item_id,
            'price'       => $this->price,
            'datetime'    => $this->datetime ?? date('Y-m-d H:i:s')
        ];
        return DB::replace('uacc_prices', $params);
    }

}