<?php

namespace Proximum\Vimeet\Application\Query\Sheet\Detail;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;

class ParticipantDetailQuery
{
    /** @var Event */
    public $event;

    /** @var Sheet */
    public $sheet;

    /** @var string */
    public $locale;

    /** @var Admin */
    public $admin;

    public function __construct(Admin $admin, Sheet $sheet, string $locale)
    {
        $this->event  = $sheet->getEvent();
        $this->sheet  = $sheet;
        $this->locale = $locale;
        $this->admin = $admin;
    }
}
