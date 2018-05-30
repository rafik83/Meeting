<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Sheet;

use Proximum\Vimeet\Application\Components\Mail\AbstractMail;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;

class SheetsDuplicatedMail extends AbstractMail
{
    /** @var string */
    protected $subject = 'mail.event.sheets_duplicated.subject';

    /** @var string */
    protected $template = 'MailBundle:Mail:Event/sheetsDuplicated.html.twig';

    /** @var string */
    protected $messageId = Events::EVENT_SHEETS_DUPLICATED;

    /** @var Event */
    public $event;

    /** @var Event */
    public $originEvent;

    /** @var Sheet[] */
    public $importedSheets;

    public function __construct(
        Event $event,
        Event $originEvent,
        array $importedSheets,
        $sender,
        $receiver,
        $locale
    ) {
        parent::__construct($sender, $receiver, $locale);

        $this->event = $event;
        $this->originEvent = $originEvent;
        $this->importedSheets = $importedSheets;
    }

    public function getSubjectParameters(): array
    {
        return [
            '%event%' => $this->event->getTitle(),
            '%eventOrigin%' => $this->originEvent->getTitle(),
        ];
    }
}
