<?php

namespace App\Transfer\Crafts;

use App\DTO\DTO;
use Symphograph\Bicycle\DTO\DTOTrait;
use PDO;

class NewCraft extends DTO
{
    use DTOTrait;
    const tableName = '`NewCrafts_20230622`';
    const importedFile = '/includes/ttt.php';
    public int    $id;
    public string $craftName;
    public int    $profNeed;
    public int    $resultAmount;

    /**
     * @return self[]
     */
    public static function listByFile(): array
    {
        $craftList = require dirname($_SERVER['DOCUMENT_ROOT']) . self::importedFile;
        $list = [];
        foreach ($craftList as $craft) {
            unset($craft[1]);
            $list[] = self::byFile($craft);
        }
        return $list;
    }

    public static function byFile(array $craft): self
    {
        $craft = array_map('strip_tags', $craft);
        $NewCraft = new self();
        $NewCraft->id = intval($craft[0]);
        $NewCraft->craftName = $craft[2];
        $NewCraft->profNeed = $craft[3];
        $NewCraft->resultAmount = self::extractAmount($craft[4]);
        $NewCraft->putToDB();
        return $NewCraft;
    }

    private static function extractAmount(string $str): int
    {
        $arr = explode(' x ',$str);
        return trim($arr[1]);
    }
}