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
use Proximum\Vimeet\Domain\Model\Package;
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
        $package1 = $this->prophesize(Package::class);
        $package2 = $this->prophesize(Package::class);
        $package3 = $this->prophesize(Package::class);

        $commentExtraData = $this->prophesize(User\Event\ExtraData::class);
        $tastingExtraData = $this->prophesize(User\Event\ExtraData::class);

        $commentExtraData->getValue()->shouldBeCalled()->willReturn('This is a comment');
        $tastingExtraData->getValue()->shouldBeCalled()->willReturn('This is a tasting');

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

        $sheet1->getFollowerName()->shouldBeCalled()->willReturn('Al Pacino');
        $sheet2->getFollowerName()->shouldBeCalled()->willReturn('Robert DeNiro');
        $sheet3->getFollowerName()->shouldBeCalled()->willReturn('Joe Pesci');

        $package1->getId()->shouldBeCalled()->willReturn(78);
        $package2->getId()->shouldBeCalled()->willReturn(65);
        $package3->getId()->shouldBeCalled()->willReturn(19);

        $package1->getTitle()->shouldBeCalled()->willReturn('Package Cosa Nostra');
        $package2->getTitle()->shouldBeCalled()->willReturn('Package Camorra');
        $package3->getTitle()->shouldBeCalled()->willReturn('Package Stidda');

        $sheet1->getPackage()->shouldBeCalled()->willReturn($package1->reveal());
        $sheet2->getPackage()->shouldBeCalled()->willReturn($package2->reveal());
        $sheet3->getPackage()->shouldBeCalled()->willReturn($package3->reveal());

        $spot->getReference()->shouldBeCalled()->willReturn('A123');

        $user->getId()->shouldBeCalled()->willReturn(1);
        $user->getEmail()->shouldBeCalled()->willReturn('test@test.com');
        $user->getAccount()->shouldBeCalled()->willReturn($account->reveal());

        $account->getGender()->shouldBeCalled()->willReturn('man');
        $account->getFirstName()->shouldBeCalled()->willReturn('Jean');
        $account->getLastName()->shouldBeCalled()->willReturn('Paul');
        $account->getMobile()->shouldBeCalled()->willReturn('0000000001');

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
            ->getExtraDataForEventNameAndUser($event->reveal(), ExtraDataType::ROOMING_TASTING, $user->reveal())
            ->shouldBeCalled()
            ->willReturn($tastingExtraData->reveal())
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
            'test@test.com',
            '0000000001',
            '1,2,3',
            'Aanera,Bbnera,Ccnera',
            'Al Pacino,Robert DeNiro,Joe Pesci',
            'Package Cosa Nostra,Package Camorra,Package Stidda',
            'Exposant,Visiteur',
            'A123',
            'This is a comment',
            'This is a tasting'
        );

        $this->assertEquals($expected, $result);
    }
}
