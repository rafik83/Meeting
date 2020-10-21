<?php


namespace Proximum\Vimeet\Application\View\Networking;


class PrivateChatView
{
    /** @var string */
    public $providerUrl;

    /** @var string */
    public $subscriberKey;

    /** @var string */
    public $topic;

    /** @var string */
    public $toUserFirstName;

    /** @var string */
    public $toUserLastName;

    /** @var string|null */
    public $toUserCompany;

    /** @var string */
    public $toUserPosition;

    /** @var int */
    public $toUserId;

    /** @var int */
    public $chatSessionId;

    /** @var bool */
    public $hasVisioButton;

    public function __construct(
        string $providerUrl,
        string $subscriberKey,
        string $topic,
        ?string $toUserFirstName,
        ?string $toUserLastName,
        ?string $toUserCompany,
        ?string $toUserPosition,
        int $toUserId,
        int $chatSessionId,
        bool $hasVisioButton
    ) {
        $this->providerUrl = $providerUrl;
        $this->subscriberKey = $subscriberKey;
        $this->topic = $topic;
        $this->toUserFirstName = $toUserFirstName;
        $this->toUserLastName = $toUserLastName;
        $this->toUserCompany = $toUserCompany;
        $this->toUserPosition = $toUserPosition;
        $this->toUserId = $toUserId;
        $this->chatSessionId = $chatSessionId;
        $this->hasVisioButton = $hasVisioButton;
    }
}
