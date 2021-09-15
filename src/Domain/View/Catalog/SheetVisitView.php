<?php

namespace Proximum\Vimeet\Domain\View\Catalog;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class SheetVisitView
{
    /** @var string */
    public $code;

    /** @var int */
    public $count;

    /** @var User|null */
    public $user;

    /** @var Sheet */
    public $sheet;

    public function __construct(string $code, int $count = 0, ?User $user, Sheet $sheet)
    {
        $this->code = $code;
        $this->count = $count;
        $this->user = $user;
        $this->sheet = $sheet;
    }
}
