<?php

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\ThirdParty\Comexposium\SSO\Participant;

use Proximum\Vimeet\Application\Components\Mail\UserMail;
use Proximum\Vimeet\Application\View\Participant\ParticipantInfoView;
use Proximum\Vimeet\Domain\Model\Event;

class ParticipantAddedMail extends UserMail
{
    public const SUBJECT = 'mail.thirdParty.comexposium.sso.participantAdded.subject';
    public const TEMPLATE = 'MailBundle:Mail:ThirdParty/Comexposium/SSO/Participant/participantAdded.html.twig';
    public const TEMPLATE_FULL_TEXT = 'MailBundle:Mail:ThirdParty/Comexposium/SSO/Participant/participantAdded_full_text.html.twig';

    /** @var string */
    protected $subject = self::SUBJECT;

    /** @var string */
    protected $template = self::TEMPLATE;

    /** @var string */
    protected $messageId = 'comexposium.sso.participant_added';

    /** @var bool */
    protected $sendToEmailTeam = false;

    /**
     * @param Event               $event
     * @param string              $sender
     * @param string              $receiver
     * @param string              $locale
     * @param ParticipantInfoView $participantInfoView
     */
    public function __construct(
        Event $event,
        $sender,
        $receiver,
        $locale,
        ParticipantInfoView $participantInfoView
    ) {
        parent::__construct($sender, $receiver, $locale, $event, $participantInfoView);
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
}
