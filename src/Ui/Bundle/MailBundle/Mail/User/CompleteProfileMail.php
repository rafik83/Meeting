<?php

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\User;

use Proximum\Vimeet\Application\Components\Mail\UserMail;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\View\Participant\ParticipantInfoView;
use Proximum\Vimeet\Domain\Model\Participant;

class CompleteProfileMail extends UserMail
{
    public const SUBJECT = 'mail.completeProfile.subject';
    public const TEMPLATE = 'MailBundle:Mail:User/completeProfile.html.twig';
    public const TEMPLATE_FULL_TEXT = 'MailBundle:Mail:User/completeProfile_full_text.html.twig';

    /** @var string */
    protected $subject = self::SUBJECT;

    /** @var string */
    protected $template = self::TEMPLATE;

    /**
     * @var string
     */
    protected $messageId = Events::USER_PROFILE_COMPLETED;

    /**
     * @var Participant
     */
    private $participant;

    /**
     * @var bool
     */
    protected $sendToEmailTeam = false;

    /**
     * @var string
     */
    protected $firstname;

    /**
     * @var string
     */
    protected $lastname;

    /** @var string */
    private $eventActivateAccountAlreadyKnownUrl;

    /**
     * @param Participant         $participant
     * @param string              $sender
     * @param string              $receiver
     * @param string              $locale
     * @param ParticipantInfoView $participantInfoView
     * @param string              $eventActivateAccountAlreadyKnownUrl
     */
    public function __construct(
        Participant $participant,
        $sender,
        $receiver,
        $locale,
        ParticipantInfoView $participantInfoView,
        string $eventActivateAccountAlreadyKnownUrl
    ) {
        parent::__construct(
            $sender,
            $receiver,
            $locale,
            $participant->getSheet()->getEvent(),
            $participantInfoView
        );

        $this->participant = $participant;
        $this->eventActivateAccountAlreadyKnownUrl = $eventActivateAccountAlreadyKnownUrl;
    }

    /**
     * @return Participant
     */
    public function getParticipant()
    {
        return $this->participant;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubjectParameters()
    {
        return [
            '%event%' => $this->getEvent()->getTitle(),
        ];
    }

    public function getEventActivateAccountAlreadyKnownUrl(): string
    {
        return $this->eventActivateAccountAlreadyKnownUrl;
    }
}
