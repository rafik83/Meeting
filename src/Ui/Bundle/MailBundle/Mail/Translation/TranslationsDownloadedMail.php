<?php

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Translation;

use Proximum\Vimeet\Application\Components\Mail\AdminMail;

class TranslationsDownloadedMail extends AdminMail
{
    /** @var string */
    protected $subject = 'admin.mail.translations.downloaded.subject';

    /** @var string */
    protected $template = 'MailBundle:Mail:Translation/TranslationsDownloaded.html.twig';

    /** @var string */
    protected $messageId = 'translations_downloaded';
}
