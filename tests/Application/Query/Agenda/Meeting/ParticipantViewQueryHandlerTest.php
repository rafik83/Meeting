<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Agenda\Meeting;

use Proximum\Vimeet\Application\Query\Agenda\Meeting\ParticipantViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\Meeting\ParticipantViewQueryHandler;
use Proximum\Vimeet\Application\Query\Participant\CardViewQuery;
use Proximum\Vimeet\Application\Query\Participant\CardViewQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\Meeting\ParticipantView;
use Proximum\Vimeet\Application\View\Participant\CardView;
use Proximum\Vimeet\Domain\Model\Rule;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Rule\Applyer;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class ParticipantViewQueryHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event       = EventFactory::createEvent();
        $type        = new Type($event);
        $sheet       = SheetFactory::create($event, null, null, $type);
        $participant = ParticipantFactory::create($sheet);
        $cardView    = new CardView(1, false, 'firstName', 'lastName', 'position', 'avatar', false, 1);
        $rules       = [new Rule($event, $type, $type, [], 1)];

        $cardViewQueryHandler = $this->prophesize(CardViewQueryHandler::class);
        $cardViewQueryHandler
            ->handle(new CardViewQuery($participant, 'fr', false))
            ->shouldBeCalled()
            ->willReturn($cardView);
        $ruleApplyer          = $this->prophesize(Applyer::class);
        $ruleApplyer->applyRuleForParticipantCard($cardView, $rules)->shouldBeCalled();

        $participantHandler = new ParticipantViewQueryHandler($ruleApplyer->reveal(), $cardViewQueryHandler->reveal());
        $result = $participantHandler->handle(new ParticipantViewQuery($participant, $rules, 'fr'));

        $expected = new ParticipantView($cardView);
        $this->assertEquals($expected, $result);
    }
}
