<?php

namespace Proximum\Vimeet\Infrastructure\Adapter\Mercure;

use Firebase\JWT\JWT;
use InvalidArgumentException;
use Proximum\Vimeet\Application\Adapter\NotificationSubscriberInterface;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class NotificationSubscriber extends AbstractNotification implements NotificationSubscriberInterface
{
    /** @var string */
    private $mercureHubUrl;

    /** @var string */
    private $mercureSubscriberKey;

    /** @var UserPayloadBuilder */
    private $userPayloadBuilder;

    public function __construct(string $mercureHubUrl, string $mercureSubscriberKey, UserPayloadBuilder $userPayloadBuilder)
    {
        $this->mercureHubUrl = $mercureHubUrl;
        $this->mercureSubscriberKey = $mercureSubscriberKey;
        $this->userPayloadBuilder = $userPayloadBuilder;
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
                'payload' => ['userId' => $user->getId()],
            ]
        ], $this->mercureSubscriberKey);
    }

    public function getNetworkingSubscriberKey(Sheet $sheet, User $user, $types): string
    {
        if (empty($types)) {
            throw new InvalidArgumentException('Types array cannot be empty');
        }

        return JWT::encode([
            'mercure' => [
                'subscriber' => array_map(function ($type) use ($sheet) {
                    return ['topic' => $this->getNetworkingTopic($sheet->getEvent()->getId())];
                }, $types),
                'payload' => $this->userPayloadBuilder->get($sheet, $user),
            ]
        ], $this->mercureSubscriberKey);
    }

    /**
     * Generate JWT token for all topics a user can be interested in
     */
    public function getUserSubscriberKey(Sheet $sheet, User $user): string
    {
        $topics[] = $this->getUserTopic($sheet->getEvent()->getId(), $user->getId());

        return JWT::encode([
            'mercure' => [
                'subscribe' => $topics,
                'payload' => $this->userPayloadBuilder->get($sheet, $user),
            ]
        ], $this->mercureSubscriberKey);
    }
}
