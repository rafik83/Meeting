<?php

namespace Proximum\Vimeet\Application\Adapter;

use Proximum\Vimeet\Domain\Model\Sheet;

interface SheetDecryptFileInterface
{
    public function decryptFile(Sheet $sheet, string $encryptedFilePath, string $decryptedFilePath): void;
}
