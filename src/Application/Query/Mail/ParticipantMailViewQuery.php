<?php

namespace Proximum\Vimeet\Application\Query\Mail;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class ParticipantMailViewQuery
{
    /**
     * @var Sheet|null
     */
    public $sheet;

    /**
     * @var User
     */
    public $user;

    /**
     * ParticipantMailViewQuery constructor.
     *
     * @param Sheet|null $sheet
     * @param User       $user
     */
    public function __construct(Sheet $sheet = null, User $user)
    {
        $this->sheet = $sheet;
        $this->user  = $user;
    }
}
