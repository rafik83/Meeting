<?php

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Command;

use Proximum\Vimeet\Application\Components\Mail\AbstractMail;
use Proximum\Vimeet\Domain\Model\Event;

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

    /** @var string */
    public $orderBy;

    /** @var Event */
    public $event;

    /** @var string */
    public $fileHash;

    /** @var int */
    public $fileId;

    /** @var string */
    public $printOption;

    public function __construct(
        Event $event,
        $sender,
        $receiver,
        $locale,
        $fileHash,
        $fileId,
        $orderBy,
        string $printOption
    ) {
        parent::__construct($sender, $receiver, $locale);

        $this->event = $event;
        $this->fileHash = $fileHash;
        $this->fileId = $fileId;
        $this->orderBy = $orderBy;
        $this->printOption = $printOption;
    }
}
