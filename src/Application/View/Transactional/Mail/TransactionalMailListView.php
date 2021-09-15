<?php

namespace Proximum\Vimeet\Application\View\Transactional\Mail;

class TransactionalMailListView
{
    /** @var MailView[] */
    public $mailViews;

    public function __construct(array $mailViews = [])
    {
        $this->mailViews = $mailViews;
    }
}
