<?php

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Sheet;

use Proximum\Vimeet\Application\Components\Mail\UserMail;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\View\Participant\ParticipantInfoView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class SheetChangeTypeMail extends UserMail
{
    public const SUBJECT = 'mail.sheet.change_type.subject';
    public const TEMPLATE = 'MailBundle:Mail:Sheet/changeType.html.twig';
    public const TEMPLATE_FULL_TEXT = 'MailBundle:Mail:Sheet/changeType_full_text.html.twig';

    /** @var string */
    protected $subject = self::SUBJECT;

    /** @var string */
    protected $template = self::TEMPLATE;

    /**
     * @var string
     */
    protected $messageId = Events::SHEET_CHANGED_TYPE;

    /**
     * @var User
     */
    private $user;

    /**
     * @var string
     */
    private $toTypeTitle;

    /**
     * @var string
     */
    private $fromTypeTitle;

    /**
     * @var Sheet
     */
    private $sheet;

    /**
     * @var bool
     */
    protected $sendToEmailTeam = true;

    /**
     * @param Event               $event
     * @param Sheet               $sheet
     * @param string              $sender
     * @param string              $receiver
     * @param string              $locale
     * @param User                $user
     * @param string              $fromTypeTitle
     * @param string              $toTypeTitle
     * @param ParticipantInfoView $participantInfoView
     */
    public function __construct(
        Event $event,
        Sheet $sheet,
        $sender,
        $receiver,
        $locale,
        User $user,
        $fromTypeTitle,
        $toTypeTitle,
        ParticipantInfoView $participantInfoView
    ) {
        parent::__construct($sender, $receiver, $locale, $event, $participantInfoView);

        $this->sheet         = $sheet;
        $this->user          = $user;
        $this->fromTypeTitle = $fromTypeTitle;
        $this->toTypeTitle   = $toTypeTitle;
    }

    /**
     * @return Sheet
     */
    public function getSheet()
    {
        return $this->sheet;
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
    public function getFromTypeTitle()
    {
        return $this->fromTypeTitle;
    }

    /**
     * @return string
     */
    public function getToTypeTitle()
    {
        return $this->toTypeTitle;
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
