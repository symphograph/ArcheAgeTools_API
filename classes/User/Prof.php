<?php

namespace User;
use PDO;

class Prof
{
    public int     $id = 25;
    public ?string $name = 'Прочее';
    public ?int    $lvl = 1;
    public ?int    $laborBonus = 0;
    public ?int    $timeBonus = 0;

    public function __set(string $name, $value): void
    {
    }

    public static function byNeed(int $profId, int $profNeed = 0): self|bool
    {

        $qwe = qwe("select * from profs
            inner join profLvls on id = :profId
            and :profNeed between profLvls.min and profLvls.max
            order by lvl desc limit 1
            ",
            ['profId' => $profId, 'profNeed' => $profNeed]
        );
        if (!$qwe || !$qwe->rowCount()) {
            return false;
        }
        return $qwe->fetchObject(self::class);
    }

    public static function byLvl(int $id, int $lvl = 1): self|bool
    {
        $qwe = qwe("select * from profs
            inner join profLvls on id = :id
            and profLvls.lvl = :lvl
            ",
            ['id' => $id, 'lvl' => $lvl]
        );
        if (!$qwe || $qwe->rowCount()) {
            return false;
        }
        return $qwe->fetchObject(self::class);
    }

    /**
     * @return array<self>|bool
     */
    public static function getAccountProfs(int $accountId): array|bool
    {
        $qwe = qwe("
            select * from
             (
                 select profs.*,
                        if(up.lvl, up.lvl, 1) as lvl
                 from profs
                   left join uacc_profs up
                     on profs.id = up.profId
                         and up.accountId = :accountId
                   where profs.used
             ) as tmp
            inner join profLvls pL 
                on tmp.lvl = pL.lvl
            ", ['accountId' => $accountId]
        );
        if (!$qwe || !$qwe->rowCount()) {
            return false;
        }
        return $qwe->fetchAll(PDO::FETCH_CLASS, self::class);
    }

    /**
     * @return array<self>|bool
     */
    public static function getList(): array|bool
    {
        $qwe = qwe("select * from profs where used");
        if (!$qwe || !$qwe->rowCount()) {
            return false;
        }
        return $qwe->fetchAll(PDO::FETCH_CLASS, self::class);
    }

    public static function saveLvl(int $accountId, int $profId, int $lvl): bool
    {
        $qwe = qwe("
        replace into uacc_profs 
            (accountId, profId, lvl) 
        VALUES 
            (:accountId, :profId, :lvl)",
        ['accountId'=>$accountId, 'profId'=>$profId, 'lvl'=>$lvl]
        );
        return boolval($qwe);
    }

    public static function getAccProfById(int $profId) :Prof|bool
    {
        if($profId === 25){
            return new self();
        }
        global $Account;
        if(empty($Account->AccSets->Profs)){
            $Account->AccSets->initProfs();
        }
        $profs = $Account->AccSets->Profs;

        foreach ($profs as $prof){
            if($prof->id === $profId){
                return $prof;
            }
        }

        return false;
    }

}