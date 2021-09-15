<?php

namespace Proximum\Vimeet\Application\Components\Mail;

use Proximum\Vimeet\Application\View\Participant\ParticipantInfoView;
use Proximum\Vimeet\Domain\Model\Event;

class UserMail extends AbstractMail implements ParticipantInfoInterface
{
    /**
     * @var ParticipantInfoView
     */
    protected $participantInfo;

    /**
     * @var Event
     */
    protected $event;

    /**
     * UserMail constructor.
     *
     * @param string              $sender
     * @param string              $receiver
     * @param string              $locale
     * @param Event               $event
     * @param ParticipantInfoView $participantInfo
     */
    public function __construct(
        $sender,
        $receiver,
        $locale,
        Event $event,
        ParticipantInfoView $participantInfo
    ) {
        parent::__construct($sender, $receiver, $locale);

        $this->participantInfo = $participantInfo;
        $this->event           = $event;
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstname()
    {
        return $this->participantInfo->firstname;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastname()
    {
        return $this->participantInfo->lastname;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * {@inheritdoc}
     */
    public function getParticipantType()
    {
        return $this->participantInfo->participantType;
    }
}
