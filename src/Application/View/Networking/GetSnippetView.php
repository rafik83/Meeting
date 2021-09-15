<?php


namespace Proximum\Vimeet\Application\View\Networking;


class GetSnippetView
{
    /** @var string */
    public $providerUrl;

    /** @var string */
    public $subscriberKey;

    /** @var string */
    public $userTopic;

    public function __construct(string $providerUrl, string $subscriberKey, string $userTopic)
    {
        $this->providerUrl = $providerUrl;
        $this->subscriberKey = $subscriberKey;
        $this->userTopic = $userTopic;
    }
}
