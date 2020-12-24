<?php

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Sheet;

use Proximum\Vimeet\Application\Components\Mail\UserMail;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\View\Participant\ParticipantInfoView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet\Group;

class SheetGroupCreatedMail extends UserMail
{
    public const SUBJECT = 'mail.sheet.group.created.subject';
    public const TEMPLATE = 'MailBundle:Mail:Sheet/groupCreated.html.twig';
    public const TEMPLATE_FULL_TEXT = 'MailBundle:Mail:Sheet/groupCreated_full_text.html.twig';

    /** @var string */
    protected $subject = self::SUBJECT;

    /** @var string */
    protected $template = self::TEMPLATE;

    /** @var string */
    protected $messageId = Events::SHEET_GROUP_CREATED;

    /** @var bool */
    protected $sendToEmailTeam = true;

    /** @var Group */
    private $group;

    /**
     * SheetGroupCreatedMail constructor.
     *
     * @param string              $sender
     * @param string              $receiver
     * @param string              $locale
     * @param Event               $event
     * @param ParticipantInfoView $participantInfoView
     * @param Group               $group
     */
    public function __construct(
        $sender,
        $receiver,
        $locale,
        Event $event,
        ParticipantInfoView $participantInfoView,
        Group $group
    ) {
        parent::__construct($sender, $receiver, $locale, $event, $participantInfoView);

        $this->group = $group;
    }

    /**
     * @return Group
     */
    public function getGroup()
    {
        return $this->group;
    }
}
