<?php

namespace Proximum\Vimeet\Application\Adapter;

use Proximum\Vimeet\Application\Components\Mail\AbstractMail;

interface MailerInterface
{
    public function send(AbstractMail $mail);
    public function setHost(string $domain);
}
