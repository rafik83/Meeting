<?php

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\User;

use Proximum\Vimeet\Application\Components\Mail\UserMail;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\View\Participant\ParticipantInfoView;
use Proximum\Vimeet\Domain\Model\Event;

class ResetPasswordMail extends UserMail
{
    public const SUBJECT = 'mail.resetPassword.subject';
    public const TEMPLATE = 'MailBundle:Mail:User/resetPassword.html.twig';
    public const TEMPLATE_FULL_TEXT = 'MailBundle:Mail:User/resetPassword_full_text.html.twig';

    /** @var string */
    protected $subject = self::SUBJECT;

    /** @var string */
    protected $template = self::TEMPLATE;

    /**
     * @var string
     */
    protected $messageId = Events::USER_PASSWORD_RESET;

    /**
     * @var string
     */
    private $token;

    /**
     * @var bool
     */
    protected $sendToEmailTeam = false;

    /**
     * @param Event               $event
     * @param string              $sender
     * @param string              $receiver
     * @param string              $locale
     * @param string              $token
     * @param ParticipantInfoView $participantInfoView
     */
    public function __construct(
        Event $event,
        $sender,
        $receiver,
        $locale,
        $token,
        ParticipantInfoView $participantInfoView
    ) {
        parent::__construct($sender, $receiver, $locale, $event, $participantInfoView);

        $this->token = $token;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
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
