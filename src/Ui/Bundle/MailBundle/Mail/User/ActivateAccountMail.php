<?php

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\User;

use Proximum\Vimeet\Application\Components\Mail\UserMail;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\View\Participant\ParticipantInfoView;
use Proximum\Vimeet\Domain\Model\Event;

class ActivateAccountMail extends UserMail
{
    public const SUBJECT = 'mail.activateAccount.subject';
    public const TEMPLATE = 'MailBundle:Mail:User/activateAccount.html.twig';
    public const TEMPLATE_FULL_TEXT = 'MailBundle:Mail:User/activateAccount_full_text.html.twig';

    /** @var string */
    protected $subject = self::SUBJECT;

    /** @var string */
    protected $template = self::TEMPLATE;

    /**
     * @var string
     */
    protected $messageId = Events::USER_ACCOUNT_ACTIVATED;

    /**
     * @var string
     */
    private $token;

    /**
     * @var bool
     */
    protected $sendToEmailTeam = false;

    /**
     * @var string
     */
    protected $eventActivateAccountAlreadyKnownUrl;

    /**
     * @param Event               $event
     * @param string              $sender
     * @param string              $receiver
     * @param string              $locale
     * @param string              $token
     * @param ParticipantInfoView $participantInfoView
     * @param string              $eventActivateAccountAlreadyKnownUrl
     */

    public function __construct(
        Event $event,
        $sender,
        $receiver,
        $locale,
        $token,
        ParticipantInfoView $participantInfoView,
        string $eventActivateAccountAlreadyKnownUrl
    ) {
        parent::__construct($sender, $receiver, $locale, $event, $participantInfoView);

        $this->token = $token;
        $this->eventActivateAccountAlreadyKnownUrl = $eventActivateAccountAlreadyKnownUrl;
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

    public function getEventActivateAccountAlreadyKnownUrl(): string
    {
        return $this->eventActivateAccountAlreadyKnownUrl;
    }
}
