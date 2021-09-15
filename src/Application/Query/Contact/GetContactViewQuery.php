<?php

namespace Proximum\Vimeet\Application\Query\Contact;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class GetContactViewQuery implements Query
{
    /** @var Event */
    public $event;

    /** @var Sheet */
    public $userSheet;

    /** @var Participant */
    public $seerParticipant;

    /** @var User */
    public $contact;

    /** @var string */
    public $locale;

    public function __construct(
        Event $event,
        Sheet $userSheet,
        Participant $seerParticipant,
        User $contact,
        string $locale
    ) {
        $this->event = $event;
        $this->userSheet = $userSheet;
        $this->seerParticipant = $seerParticipant;
        $this->contact = $contact;
        $this->locale = $locale;
    }
}
