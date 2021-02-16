<?php

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\User;

use Proximum\Vimeet\Application\Components\Mail\UserMail;
use Proximum\Vimeet\Application\View\Participant\ParticipantInfoView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class ChangeNewMailAddressMail extends UserMail
{
    public const SUBJECT = 'mail.changeMailNew.subject';
    public const TEMPLATE = 'MailBundle:Mail:ChangeMail/newMail.html.twig';
    public const TEMPLATE_FULL_TEXT = 'MailBundle:Mail:ChangeMail/newMail_full_text.html.twig';

    /** @var string */
    protected $subject = self::SUBJECT;

    /** @var string */
    protected $template = self::TEMPLATE;

    /**
     * @var string
     */
    protected $messageId = 'change_mail_new';

    /**
     * @var string
     */
    private $token;

    /**
     * @var bool
     */
    protected $sendToEmailTeam = false;

    /**
     * @var User
     */
    private $user;

    /**
     * @param Event               $event
     * @param string              $sender
     * @param string              $receiver
     * @param string              $locale
     * @param string              $token
     * @param User                $user
     * @param ParticipantInfoView $participantInfo
     */
    public function __construct(
        Event $event,
        $sender,
        $receiver,
        $locale,
        $token,
        User $user,
        ParticipantInfoView $participantInfo
    ) {
        parent::__construct($sender, $receiver, $locale, $event, $participantInfo);

        $this->token = $token;
        $this->user  = $user;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }
}
