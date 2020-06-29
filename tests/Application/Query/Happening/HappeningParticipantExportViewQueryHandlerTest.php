<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Happening;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Sheet\SheetGuesser;
use Proximum\Vimeet\Application\Exception\Happening\EmptyHappeningParticipationException;
use Proximum\Vimeet\Application\Query\Happening\Admin\HappeningParticipantExportViewQuery;
use Proximum\Vimeet\Application\Query\Happening\Admin\HappeningParticipantExportViewQueryHandler;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Happening\QuestionRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;
use Proximum\Vimeet\Domain\Service\SheetsGroup\GroupNameResolver;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class HappeningParticipantExportViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event     = EventFactory::createEvent();
        $locale    = 'fr';
        $category  = new Happening\Category($event, 'picto', 1, '#000', '#fff');
        $begin     = new \DateTime();
        $end       = new \DateTime();

        $user      = $this->prophesize(User::class);
        $user->getId()->willReturn(1);
        $user->getFirstName()->willReturn('john');
        $user->getLastName()->willReturn('doh');
        $user->getEmail()->willReturn('johndoh@gmail.com');
        $user->getPosition()->willReturn('ceo');

        $sheet     = SheetFactory::create($event, $user->reveal());
        $happening = new Happening($event, $begin, $end, $category, []);

        $participation = new HappeningParticipation($happening, $user->reveal());
        $happening->setParticipations([$participation]);

        // Mock
        $happeningRepository = $this->prophesize(HappeningRepositoryInterface::class);
        $questionRepository  = $this->prophesize(QuestionRepositoryInterface::class);
        $groupNameResolver   = $this->prophesize(GroupNameResolver::class);
        $sheetGuesser        = $this->prophesize(SheetGuesser::class);

        $happeningRepository->findHappeningParticipant($event)->shouldBeCalled()->willReturn([$happening]);

        $questionRepository->findByHappeningAndSheet($happening, $sheet)->shouldBeCalled()->willReturn([]);

        $groupNameResolver->resolve($event, $user)->shouldBeCalled()->willReturn('');
        $sheetGuesser->getUserSheet($user, $event, $locale)->shouldBeCalled()->willReturn($sheet);

        $handler = new HappeningParticipantExportViewQueryHandler(
            $happeningRepository->reveal(),
            $questionRepository->reveal(),
            $groupNameResolver->reveal(),
            $sheetGuesser->reveal()
        );

        $happeningParticipantListView = $handler->handle(
            new HappeningParticipantExportViewQuery($event, $locale)
        );

        $this->assertCount(1, $happeningParticipantListView->getHappeningParticipantListView());
        $happeningParticipantView = $happeningParticipantListView->getHappeningParticipantListView()[0];

        $this->assertEquals('john', $happeningParticipantView->getFirstname());
        $this->assertEquals('doh', $happeningParticipantView->getLastname());
        $this->assertEquals('ceo', $happeningParticipantView->getPosition());
        $this->assertEquals('johndoh@gmail.com', $happeningParticipantView->getEmail());
    }

    public function testHandleEmptyParticipation()
    {
        $this->expectException(EmptyHappeningParticipationException::class);

        $event  = EventFactory::createEvent();
        $locale = 'fr';

        // Mock
        $happeningRepository = $this->prophesize(HappeningRepositoryInterface::class);
        $questionRepository  = $this->prophesize(QuestionRepositoryInterface::class);
        $groupNameResolver   = $this->prophesize(GroupNameResolver::class);
        $sheetGuesser        = $this->prophesize(SheetGuesser::class);

        $happeningRepository->findHappeningParticipant($event)->shouldBeCalled()->willReturn([]);

        $handler = new HappeningParticipantExportViewQueryHandler(
            $happeningRepository->reveal(),
            $questionRepository->reveal(),
            $groupNameResolver->reveal(),
            $sheetGuesser->reveal()
        );

        $this->expectException(EmptyHappeningParticipationException::class);

        $handler->handle(new HappeningParticipantExportViewQuery($event, $locale));
    }
}
