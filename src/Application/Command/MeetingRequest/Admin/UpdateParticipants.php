<?php

namespace Proximum\Vimeet\Application\Command\MeetingRequest\Admin;

use Proximum\Vimeet\Domain\Model\Meeting\Request;

class UpdateParticipants
{
    /**
     * @var Request
     */
    public $request;

    /**
     * @var array
     */
    public $fromParticipants;

    /**
     * @var array
     */
    public $toParticipants;

    /**
     * @param Request $request
     * @param array   $fromParticipants array of from Participant ids
     * @param array   $toParticipants   array of to Participant ids
     */
    public function __construct(Request $request, array $fromParticipants, array $toParticipants)
    {
        $this->request          = $request;
        $this->fromParticipants = $fromParticipants;
        $this->toParticipants   = $toParticipants;
    }
}
