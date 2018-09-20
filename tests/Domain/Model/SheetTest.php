<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\Model;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Exception\Sheet\SheetException;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class SheetTest extends TestCase
{
    public function testHasUser()
    {
        $event = EventFactory::createEvent();
        $type  = new Type($event);
        $user1 = new User('user1@test.com', '', '', 'fr');
        $sheet = new Sheet($event, $type, [], $user1, new \DateTime());

        $user2 = new User('user2@test.com', '', '', 'fr');
        $user3 = new User('user3@test.com', '', '', 'fr');

        $reflection = new \ReflectionClass(User::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($user1, 1);
        $property->setValue($user2, 2);
        $property->setValue($user3, 3);
        $property->setAccessible(false);

        $sheet->addParticipant(new Participant($sheet, $user1, [], true));
        $sheet->addParticipant(new Participant($sheet, $user2, [], false));

        $this->assertTrue($sheet->hasUser($user1));
        $this->assertTrue($sheet->hasUser($user2));
        $this->assertFalse($sheet->hasUser($user3));
    }

    public function testHasParticipant()
    {
        $event = EventFactory::createEvent();
        $type  = new Type($event);
        $user  = new User('test@test.com', 'salt', 'password', 'fr');
        $sheet = new Sheet($event, $type, [], $user, new \DateTime());

        $participant1 = new Participant($sheet, new User('user1@test.com', '', '', 'fr'), [], true);
        $participant2 = new Participant($sheet, new User('user2@test.com', '', '', 'fr'), [], true);
        $participant3 = new Participant($sheet, new User('user3@test.com', '', '', 'fr'), [], true);

        $sheet->addParticipant($participant1);
        $sheet->addParticipant($participant2);

        $this->assertTrue($sheet->hasParticipant($participant1));
        $this->assertTrue($sheet->hasParticipant($participant2));
        $this->assertFalse($sheet->hasParticipant($participant3));
    }

    public function testGetUserParticipant()
    {
        $reflection = new \ReflectionClass(User::class);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);

        $event = EventFactory::createEvent();
        $type  = new Type($event);
        $user1 = new User('user1@test.com', '', '', 'fr');
        $property->setValue($user1, 1);
        $sheet = new Sheet($event, $type, [], $user1, new \DateTime());

        $user2 = new User('user2@test.com', '', '', 'fr');
        $property->setValue($user2, 2);

        $user3 = new User('user3@test.com', '', '', 'fr');
        $property->setValue($user3, 3);

        $user4 = new User('user4@test.com', '', '', 'fr');

        $participant1 = new Participant($sheet, $user1, [], true);
        $participant2 = new Participant($sheet, $user2, [], true);
        $participant3 = new Participant($sheet, $user3, [], true);

        $sheet->addParticipant($participant1);
        $sheet->addParticipant($participant2);
        $sheet->addParticipant($participant3);

        $this->assertEquals($participant1, $sheet->getUserParticipant($user1));
        $this->assertEquals($participant2, $sheet->getUserParticipant($user2));
        $this->assertEquals($participant3, $sheet->getUserParticipant($user3));
        $this->assertNull($sheet->getUserParticipant($user4));

        $property->setAccessible(false);
    }

    public function testAssignOrganizer()
    {
        $event    = EventFactory::createEvent();
        $type     = new Type($event);
        $user     = new User('test@test.com', 'salt', 'password', 'fr');
        $sheet    = new Sheet($event, $type, [], $user, new \DateTime());
        $dateTime = new \DateTime();

        $organizer = new Admin('test@test.com', '', '', 'fr', 'Test', 'Test', Admin::ROLE_ORGANIZER, $dateTime);

        $this->assertEquals($organizer, $sheet->assign($organizer)->getFollower());
    }

    public function testAssignOperator()
    {
        $event    = EventFactory::createEvent();
        $type     = new Type($event);
        $user     = new User('test@test.com', 'salt', 'password', 'fr');
        $sheet    = new Sheet($event, $type, [], $user, new \DateTime());
        $dateTime = new \DateTime();

        $operator = new Admin('test@test.com', '', '', 'fr', 'Test', 'Test', Admin::ROLE_OPERATOR, $dateTime);

        $this->assertEquals($operator, $sheet->assign($operator)->getFollower());
    }

    public function testAssignSuperAdmin()
    {
        $this->expectException(SheetException::class);

        $event    = EventFactory::createEvent();
        $type     = new Type($event);
        $user     = new User('test@test.com', 'salt', 'password', 'fr');
        $sheet    = new Sheet($event, $type, [], $user, new \DateTime());
        $dateTime = new \DateTime();

        $operator = new Admin('test@test.com', '', '', 'fr', 'Test', 'Test', Admin::ROLE_SUPER_ADMIN, $dateTime);

        $sheet->assign($operator);
    }
}
