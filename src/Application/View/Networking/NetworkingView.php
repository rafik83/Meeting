<?php


namespace Proximum\Vimeet\Application\View\Networking;


use Proximum\Vimeet\Domain\Model\User;

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

    /** @var int */
    public $userCurrentId;

    public function __construct(string $providerUrl, string $subscriberKey, string $topic, array $subscriptions, int $userCurrentId)
    {
        $this->providerUrl = $providerUrl;
        $this->subscriberKey = $subscriberKey;
        $this->topic = $topic;
        $this->subscriptions = $subscriptions;
        $this->userCurrentId = $userCurrentId;
    }
}
