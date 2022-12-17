<?php

namespace User;

use Auth\Mailru\MailruUser;
use Auth\Telegram\TeleUser;
use Symphograph\Bicycle\DB;

class User
{
    public int|null $id;
    public string|null $created;
    public Sess|null $Sess;

    public function __set(string $name, $value): void{}

    public static function byId(int $id): self|bool
    {
        $qwe = qwe("select * from users where id = :id", ['id'=>$id]);
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        return $qwe->fetchObject(self::class);
    }

    public static function create(): self|bool
    {
        $User = new User();
        if(!($id = DB::createNewID('users', 'id'))){
            return false;
        }
        $User->id = $id;
        $User->created = date('Y-m-d H:i:s');

        if($User->putToDB()){
            return $User;
        }
        return false;
    }

    private function putToDB(): bool
    {
        $params = [
            'id' => $this->id,
            'created' => $this->created
        ];
        return DB::replace('users',$params);
    }

    public static function delete(int $id): bool
    {
        $qwe = qwe("delete FROM users where id = :id",['id'=>$id]);
        return boolval($qwe);
    }
}