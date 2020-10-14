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

    /** @var int */
    public $userCurrentId;

    /** @var int*/
    public $networkingChatNewMessages;

    /** @var ChatSessionView[] */
    public $privateChatSessions;

    /** @var int */
    public $privateChatNewMessages;

    /**
     * @param ChatSessionView[] $privateChatSessions
     */
    public function __construct(
        string $providerUrl,
        string $subscriberKey,
        string $topic,
        array $subscriptions,
        int $userCurrentId,
        int $networkingChatNewMessages,
        array $privateChatSessions,
        int $privateChatNewMessages
    ) {
        $this->providerUrl = $providerUrl;
        $this->subscriberKey = $subscriberKey;
        $this->topic = $topic;
        $this->subscriptions = $subscriptions;
        $this->userCurrentId = $userCurrentId;
        $this->networkingChatNewMessages = $networkingChatNewMessages;
        $this->privateChatSessions = $privateChatSessions;
        $this->privateChatNewMessages = $privateChatNewMessages;
    }
}
