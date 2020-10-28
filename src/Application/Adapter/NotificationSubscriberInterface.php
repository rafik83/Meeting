<?php

namespace Proximum\Vimeet\Application\Adapter;


use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

interface NotificationSubscriberInterface
{
    public function getUrl(): string;

    public function getHappeningSubscriberKey(Happening $happening, User $user, array $types): string;

    public function getNetworkingSubscriberKey(Sheet $sheet, User $user, $types): string;

    public function getNetworkingTopic(int $eventId): string;

    public function getUserTopic(int $eventId, int $userId): string;

    public function getCallVisioTopic(Event $event): string;

    public function getUserSubscriberKey(Sheet $sheet, User $user): string;
}
