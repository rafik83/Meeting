<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Group\Request;

use Proximum\Vimeet\Application\Command\Planning\ParticipantInfoGuesserCache;
use Proximum\Vimeet\Application\Query\Group\Request\ParticipantViewQuery;
use Proximum\Vimeet\Application\Query\Group\Request\ParticipantViewQueryHandler;
use Proximum\Vimeet\Application\View\Group\Request\ParticipantView;
use Proximum\Vimeet\Domain\Model\Participant;

class ParticipantViewQueryHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $locale = 'fr';
        $participant = $this->prophesize(Participant::class);
        $participantInfoGuesser = $this->prophesize(ParticipantInfoGuesserCache::class);
        $participantInfoGuesser->guessParticipantCompleteName($participant->reveal(), $locale)->shouldBeCalled()->willReturn('complete Name');

        $handler = new ParticipantViewQueryHandler($participantInfoGuesser->reveal());

        $result = $handler->handle(new ParticipantViewQuery($participant->reveal(), $locale));

        $expected = new ParticipantView('complete Name');

        $this->assertEquals($expected, $result);
    }
}
