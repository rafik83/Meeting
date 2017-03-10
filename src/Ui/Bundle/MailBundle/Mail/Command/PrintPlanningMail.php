<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Command;

use Proximum\Vimeet\Application\Components\Mail\AbstractMail;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;

class PrintPlanningMail extends AbstractMail
{
    /**
     * @var string
     */
    protected $subject = 'mail.planning.print.subject';

    /**
     * @var string
     */
    protected $template = 'MailBundle:Mail:Planning/print.html.twig';

    /**
     * @var string
     */
    protected $messageId = 'export_planning_print';

    /**
     * @var string
     */
    public $filePath;

    /**
     * @var Type[]
     */
    public $types;

    /** @var string */
    public $orderBy;

    /**
     * @var Event
     */
    public $event;

    /**
     * @param Event  $event
     * @param string $sender
     * @param string $receiver
     * @param string $locale
     * @param string $filePath
     * @param Type[] $types
     * @param string $orderBy
     */
    public function __construct(
        Event $event,
        $sender,
        $receiver,
        $locale,
        $filePath,
        array $types = [],
        $orderBy
    ) {
        parent::__construct($sender, $receiver, $locale);

        $this->event    = $event;
        $this->filePath = $filePath;
        $this->types    = $types;
        $this->orderBy  = $orderBy;
    }
}
