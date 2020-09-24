<?php


namespace Proximum\Vimeet\Application\View\Networking;


class NetworkingView
{
    /** @var string */
    public $providerUrl;

    /** @var string */
    public $subscriberKey;

    /** @var string */
    public $topic;

    public function __construct(string $providerUrl, string $subscriberKey, string $topic)
    {
        $this->providerUrl = $providerUrl;
        $this->subscriberKey = $subscriberKey;
        $this->topic = $topic;
    }
}
