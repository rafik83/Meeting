<?php

namespace Proximum\Vimeet\Application\Query\MultipleSheets\Request;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class SheetViewQuery
{
    /** @var Sheet */
    public $sheet;

    /** @var string */
    public $locale;

    /** @var User */
    public $user;

    /**
     * @param Sheet  $sheet
     * @param User   $user
     * @param string $locale
     */
    public function __construct(Sheet $sheet, User $user, string $locale)
    {
        $this->sheet  = $sheet;
        $this->locale = $locale;
        $this->user   = $user;
    }
}
