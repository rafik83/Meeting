<?php

namespace Proximum\Vimeet\Application\Components\Mail;

use Proximum\Vimeet\Domain\Model\Event;

interface ParticipantInfoInterface
{
    /**
     * @return string
     */
    public function getFirstname();

    /**
     * @return string
     */
    public function getLastname();

    /**
     * @return Event
     */
    public function getEvent();

    /**
     * @return string
     */
    public function getParticipantType();
}
