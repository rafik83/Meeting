<?php


namespace Proximum\Vimeet\Application\View\Networking;

use Proximum\Vimeet\Domain\Model\ChatSession;

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

    /** @var ChatSession[] */
    public $privateChatSessions;

    /**
     * @param ChatSession[] $privateChatSessions
     */
    public function __construct(string $providerUrl, string $subscriberKey, string $topic, array $subscriptions, array $privateChatSessions)
    {
        $this->providerUrl = $providerUrl;
        $this->subscriberKey = $subscriberKey;
        $this->topic = $topic;
        $this->subscriptions = $subscriptions;
        $this->privateChatSessions = $privateChatSessions;
    }
}
