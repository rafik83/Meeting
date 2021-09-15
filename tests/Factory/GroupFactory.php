<?php

namespace Proximum\Vimeet\Tests\Factory;

use DateTime;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Proximum\Vimeet\Domain\Model\User;

class GroupFactory
{
    /**
     * @param Event         $event
     * @param User|null     $user
     * @param DateTime|null $dateTime
     * @param string|null   $title
     * @param bool          $sheetTitleForced
     *
     * @return Group
     * @throws \Exception
     */
    public static function createGroup(
        Event $event,
        User $user = null,
        DateTime $dateTime = null,
        $title = null,
        bool $sheetTitleForced = false
    ): Group {
        $user     = (null !== $user) ? $user : UserFactory::create();
        $dateTime = (null !== $dateTime) ? $dateTime : new DateTime();
        $title    = (null !== $title) ? $title : 'GroupTitle';

        return new Group($event, $user, $title, $sheetTitleForced, $dateTime);
    }
}
