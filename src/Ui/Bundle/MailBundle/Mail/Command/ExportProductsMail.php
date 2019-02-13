<?php

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Command;

use Proximum\Vimeet\Application\Components\Mail\AbstractMail;
use Proximum\Vimeet\Domain\Model\Event;

class ExportProductsMail extends AbstractMail
{
    /** @var string */
    protected $subject = 'mail.product.export.subject';
    
    /** @var string */
    protected $template = 'MailBundle:Mail:Product/export.html.twig';
    
    /** @var string */
    protected $messageId = 'export_products';
    
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
