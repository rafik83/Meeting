<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Messaging;

use SendGrid\Client;
use SendGrid\Mail;

final class SendgridApiClient extends Client
{
    public function send(Mail $mail)
    {
        return $this->mail()->send()->post($mail);
    }
}
