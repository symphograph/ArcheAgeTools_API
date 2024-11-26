<?php

namespace App\Prof\Repo;

use App\Prof\Prof;
use App\Prof\Repo\RepITF;

class ProfRepo implements RepITF
{

    static function get(int $id): Prof
    {
        $prof = RepoMemory::get($id);
        if(!empty($prof)) return $prof;

        $profs = RepoDB::getList();
        RepoMemory::setList($profs);

        return RepoMemory::get($id);
    }


    /**
     * @return Prof[]
     */
    static function getList(): array
    {
        $profs = RepoMemory::getList();
        if(!empty($profs)) return $profs;

        $profs = RepoDB::getList();
        RepoMemory::setList($profs);
        return $profs;
    }
}