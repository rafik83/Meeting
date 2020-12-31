<?php

namespace Proximum\Vimeet\Infrastructure\Encryption;

use Defuse\Crypto\File;
use Proximum\Vimeet\Application\Adapter\SheetEncryptFileInterface;
use Proximum\Vimeet\Domain\Model\Sheet;

class SheetEncryptFileAdapter implements SheetEncryptFileInterface
{
    /** @var SheetKeyGetter */
    private $sheetKeyGetter;

    public function __construct(SheetKeyGetter $sheetKeyGetter)
    {
        $this->sheetKeyGetter = $sheetKeyGetter;
    }

    public function encryptFile(Sheet $sheet, string $initialFilePath, string $encryptedFilePath): void
    {
        $key = $this->sheetKeyGetter->getKeyBySheet($sheet);
        File::encryptFile($initialFilePath, $encryptedFilePath, $key);
    }
}
