<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Messaging;

final class SendGridApiClientFactory
{
    private $key;
    private $version;
    private $baseurl;

    public function __construct($key, $version = 3, $baseurl = 'https://api.sendgrid.com')
    {
        $this->key     = $key;
        $this->version = $version;
        $this->baseurl = $baseurl;
    }

    public function createClient()
    {
        $headers = [
            sprintf('Authorization: Bearer %s', $this->key),
            sprintf('User-Agent: sendgrid/%s;php', \SendGrid::VERSION),
            'Accept: application/json',
        ];

        return new SendGridApiClient($this->baseurl, $headers, "/v$this->version");
    }
}
