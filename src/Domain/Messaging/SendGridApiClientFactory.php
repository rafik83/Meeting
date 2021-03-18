<?php

namespace Proximum\Vimeet\Domain\Messaging;

final class SendGridApiClientFactory
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var int
     */
    private $version;

    /**
     * @var string
     */
    private $baseurl;

    /**
     * @param string $key     The SendGrid API key
     * @param int    $version The SendGrid API version
     * @param string $baseurl The SendGrid API base url
     */
    public function __construct($key, $version = 3, $baseurl = 'https://api.sendgrid.com')
    {
        $this->key     = $key;
        $this->version = $version;
        $this->baseurl = $baseurl;
    }

    /**
     * Provides SendGrid API Clients.
     *
     * @return SendGridApiClient
     */
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
