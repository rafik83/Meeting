<?php

namespace Proximum\Vimeet\Application\Adapter;

use Proximum\Vimeet\Domain\Model\Sheet;

interface SheetEncryptFileInterface
{
    public function encryptFile(Sheet $sheet, string $initialFilePath, string $encryptedFilePath): void;
}
