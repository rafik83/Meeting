<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Tests\Domain\Model;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;

class SheetTest extends \PHPUnit_Framework_TestCase
{
    public function testHasUser()
    {
        $event = new Event();
        $type  = new Type($event);
        $sheet = new Sheet($event, $type, [], [], new \DateTime());

        $user1 = new User('user1@test.com', '', '', 'fr');
        $user2 = new User('user2@test.com', '', '', 'fr');
        $user3 = new User('user3@test.com', '', '', 'fr');

        $sheet->addParticpant(new Participant($sheet, $user1, [], true, true));
        $sheet->addParticpant(new Participant($sheet, $user2, [], false, true));

        $this->assertTrue($sheet->hasUser($user1));
        $this->assertTrue($sheet->hasUser($user2));
        $this->assertFalse($sheet->hasUser($user3));
    }

    public function testHasParticipant()
    {
        $event = new Event();
        $type  = new Type($event);
        $sheet = new Sheet($event, $type, [], [], new \DateTime());

        $participant1 = new Participant($sheet, new User('user1@test.com', '', '', 'fr'), [], true, true);
        $participant2 = new Participant($sheet, new User('user2@test.com', '', '', 'fr'), [], true, true);
        $participant3 = new Participant($sheet, new User('user3@test.com', '', '', 'fr'), [], true, true);


        $sheet->addParticpant($participant1);
        $sheet->addParticpant($participant2);

        $this->assertTrue($sheet->hasParticipant($participant1));
        $this->assertTrue($sheet->hasParticipant($participant2));
        $this->assertFalse($sheet->hasParticipant($participant3));
    }

    public function testGetUserParticipant()
    {
        $event = new Event();
        $type  = new Type($event);
        $sheet = new Sheet($event, $type, [], [], new \DateTime());

        $user1 = new User('user1@test.com', '', '', 'fr');
        $user2 = new User('user2@test.com', '', '', 'fr');
        $user3 = new User('user3@test.com', '', '', 'fr');
        $user4 = new User('user4@test.com', '', '', 'fr');

        $participant1 = new Participant($sheet, $user1, [], true, true);
        $participant2 = new Participant($sheet, $user2, [], true, true);
        $participant3 = new Participant($sheet, $user3, [], true, true);


        $sheet->addParticpant($participant1);
        $sheet->addParticpant($participant2);
        $sheet->addParticpant($participant3);

        $this->assertEquals($participant1, $sheet->getUserParticipant($user1));
        $this->assertEquals($participant2, $sheet->getUserParticipant($user2));
        $this->assertEquals($participant3, $sheet->getUserParticipant($user3));
        $this->assertNull($sheet->getUserParticipant($user4));
    }
}
