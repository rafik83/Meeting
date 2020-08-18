<?php

namespace Proximum\Vimeet\Application\Adapter;

use Proximum\Vimeet\Application\Components\Mail\AbstractMail;

interface MailerInterface
{
    /**
     * @param AbstractMail $mail
     */
    public function send(AbstractMail $mail);
}
