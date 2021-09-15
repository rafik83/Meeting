<?php

namespace Proximum\Vimeet\Application\Command\Encryption;

use Proximum\Vimeet\Application\Adapter\SheetEncryptFileInterface;
use Proximum\Vimeet\Application\Adapter\UserEventEncryptFileInterface;

class EncryptHandler
{
    /** @var SheetEncryptFileInterface */
    private $sheetEncryptFile;

    /** @var UserEventEncryptFileInterface */
    private $userEventEncryptFile;

    public function __construct(
        SheetEncryptFileInterface $sheetEncryptFile,
        UserEventEncryptFileInterface $userEventEncryptFile
    ) {
        $this->sheetEncryptFile = $sheetEncryptFile;
        $this->userEventEncryptFile = $userEventEncryptFile;
    }

    public function handle(Encrypt $encrypt)
    {
        if ($encrypt->isSheetData) {
            $this->sheetEncryptFile->encryptFile(
                $encrypt->sheet,
                $encrypt->initialFilePath,
                $encrypt->encryptedFilePath
            );

            return;
        }

        $this->userEventEncryptFile->encryptFile(
            $encrypt->sheet->getEvent(),
            $encrypt->user,
            $encrypt->initialFilePath,
            $encrypt->encryptedFilePath
        );
    }
}
