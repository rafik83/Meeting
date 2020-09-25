<?php

namespace Proximum\Vimeet\Infrastructure\Adapter\Mercure;

use Firebase\JWT\JWT;
use InvalidArgumentException;
use Proximum\Vimeet\Application\Adapter\NotificationSubscriberInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\User;

class NotificationSubscriber extends AbstractNotification implements NotificationSubscriberInterface
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

    public function getHappeningSubscriberKey(Happening $happening, int $userId, array $types): string
    {
        if (empty($types)) {
            throw new InvalidArgumentException('Types array cannot be empty');
        }

        return JWT::encode([
            'mercure' => [
                'subscriber' => array_map(function ($type) use ($happening) {
                    return ['topic' => $this->getHappeningTopic($happening->getId(), $type)];
                }, $types),
                'payload' => ['userId' => $userId],
            ]
        ], $this->mercureSubscriberKey);
    }

    public function getNetworkingSubscriberKey(Event $event, User $user, $types): string
    {
        if (empty($types)) {
            throw new InvalidArgumentException('Types array cannot be empty');
        }

        return JWT::encode([
            'mercure' => [
                'subscriber' => array_map(function ($type) use ($event) {
                    return ['topic' => $this->getNetworkingTopic($event->getId(), $type)];
                }, $types),
                'payload' => [
                    'userId' => $user->getId(),
                    'userLastName' => $user->getLastName(),
                    'userFirstName' => $user->getFirstName(),
                    'userPosition' => $user->getPosition(),
                    'userAvatar' => $user->getAvatar(),
                    'userCompany' => $user->getAccount()->getCompany()
                ]
            ]
        ], $this->mercureSubscriberKey);
    }
}
