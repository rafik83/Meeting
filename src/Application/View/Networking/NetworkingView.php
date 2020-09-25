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

    /** @var array */
    public $subscriptions;

    public function __construct(string $providerUrl, string $subscriberKey, string $topic, array $subscriptions)
    {
        $this->providerUrl = $providerUrl;
        $this->subscriberKey = $subscriberKey;
        $this->topic = $topic;
        $this->subscriptions = $subscriptions;
    }
}
