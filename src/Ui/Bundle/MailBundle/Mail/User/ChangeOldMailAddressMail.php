<?php

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\User;

use Proximum\Vimeet\Application\Components\Mail\UserMail;
use Proximum\Vimeet\Application\View\Participant\ParticipantInfoView;
use Proximum\Vimeet\Domain\Model\Event;

class ChangeOldMailAddressMail extends UserMail
{
    public const SUBJECT = 'mail.changeMailOld.subject';
    public const TEMPLATE = 'MailBundle:Mail:ChangeMail/oldMail.html.twig';
    public const TEMPLATE_FULL_TEXT = 'MailBundle:Mail:ChangeMail/oldMail_full_text.html.twig';

    /** @var string */
    protected $subject = self::SUBJECT;

    /** @var string */
    protected $template = self::TEMPLATE;

    /**
     * @var string
     */
    protected $messageId = 'change_mail_old';

    /**
     * @var string
     */
    private $newMail;

    /**
     * @var bool
     */
    protected $sendToEmailTeam = false;

    /**
     * @param Event               $event
     * @param string              $sender
     * @param string              $receiver
     * @param string              $locale
     * @param string              $newMail
     * @param ParticipantInfoView $participantInfo
     */
    public function __construct(
        Event $event,
        $sender,
        $receiver,
        $locale,
        $newMail,
        ParticipantInfoView $participantInfo
    ) {
        parent::__construct($sender, $receiver, $locale, $event, $participantInfo);

        $this->newMail = $newMail;
    }

    /**
     * @return string
     */
    public function getNewMail()
    {
        return $this->newMail;
    }
}
