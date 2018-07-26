<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Meeting\Admin\ListMeeting;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Meeting\Admin\ListMeeting\ParticipantViewQuery;
use Proximum\Vimeet\Application\Query\Meeting\Admin\ListMeeting\ParticipantViewQueryHandler;
use Proximum\Vimeet\Application\View\Meeting\Admin\ListMeeting\ParticipantView;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class ParticipantViewQueryHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $participant = $this->prophesize(Participant::class);
        $participant->getId()->shouldBeCalled()->willReturn(12);
        $locale = 'fr';

        $participantInfoGuesser = $this->prophesize(ParticipantInfoGuesser::class);
        $participantInfoGuesser->guessParticipantCompleteName($participant->reveal(), $locale)
            ->shouldBeCalled()
            ->willReturn('Jean Paul')
        ;

        $query = new ParticipantViewQuery($participant->reveal(), $locale);

        $handler = new ParticipantViewQueryHandler(
            $participantInfoGuesser->reveal()
        );

        $result = $handler->handle($query);

        $expected = new ParticipantView(12, 'Jean Paul');

        $this->assertEquals($expected, $result);
    }
}
