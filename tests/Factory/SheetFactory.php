<?php

namespace Proximum\Vimeet\Tests\Factory;

use DateTime;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;

class SheetFactory
{
    /**
     * @param Event|null    $event
     * @param User|null     $user
     * @param DateTime|null $dateTime
     * @param Type|null     $type
     *
     * @return Sheet
     */
    public static function create(Event $event = null, User $user = null, DateTime $dateTime = null, Type $type = null)
    {
        $event = (null !== $event) ? $event : EventFactory::createEvent();

        $type     = null !== $type ? $type : new Type($event);
        $user     = (null !== $user) ? $user : new User('user@vimeet.com', 'salt', 'password', 'fr');
        $dateTime = (null !== $dateTime) ? $dateTime : new DateTime();

        $sheet = new Sheet(
            $event,
            $type,
            [],
            $user,
            $dateTime
        );

        return $sheet;
    }
}
