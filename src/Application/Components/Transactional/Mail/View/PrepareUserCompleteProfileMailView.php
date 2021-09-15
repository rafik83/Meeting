<?php

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\View;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Transactional\Mail\Constant;

class PrepareUserCompleteProfileMailView extends AbstractPrepareMail
{
    /** @var Participant */
    public $participant;

    public function __construct(
        Event $event,
        User $user,
        string $locale,
        Sheet $sheet,
        Participant $participant
    ) {
        parent::__construct(
            $event,
            $user,
            Constant::TRANSACTIONAL_MAIL_KEY_USER_COMPLETE_PROFILE,
            $locale,
            $sheet
        );

        $this->participant = $participant;
    }

    public function getParticipant(): Participant
    {
        return $this->participant;
    }
}
