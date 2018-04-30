<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Sheet;

use Proximum\Vimeet\Application\Components\Mail\UserMail;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\View\Participant\ParticipantInfoView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet\Group;

class SheetGroupCreatedMail extends UserMail
{
    /** @var string */
    protected $subject = 'mail.sheet.group.created.subject';

    /** @var string */
    protected $template = 'MailBundle:Mail:Sheet/groupCreated.html.twig';

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
