<?php

namespace App\Item;

use Symphograph\Bicycle\Files\FileIMG;
use Symphograph\Bicycle\Files\TmpUploadFile;

class IconIMG extends FileIMG
{
    const string mainFolder   = '/icons/';
    const string defaultEXT = 'png';

    public static function byUploaded(TmpUploadFile $file): static
    {
        $FileIMG = parent::byUploaded($file);
        if (empty($FileIMG->ext)) {
            $FileIMG->ext = self::defaultEXT;
        }
        return $FileIMG;
    }

    public static function byItemId(int $itemId): ?self
    {
        $item = Item::byId($itemId);
        if(empty($item)) return null;
        return self::byId($item->iconId) ?: null;
    }

    public function save(): void
    {
        printr($this->getFullPath());
    }

}