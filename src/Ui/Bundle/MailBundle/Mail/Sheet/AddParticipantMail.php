<?php

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Sheet;

use Proximum\Vimeet\Application\Components\Mail\UserMail;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\View\Participant\ParticipantInfoView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class AddParticipantMail extends UserMail
{
    public const SUBJECT = 'mail.sheet.add_participant_confirmation.subject';
    public const TEMPLATE = 'MailBundle:Mail:Sheet/Invitation/addParticipantConfirmation.html.twig';
    public const TEMPLATE_FULL_TEXT = 'MailBundle:Mail:Sheet/Invitation/addParticipantConfirmation_full_text.html.twig';

    /** @var string */
    protected $subject = self::SUBJECT;

    /** @var string */
    protected $template = self::TEMPLATE;

    /**
     * @var string
     */
    protected $messageId = Events::SHEET_ADD_PARTICIPANT_CONFIRMATION;

    /**
     * @var bool
     */
    protected $sendToEmailTeam = false;

    /**
     * @var User
     */
    private $guest;

    /**
     * @param Event               $event
     * @param string              $sender
     * @param string              $receiver
     * @param string              $locale
     * @param User                $guest
     * @param ParticipantInfoView $participantInfoView
     */
    public function __construct(
        Event $event,
        $sender,
        $receiver,
        $locale,
        User $guest,
        ParticipantInfoView $participantInfoView
    ) {
        parent::__construct($sender, $receiver, $locale, $event, $participantInfoView);

        $this->guest = $guest;
    }

    /**
     * @return User
     */
    public function getGuest()
    {
        return $this->guest;
    }
}
