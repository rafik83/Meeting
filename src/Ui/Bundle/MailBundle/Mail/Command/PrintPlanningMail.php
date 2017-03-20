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
use Proximum\Vimeet\Domain\View\TypeView;

class PrintPlanningMail extends AbstractMail
{
    /** @var string */
    protected $subject = 'mail.planning.print.subject';

    /** @var string */
    protected $template = 'MailBundle:Mail:Planning/print.html.twig';

    /** @var string */
    protected $messageId = 'export_planning_print';

    /** @var string */
    public $filePath;

    /** @var TypeView[] */
    public $typeViews;

    /** @var string */
    public $orderBy;

    /** @var Event */
    public $event;

    /** @var string */
    public $fileHash;

    /** @var int */
    public $fileId;

    /**
     * @param Event      $event
     * @param string     $sender
     * @param string     $receiver
     * @param string     $locale
     * @param string     $fileHash
     * @param int        $fileId
     * @param TypeView[] $typeViews
     * @param string     $orderBy
     */
    public function __construct(
        Event $event,
        $sender,
        $receiver,
        $locale,
        $fileHash,
        $fileId,
        array $typeViews = [],
        $orderBy
    ) {
        parent::__construct($sender, $receiver, $locale);

        $this->event     = $event;
        $this->fileHash  = $fileHash;
        $this->fileId    = $fileId;
        $this->typeViews = $typeViews;
        $this->orderBy   = $orderBy;
    }
}
