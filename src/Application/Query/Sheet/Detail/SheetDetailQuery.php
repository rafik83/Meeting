<?php

namespace Proximum\Vimeet\Application\Query\Sheet\Detail;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Sheet;

class SheetDetailQuery implements Query
{
    /** @var Sheet */
    public $sheet;

    /** @var string */
    public $locale;

    /** @var Admin */
    public $admin;

    public function __construct(Admin $admin, Sheet $sheet, string $locale)
    {
        $this->sheet  = $sheet;
        $this->locale = $locale;
        $this->admin = $admin;
    }
}
