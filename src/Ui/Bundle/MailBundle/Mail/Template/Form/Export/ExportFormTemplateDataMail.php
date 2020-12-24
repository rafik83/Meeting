<?php

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Template\Form\Export;

use Proximum\Vimeet\Application\Components\Mail\AdminMail;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;

class ExportFormTemplateDataMail extends AdminMail
{
    /** @var string */
    protected $subject = 'admin.mail.export_form_template_data.subject';

    /** @var string */
    protected $template = 'MailBundle:Mail:Template/Form/Export/exportFormTemplateData.html.twig';

    /** @var string */
    protected $messageId = 'EXPORT_FORM_TEMPLATE_DATA_ID';

    /** @var File */
    protected $file;

    public function __construct(string $sender, string $receiver, string $locale, Event $event, File $file)
    {
        parent::__construct($sender, $receiver, $locale);

        $this->event = $event;
        $this->file = $file;
    }

    public function getFile(): File
    {
        return $this->file;
    }
}
