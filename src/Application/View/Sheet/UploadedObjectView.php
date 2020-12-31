<?php

namespace Proximum\Vimeet\Application\View\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class UploadedObjectView
{
    /** @var string */
    public $path;

    /** @var string */
    public $filename;

    /** @var bool */
    public $crypted;

    /** @var Sheet */
    public $sheet;

    /** @var null|User */
    public $user;

    /** @var bool */
    public $isSheetData;

    public function __construct(
        string $path,
        string $filename,
        bool $crypted,
        Sheet $sheet,
        ?User $user,
        bool $isSheetData
    ) {
        $this->path = $path;
        $this->filename = $filename;
        $this->crypted = $crypted;
        $this->sheet = $sheet;
        $this->user = $user;
        $this->isSheetData = $isSheetData;
    }
}
