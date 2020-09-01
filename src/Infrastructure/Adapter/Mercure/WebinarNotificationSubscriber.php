<?php

namespace Proximum\Vimeet\Infrastructure\Adapter\Mercure;

use Firebase\JWT\JWT;
use Proximum\Vimeet\Application\Adapter\WebinarNotificationSubscriberInterface;
use Proximum\Vimeet\Domain\Model\Happening;

class WebinarNotificationSubscriber extends AbstractWebinarNotification implements WebinarNotificationSubscriberInterface
{
    /** @var string */
    private $mercureHubUrl;

    /** @var string */
    private $mercureSubscriberKey;

    public function __construct(string $mercureHubUrl, string $mercureSubscriberKey)
    {
        $this->mercureHubUrl = $mercureHubUrl;
        $this->mercureSubscriberKey = $mercureSubscriberKey;
    }

    public function getUrl(): string
    {
        return $this->mercureHubUrl;
    }

    public function getSubscriberKey(Happening $happening, $type): string
    {
        return JWT::encode([
            'mercure' => [
                'subscriber' => ['topic' => $this->getTopic($happening->getId(), $type)],
            ]
        ], $this->mercureSubscriberKey);
    }

}
