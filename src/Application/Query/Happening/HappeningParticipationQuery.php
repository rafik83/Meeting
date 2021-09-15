<?php

namespace Proximum\Vimeet\Application\Query\Happening;

use Proximum\Vimeet\Application\View\Happening\ProgramView;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class HappeningParticipationQuery
{
    /** @var ProgramView */
    public $programView;

    /** @var Sheet */
    public $sheet;

    /** @var User */
    public $currentUser;

    public function __construct(ProgramView $programView, Sheet $sheet, User $currentUser)
    {
        $this->programView = $programView;
        $this->sheet = $sheet;
        $this->currentUser = $currentUser;
    }
}
