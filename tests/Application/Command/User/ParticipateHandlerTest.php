<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\User;

use Proximum\Vimeet\Application\Command\User\Participate;
use Proximum\Vimeet\Application\Command\User\ParticipateHandler;
use Proximum\Vimeet\Application\Components\Template\Validator;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class ParticipateHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $user  = new User('test@test.com', 'salt', 'password', 'fr');
        $event = new Event();
        $type  = new Type($event);
        $type->setParticipantTemplate([
            'foobar' => [
                'required' => true,
                'private'  => false,
            ]
        ]);
        $owner = true;

        $sheetRepository       = $this->prophesize(SheetRepositoryInterface::class);
        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);

        $expectedSheet = new Sheet($event, $type, [], [], new \DateTime());
        $sheetRepository->add($expectedSheet)->shouldBeCalled();

        $expectedSheetWithParticipant = new Sheet($event, $type, [], [], new \DateTime());
        $expectedParticipant          = new Participant($expectedSheetWithParticipant, $user, ['foobar' => 'barfoo'], $owner, true);
        $participantRepository->add($expectedParticipant)->shouldBeCalled();

        $validator = $this->prophesize(Validator::class);

        $handler = new ParticipateHandler($sheetRepository->reveal(), $participantRepository->reveal(), $validator->reveal(), new \DateTimeImmutable());
        $handler->handle(new Participate($user, $event, $type, ['foobar' => 'barfoo']));
    }
}
