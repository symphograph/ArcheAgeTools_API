<?php

namespace App\Prof;

use App\Prof\Lvl\ProfLvl;
use App\Prof\Lvl\Repo\ProfLvlRepo;
use App\Prof\Repo\ProfRepo;
use App\User\AccSets;
use PDO;
use Symphograph\Bicycle\PDO\DB;

class Prof
{
    public int     $id         = 25;
    public ?string $name       = 'Прочее';
    public int     $lvl;
    public ?int    $laborBonus = 0;
    public ?int    $timeBonus  = 0;
    public bool    $used;

    public function __set(string $name, $value): void
    {
    }

    public static function newInstance(int $id, string $name, int $lvl): static
    {
        $prof = new static();
        $prof->id = $id;
        $prof->name = $name;
        $prof->lvl = $lvl;
        return $prof;
    }

    public function setLvl(int $lvl): static
    {
        $profLvl = ProfLvlRepo::get($lvl);
        $this->lvl = $lvl;
        $this->laborBonus = $profLvl->laborBonus;
        $this->timeBonus = $profLvl->timeBonus;

        return $this;
    }

    public static function byNeed(int $profId, int $profNeed = 0): self
    {
        $prof = ProfRepo::get($profId);
        $profLvl = ProfLvl::byNeed($profNeed);
        $prof->setLvl($profLvl->lvl);
        return $prof;
    }

    /**
     * @return self[]|bool
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

    public static function saveLvl(int $accountId, int $profId, int $lvl): void
    {
        $sql = "
            replace into uacc_profs 
                (accountId, profId, lvl) 
            VALUES 
                (:accountId, :profId, :lvl)";

        $params = compact('accountId', 'profId', 'lvl');

        DB::qwe($sql, $params);
    }

    public static function getAccProfById(int $profId): Prof|false
    {
        if ($profId === 25) {
            return new self();
        }


        foreach (AccSets::$current->Profs as $prof) {
            if ($prof->id === $profId) {
                return $prof;
            }
        }
        return false;
    }

}