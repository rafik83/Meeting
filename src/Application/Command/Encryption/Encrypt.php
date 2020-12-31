<?php

namespace Proximum\Vimeet\Application\Command\Encryption;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class Encrypt
{
    /** @var Sheet */
    public $sheet;

    /** @var User */
    public $user;

    /** @var bool */
    public $isSheetData;

    /** @var string */
    public $initialFilePath;

    /** @var string */
    public $encryptedFilePath;

    public function __construct(
        Sheet $sheet,
        User $user,
        bool $isSheetData,
        string $initialFilePath,
        string $encryptedFilePath
    ) {
        $this->sheet = $sheet;
        $this->user = $user;
        $this->isSheetData = $isSheetData;
        $this->initialFilePath = $initialFilePath;
        $this->encryptedFilePath = $encryptedFilePath;
    }
}
