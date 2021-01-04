<?php

namespace Proximum\Vimeet\Infrastructure\Encryption;

use Defuse\Crypto\File;
use Proximum\Vimeet\Application\Adapter\SheetDecryptFileInterface;
use Proximum\Vimeet\Domain\Model\Sheet;

class SheetDecryptFileAdapter implements SheetDecryptFileInterface
{
    /** @var SheetKeyGetter */
    private $sheetKeyGetter;

    public function __construct(SheetKeyGetter $sheetKeyGetter)
    {
        $this->sheetKeyGetter = $sheetKeyGetter;
    }

    public function decryptFile(Sheet $sheet, string $encryptedFilePath, string $decryptedFilePath): void
    {
        $key = $this->sheetKeyGetter->getKeyBySheet($sheet);
        File::decryptFile($encryptedFilePath, $decryptedFilePath, $key);
    }
}
