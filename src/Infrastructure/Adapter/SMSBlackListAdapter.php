<?php

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Ovh\Api;
use Proximum\Vimeet\Application\Adapter\SMSBlackListInterface;

class SMSBlackListAdapter implements SMSBlackListInterface
{
    /** @var Api */
    private $api;

    /** @var string */
    private $ovhServiceName;

    /**
     * @param Api    $api
     * @param string $ovhServiceName
     */
    public function __construct(Api $api, string $ovhServiceName)
    {
        $this->api = $api;
        $this->ovhServiceName = $ovhServiceName;
    }

    /**
     * @return array
     */
    public function getBlackList(): array
    {
        return $this->api->get(sprintf('/sms/%s/blacklists', $this->ovhServiceName));
    }
}
