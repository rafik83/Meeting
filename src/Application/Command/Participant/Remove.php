<?php

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;

class Remove implements Command
{
    /** @var Sheet */
    public $sheet;

    /** @var Participant[] */
    public $participants = [];

    /** @var string */
    public $locale;

    /**
     * @param Sheet  $sheet
     * @param string $locale
     */
    public function __construct(Sheet $sheet, string $locale)
    {
        $this->sheet  = $sheet;
        $this->locale = $locale;
    }
}
