<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Meeting;

use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use PHPUnit\Framework\TestCase;

class RequestTransformableTest extends TestCase
{
    public function testOneToOneWithNoPreference()
    {
        $event     = EventFactory::createEvent();
        $locale    = 'fr';
        $user      = new User('user@gmail.com', '', '', $locale);
        $datetime  = new \DateTime();
        $fromSheet = SheetFactory::create($event);
        $toSheet   = SheetFactory::create($event);

        $request = new Meeting\Request($fromSheet, [], $toSheet, [], $datetime, $user, $event);

        $this->assertEquals(false, Meeting\TransformableRequest::isTransformable($request));
    }

    public function testOneToManyWithNoPreference()
    {
        $event     = EventFactory::createEvent();
        $locale    = 'fr';
        $user      = new User('user@gmail.com', '', '', $locale);
        $datetime  = new \DateTime();
        $fromSheet = SheetFactory::create($event);
        $toSheet   = SheetFactory::create($event);

        $toSheet->addParticipant(new Participant($toSheet, $user, [], true));
        $toSheet->addParticipant(new Participant($toSheet, $user, [], true));

        $request = new Meeting\Request($fromSheet, [], $toSheet, [], $datetime, $user, $event);
        $this->assertEquals(false, Meeting\TransformableRequest::isTransformable($request));
    }

    public function testOneToManyWithPreference()
    {
        $event     = EventFactory::createEvent();
        $locale    = 'fr';
        $user      = new User('user@gmail.com', '', '', $locale);
        $datetime  = new \DateTime();
        $fromSheet = SheetFactory::create($event);
        $toSheet   = SheetFactory::create($event);

        $participantOne = new Participant($toSheet, $user, [], true);
        $participantTwo = new Participant($toSheet, $user, [], true);

        $toSheet->addParticipant($participantOne);
        $toSheet->addParticipant($participantTwo);

        $request = new Meeting\Request($fromSheet, [], $toSheet, [$participantTwo], $datetime, $user, $event);
        $this->assertEquals(true, Meeting\TransformableRequest::isTransformable($request));

        $request = new Meeting\Request($fromSheet, [], $toSheet, [$participantTwo, $participantOne], $datetime, $user, $event);
        $this->assertEquals(true, Meeting\TransformableRequest::isTransformable($request));
    }

    public function testManyToManyWithNoPreference()
    {
        $event     = EventFactory::createEvent();
        $locale    = 'fr';
        $user      = new User('user@gmail.com', '', '', $locale);
        $datetime  = new \DateTime();
        $fromSheet = SheetFactory::create($event);
        $toSheet   = SheetFactory::create($event);

        $participantOne = new Participant($toSheet, $user, [], true);
        $participantTwo = new Participant($toSheet, $user, [], true);
        $participantThree = new Participant($toSheet, $user, [], true);
        $participantFour = new Participant($toSheet, $user, [], true);

        $fromSheet->addParticipant($participantOne);
        $fromSheet->addParticipant($participantTwo);

        $toSheet->addParticipant($participantThree);
        $toSheet->addParticipant($participantFour);

        $request = new Meeting\Request($fromSheet, [$participantOne, $participantTwo], $toSheet, [], $datetime, $user, $event);
        $this->assertEquals(false, Meeting\TransformableRequest::isTransformable($request));
    }
}
