<?php

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;

class ChangeOwner implements Command
{
    /** @var Sheet */
    public $sheet;

    /** @var Admin */
    public $admin;

    /** @var null|Participant */
    public $owner;

    /** @var string */
    public $locale;

    public function __construct(
        Sheet $sheet,
        Admin $admin,
        string $locale
    ) {
        $this->sheet = $sheet;
        $this->admin = $admin;
        $this->owner = $sheet->getParticipantOwner();
        $this->locale = $locale;
    }
}
