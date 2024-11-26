<?php

namespace App\Transfer\Items;

use App\DBServices\ItemFixer;
use App\Transfer\Errors\IconErr;
use App\Transfer\Errors\TransferErr;
use App\Transfer\Page;
use Symphograph\Bicycle\FileHelper;

class PageIcon extends Page
{
    const string iconDir = '/img/icons/90/';

    public string  $newSRC;
    public string  $iconMD5;
    private string $tmpDir;
    private string $tmpFullPath;
    public bool   $readOnly;

    public function __construct(public string $src, public int $itemId)
    {

    }

    /**
     * @throws IconErr
     */
    public function executeTransfer(bool $readOnly = true): void
    {
        $this->readOnly = $readOnly;
        try {
            self::initContent();
        } catch (TransferErr $err) {
            throw new IconErr($err->getMessage());
        }

        self::validateFormat();
        self::saveIconFile();
        self::initMD5();
    }

    /**
     * @throws TransferErr
     */
    private function initContent(): void
    {
        $url = self::site . '/items/' . $this->src;
        self::getContent($url);
    }

    /**
     * @throws IconErr
     */
    private function validateFormat(): void
    {
        self::initDirs();
        FileHelper::delDir($this->tmpDir);
        FileHelper::fileForceContents($this->tmpFullPath, $this->content);

        if(exif_imagetype($this->tmpFullPath) !== IMAGETYPE_PNG){
            throw new IconErr('invalid format');
        }
    }

    /**
     * @throws IconErr
     */
    private function saveIconFile(): void
    {
        if($this->readOnly){
            return;
        }
        $newFullPath = $_SERVER['DOCUMENT_ROOT'] . self::iconDir . $this->newSRC;
        if(!FileHelper::fileForceContents($newFullPath, $this->content)){
            throw new IconErr('error on file save');
        }
    }

    /**
     * @throws IconErr
     */
    private function initMD5(): void
    {
        if(!$md5 = md5_file($this->tmpFullPath)){
            throw new IconErr('error md5');
        }
        $this->iconMD5 = $md5;
    }

    private function initDirs(): void
    {
        $this->tmpDir = dirname($_SERVER['DOCUMENT_ROOT']) . '/tmp/img/';
        $this->newSRC = ItemFixer::iconNameSeparator($this->src);
        $this->tmpFullPath = $this->tmpDir . $this->newSRC;
    }

}