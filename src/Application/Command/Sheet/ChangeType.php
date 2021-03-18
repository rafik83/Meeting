<?php

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;

class ChangeType implements Command
{
    /** @var Sheet */
    public $sheet;

    /** @var Type */
    public $type;

    /** @var Admin */
    public $admin;

    /** @var string */
    public $locale;

    /**
     * @param Sheet  $sheet
     * @param Type   $type
     * @param Admin  $admin
     * @param string $locale
     */
    public function __construct(Sheet $sheet, Type $type, Admin $admin, $locale)
    {
        $this->sheet  = $sheet;
        $this->type   = $type;
        $this->admin  = $admin;
        $this->locale = $locale;
    }
}
