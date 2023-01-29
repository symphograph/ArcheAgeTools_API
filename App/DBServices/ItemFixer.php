<?php

namespace App\DBServices;

use PDO;
use Symphograph\Bicycle\FileHelper;

class ItemFixer
{
    public static function craftableCol(): void
    {
        qwe("update items set craftable = 0 where id")
        or die('craftableCol err');
        qwe("
            update items 
            set craftable = 1 
            where id in 
                  (select distinct resultItemId from crafts where onOff)"
        ) or die('craftableCol err');
    }

    public static function renameIcons(): void
    {
        $qwe = qwe("
            select id, icon from items 
            where icon is not null
            /*limit 10*/
            /*and icon like '%\/%'*/"
        );
        //$icons = $qwe->fetchAll(PDO::FETCH_COLUMN);
        ?>
        <table>
            <tbody>
            <?php
            foreach ($qwe as $q){
                $q = (object) $q;
                $icon = $q->icon;
                $icon .= '.png';
                $fileName = pathinfo($icon, PATHINFO_BASENAME);
                $newFileName = self::iconNameSeparator($icon);
                $oldPath = $_SERVER['DOCUMENT_ROOT'] . '/img/icons/80/' . $fileName;
                $newPath = $_SERVER['DOCUMENT_ROOT'] . '/img/icons/81/' . $newFileName;
                echo "<tr><td>$oldPath</td><td>$newPath</td><td>$newFileName</td></tr>";
                FileHelper::copy($oldPath, $newPath);
                //$data = file_get_contents($oldPath);
                //FileHelper::fileForceContents($newPath, $data);
            }
            ?>
            </tbody>
        </table>
        <?php

    }

    public static function iconNameSeparator(string $str)
    {
        $fileBaseName = pathinfo($str, PATHINFO_BASENAME);
        $dir = pathinfo($str, PATHINFO_DIRNAME);
        if(in_array($dir,['.','..'])){
            $dir = '';
        }
        $arr = explode('_', $fileBaseName);
        $result = '';
        $i = 0;
        foreach ($arr as $pit) {
            $i++;
            $separator = mb_strlen($pit) > 2 ? '/' : '_';
            $result .= $i > 1 ? $separator . $pit : $pit;
        }
        $separator = !empty($dir) ? '/' : '';
        $result = $dir . $separator . $result;
        return str_replace(
            [
                'costume_',
                'customizing_',
                'icon/item/0',
                'icon/item/1',
                'icon/item/2',
                'icon/item/3',
                'icon/item/4',
                'icon/item/5',
                'icon/item/6',
                'icon/item/7',
            ],
            [
                'costume/',
                'customizing/',
                'icon/item/0000/0',
                'icon/item/1000/1',
                'icon/item/2000/2',
                'icon/item/3000/3',
                'icon/item/4000/4',
                'icon/item/5000/5',
                'icon/item/6000/6',
                'icon/item/7000/7',
            ],
            $result
        );
    }
}