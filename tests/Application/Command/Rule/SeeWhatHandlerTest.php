<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Rule;

use Proximum\Vimeet\Application\Command\Rule\SeeWhat;
use Proximum\Vimeet\Application\Command\Rule\SeeWhatHandler;
use Proximum\Vimeet\Domain\Model\Rule;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class SeeWhatHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        // Init
        $event   = EventFactory::createEvent();
        $seer    = new Type($event);
        $seable  = new Type($event);
        $rule    = new Rule($event, $seer, $seable, ['participant_position']);
        $seeWhat = new SeeWhat($rule);
        $seeWhat->seeWhat = ['participant_firstname', 'participant_lastname'];

        $expectedRule = new Rule($event, $seer, $seable, ['participant_firstname', 'participant_lastname']);

        // Mock
        $ruleRepository = $this->prophesize(RuleRepositoryInterface::class);
        $ruleRepository->update($expectedRule)->shouldBeCalled();

        // Handler
        $handler = new SeeWhatHandler($ruleRepository->reveal());
        $handler->handle($seeWhat);
    }
}
