<?php

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Invoice;

use Proximum\Vimeet\Application\Components\Mail\AbstractMail;
use Proximum\Vimeet\Domain\Model\Event;

class PrintInvoicesMail extends AbstractMail
{
    /** @var string */
    protected $subject = 'admin.mail.invoices.print.subject';

    /** @var string */
    protected $template = 'MailBundle:Mail:Invoice/printInvoices.html.twig';

    /** @var string */
    protected $messageId = 'print_invoices';

    /** @var string */
    public $filePath;

    /** @var Event */
    public $event;

    /** @var string */
    public $fileHash;

    /** @var int */
    public $fileId;

    public function __construct(
        Event $event,
        string $sender,
        string $receiver,
        string $locale,
        string $fileHash,
        int $fileId
    ) {
        parent::__construct($sender, $receiver, $locale);

        $this->event     = $event;
        $this->fileHash  = $fileHash;
        $this->fileId    = $fileId;
    }
}
