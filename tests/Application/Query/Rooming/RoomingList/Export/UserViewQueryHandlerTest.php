<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Rooming\RoomingList\Export;

use Proximum\Vimeet\Application\Query\Rooming\RoomingList\Export\UserViewQuery;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\Export\UserViewQueryHandler;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\View\Rooming\ExportList\UserSheetView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type as ExtraDataType;

class UserViewQueryHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $user = $this->prophesize(User::class);
        $account = $this->prophesize(User\Account::class);
        $event = $this->prophesize(Event::class);
        $locale = 'fr';
        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);
        $sheet3 = $this->prophesize(Sheet::class);
        $spot = $this->prophesize(Spot::class);
        $type1 = $this->prophesize(Type::class);
        $type2 = $this->prophesize(Type::class);

        $commentExtraData = $this->prophesize(User\Event\ExtraData::class);
        $testingExtraData = $this->prophesize(User\Event\ExtraData::class);

        $commentExtraData->getValue()->shouldBeCalled()->willReturn('This is a comment');
        $testingExtraData->getValue()->shouldBeCalled()->willReturn('This is a testing');

        $sheet1->getId()->shouldBeCalled()->willReturn(1);
        $sheet2->getId()->shouldBeCalled()->willReturn(2);
        $sheet3->getId()->shouldBeCalled()->willReturn(3);

        $sheet1->getTitle()->shouldBeCalled()->willReturn('Aanera');
        $sheet2->getTitle()->shouldBeCalled()->willReturn('Bbnera');
        $sheet3->getTitle()->shouldBeCalled()->willReturn('Ccnera');

        $sheet1->getType()->shouldBeCalled()->willReturn($type1->reveal());
        $sheet2->getType()->shouldBeCalled()->willReturn($type2->reveal());
        $sheet3->getType()->shouldBeCalled()->willReturn($type1->reveal());

        $sheet1->getTypeTitle('fr')->shouldBeCalled()->willReturn('Exposant');
        $sheet2->getTypeTitle('fr')->shouldBeCalled()->willReturn('Visiteur');

        $type1->getId()->shouldBeCalled()->willReturn(11);
        $type2->getId()->shouldBeCalled()->willReturn(12);

        $sheet1->getSpot()->shouldBeCalled()->willReturn(null);
        $sheet2->getSpot()->shouldBeCalled()->willReturn(null);
        $sheet3->getSpot()->shouldBeCalled()->willReturn($spot->reveal());

        $spot->getReference()->shouldBeCalled()->willReturn('A123');

        $user->getId()->shouldBeCalled()->willReturn(1);
        $user->getAccount()->shouldBeCalled()->willReturn($account->reveal());

        $account->getGender()->shouldBeCalled()->willReturn('man');
        $account->getFirstName()->shouldBeCalled()->willReturn('Jean');
        $account->getLastName()->shouldBeCalled()->willReturn('Paul');

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);

        $sheetRepository->getSheetsByUserAndEvent($user->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn([$sheet1->reveal(), $sheet2->reveal(), $sheet3->reveal()])
        ;

        $extraDataRepository
            ->getExtraDataForEventNameAndUser($event->reveal(), ExtraDataType::ROOMING_COMMENT, $user->reveal())
            ->shouldBeCalled()
            ->willReturn($commentExtraData->reveal())
        ;

        $extraDataRepository
            ->getExtraDataForEventNameAndUser($event->reveal(), ExtraDataType::ROOMING_TESTING, $user->reveal())
            ->shouldBeCalled()
            ->willReturn($testingExtraData->reveal())
        ;

        $query = new UserViewQuery($event->reveal(), $user->reveal(), $locale);
        $handler = new UserViewQueryHandler(
            $sheetRepository->reveal(),
            $extraDataRepository->reveal()
        );

        $result = $handler->handle($query);

        $expected = new UserSheetView(
            1,
            'man',
            'Jean',
            'Paul',
            '1,2,3',
            'Aanera,Bbnera,Ccnera',
            'Exposant,Visiteur',
            'A123',
            'This is a comment',
            'This is a testing'
        );

        $this->assertEquals($expected, $result);
    }
}
