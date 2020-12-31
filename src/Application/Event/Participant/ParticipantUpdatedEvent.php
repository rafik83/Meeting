<?php

namespace Proximum\Vimeet\Application\Event\Participant;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Symfony\Component\EventDispatcher\Event;

class ParticipantUpdatedEvent extends Event
{
    /** @var Participant */
    public $participant;

    /** @var TemplateData */
    public $templateData;

    public function __construct(Participant $participant, TemplateData $templateData)
    {
        $this->participant = $participant;
        $this->templateData = $templateData;
    }
}
