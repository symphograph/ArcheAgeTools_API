<?php

namespace App\Transfer;



use App\DBServices\ItemFixer;
use Symphograph\Bicycle\FileHelper;

class PageIcon extends Page
{
    const iconDir = '/img/icons/90/';

    public string  $newSRC;
    public string  $iconMD5;
    private string $tmpDir;
    private string $tmpFullPath;
    private string $newFullPath;

    public function __construct(public string $src, public int $itemId)
    {

    }

    public function executeTransfer(bool $readOnly = true): bool
    {
        return match (false){
            self::initContent() => false,
            self::isIconPNG() => false,
            self::saveIconFile() => false,
            self::initMD5() => false,
            default => true
        };
    }

    private function initContent(): bool
    {
        $url = self::site . '/items/' . $this->src;
        return self::getContent($url);
    }

    private function isIconPNG(): bool
    {
        self::initDirs();
        FileHelper::delDir($this->tmpDir);
        FileHelper::fileForceContents($this->tmpFullPath, $this->content);

        if(exif_imagetype($this->tmpFullPath) !== IMAGETYPE_PNG){
            $this->error = 'invalid format';
            return false;
        }
        return true;
    }

    private function saveIconFile(): bool
    {
        $this->newFullPath = $_SERVER['DOCUMENT_ROOT'] . self::iconDir . $this->newSRC;
        if(!FileHelper::fileForceContents($this->newFullPath, $this->content)){
            $this->error = 'error on file save';
            return false;
        }
        return true;
    }

    private function initMD5(): bool
    {
        if(!$md5 = md5_file($this->newFullPath)){
            $this->error = 'error md5';
            return false;
        }
        $this->iconMD5 = $md5;
        return true;
    }

    private function initDirs(): void
    {
        $this->tmpDir = dirname($_SERVER['DOCUMENT_ROOT']) . '/tmp/img/';
        $this->newSRC = ItemFixer::iconNameSeparator($this->src);
        $this->tmpFullPath = $this->tmpDir . $this->newSRC;
        //printr($this->tmpFullPath);
    }

}