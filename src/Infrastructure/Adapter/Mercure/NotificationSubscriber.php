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

    public function getNetworkingSubscriberKey(Sheet $sheet, User $user, $types): string
    {
        if (empty($types)) {
            throw new InvalidArgumentException('Types array cannot be empty');
        }

        return JWT::encode([
            'mercure' => [
                'subscriber' => array_map(function ($type) use ($sheet) {
                    return ['topic' => $this->getNetworkingTopic($sheet->getEvent()->getId(), $type)];
                }, $types),
                'payload' => $this->userPayloadBuilder->get($sheet, $user),
            ]
        ], $this->mercureSubscriberKey);
    }
}
