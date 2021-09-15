<?php

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\View;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Transactional\Mail\Constant;

class PrepareParticipantAddedMailView extends AbstractPrepareMail
{
    /** @var Participant */
    public $guest;

    public function __construct(
        Event $event,
        User $user,
        string $locale,
        Sheet $sheet,
        Participant $guest
    ) {
        parent::__construct(
            $event,
            $user,
            Constant::TRANSACTIONAL_MAIL_KEY_PARTICIPANT_ADDED_CONFIRMATION,
            $locale,
            $sheet
        );

        $this->guest = $guest;
    }
}
