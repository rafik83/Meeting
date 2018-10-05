<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Adapter;

use Proximum\Vimeet\Domain\Model\Sheet;

interface SheetEncryptFileInterface
{
    public function encryptFile(Sheet $sheet, string $initialFilePath, string $encryptedFilePath): void;
}
