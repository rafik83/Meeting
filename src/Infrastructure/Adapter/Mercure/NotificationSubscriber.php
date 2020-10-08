<?php

namespace Proximum\Vimeet\Infrastructure\Adapter\Mercure;

use Firebase\JWT\JWT;
use InvalidArgumentException;
use Proximum\Vimeet\Application\Adapter\NotificationSubscriberInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\User;

class NotificationSubscriber extends AbstractNotification implements NotificationSubscriberInterface
{
    /** @var string */
    private $mercureHubUrl;

    /** @var string */
    private $mercureSubscriberKey;

    /** @var RouterInterface */
    private $routerAdapter;

    public function __construct(string $mercureHubUrl, string $mercureSubscriberKey, RouterInterface $routerAdapter)
    {
        $this->mercureHubUrl = $mercureHubUrl;
        $this->mercureSubscriberKey = $mercureSubscriberKey;
        $this->routerAdapter = $routerAdapter;
    }

    public function getUrl(): string
    {
        return $this->mercureHubUrl;
    }

    public function getHappeningSubscriberKey(Happening $happening, User $user, array $types): string
    {
        if (empty($types)) {
            throw new InvalidArgumentException('Types array cannot be empty');
        }

        return JWT::encode([
            'mercure' => [
                'subscribe' => array_map(function ($type) use ($happening) {
                    return $this->getHappeningTopic($happening->getId(), $type);
                }, $types),
                'payload' => $this->getUserPayload($user),
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
                'subscribe' => array_map(function ($type) use ($event) {
                    return $this->getNetworkingTopic($event->getId(), $type);
                }, $types),
                'payload' => $this->getUserPayload($user),
            ]
        ], $this->mercureSubscriberKey);
    }

    /**
     * Generate JWT token for all topics a user can be interested in
     * @param ChatSession[] $sessions
     */
    public function getEventSubscriberKey(Event $event, User $user): string
    {
        $topics[] = $this->getChatSessionTopic($user->getId());

        $topics[] = $this->getNotificationTopic($event->getId());

        return JWT::encode([
            'mercure' => [
                'subscribe' => $topics,
                'payload' => $this->getUserPayload($user),
            ]
        ], $this->mercureSubscriberKey);
    }

    private function getUserPayload(User $user): array
    {
        $avatar = $user->getAvatar();
        if ($avatar === null) {
            $avatar = $this->routerAdapter->generate('event_chat_avatar', ['name' => $user->getAccount()->getCompleteName()]);
        }

        return [
            'userId' => $user->getId(),
            'userLastName' => $user->getLastName(),
            'userFirstName' => $user->getFirstName(),
            'userPosition' => $user->getPosition(),
            'userAvatar' => $avatar,
            'userCompany' => $user->getAccount()->getCompany()
        ];
    }
}
