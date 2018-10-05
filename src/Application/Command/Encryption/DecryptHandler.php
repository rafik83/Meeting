<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Encryption;

use Proximum\Vimeet\Application\Adapter\SheetDecryptFileInterface;
use Proximum\Vimeet\Application\Adapter\UserEventDecryptFileInterface;

class DecryptHandler
{
    /** @var SheetDecryptFileInterface */
    private $sheetDecryptFile;

    /** @var UserEventDecryptFileInterface */
    private $userEventDecryptFile;

    public function __construct(
        SheetDecryptFileInterface $sheetDecryptFile,
        UserEventDecryptFileInterface $userEventDecryptFile
    ) {
        $this->sheetDecryptFile = $sheetDecryptFile;
        $this->userEventDecryptFile = $userEventDecryptFile;
    }

    public function handle(Decrypt $decrypt)
    {
        if ($decrypt->isSheetData) {
            $this->sheetDecryptFile->decryptFile(
                $decrypt->sheet,
                $decrypt->encryptedFilePath,
                $decrypt->destinationFilePath
            );

            return;
        }

        $this->userEventDecryptFile->decryptFile(
            $decrypt->sheet->getEvent(),
            $decrypt->user,
            $decrypt->encryptedFilePath,
            $decrypt->destinationFilePath
        );
    }
}
