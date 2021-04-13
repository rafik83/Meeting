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
     * @param DateTime|null $datetime
     * @param Type|null     $type
     *
     * @return Sheet
     */
    public static function create(Event $event = null, User $user = null, DateTime $datetime = null, Type $type = null)
    {
        $event = (null !== $event) ? $event : EventFactory::createEvent();

        $type     = null !== $type ? $type : new Type($event);
        $user     = (null !== $user) ? $user : new User('user@vimeet.com', 'salt', 'password', 'fr');
        $datetime = (null !== $datetime) ? $datetime : new DateTime();

        $sheet = new Sheet(
            $event,
            $type,
            [],
            $user,
            $datetime
        );

        // set id value, done by Doctrine in real app
        $reflection = new \ReflectionClass($sheet);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($sheet, 123);

        return $sheet;
    }
}
