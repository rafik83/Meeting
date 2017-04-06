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
use SendGrid\Response;

/**
 * Wrapper around the SendGrid Web API client.
 */
class SendGridApiClient extends Client
{
    /**
     * Sends an email through the SendGrid Web API.
     *
     * @param Mail $mail
     *
     * @return Response
     */
    public function send(Mail $mail)
    {
        return $this->mail()->send()->post($mail);
    }
}
