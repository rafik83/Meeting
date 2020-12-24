<?php

namespace Proximum\Vimeet\Application\Command\Encryption;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class Decrypt
{
    /** @var Sheet */
    public $sheet;

    /** @var null|User */
    public $user;

    /** @var bool */
    public $isSheetData;

    /** @var string */
    public $encryptedFilePath;

    /** @var string */
    public $destinationFilePath;

    public function __construct(
        Sheet $sheet,
        ?User $user,
        bool $isSheetData,
        string $encryptedFilePath,
        string $destinationFilePath
    ) {
        $this->sheet = $sheet;
        $this->user = $user;
        $this->isSheetData = $isSheetData;
        $this->encryptedFilePath = $encryptedFilePath;
        $this->destinationFilePath = $destinationFilePath;
    }
}
