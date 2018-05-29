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

    /** @var Sheet[] */
    public $sheets;

    /** @var Event */
    public $event;

    public function __construct(
        array $sheets,
        Event $event,
        $sender,
        $receiver,
        $locale
    ) {
        parent::__construct($sender, $receiver, $locale);

        $this->event = $event;
        $this->sheets = $sheets;
    }
}
