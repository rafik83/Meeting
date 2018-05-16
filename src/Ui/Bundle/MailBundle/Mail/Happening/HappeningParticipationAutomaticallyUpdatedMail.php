<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Happening;

use Proximum\Vimeet\Application\Components\Mail\AbstractMail;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\View\Happening\HappeningParticipationView;

class HappeningParticipationAutomaticallyUpdatedMail extends AbstractMail
{
    /** @var string */
    protected $subject = 'mail.happening.participation.subject';

    /** @var string */
    protected $template = 'MailBundle:Mail:Happening/participation.html.twig';

    /** @var string */
    protected $messageId = Events::HAPPENING_PARTICIPATION_AUTOMATICALLY_UPDATED;

    /** @var HappeningParticipationView[] */
    public $happeningParticipationViews;

    /** @var Event */
    public $event;

    public function __construct(
        array $happeningParticipationViews,
        Event $event,
        $sender,
        $receiver,
        $locale
    ) {
        parent::__construct($sender, $receiver, $locale);

        $this->event = $event;
        $this->happeningParticipationViews = $happeningParticipationViews;
    }
}
