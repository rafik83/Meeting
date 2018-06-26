<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Badge;

use Proximum\Vimeet\Application\Adapter\QRCodeGeneratorInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Components\User\UserInfoGuesser;
use Proximum\Vimeet\Application\Query\Badge\GetBadgeConfigurationByTypeQuery;
use Proximum\Vimeet\Application\Query\Badge\GetUserBadgeByEventQuery;
use Proximum\Vimeet\Application\Query\Badge\GetUserBadgeByEventQueryHandler;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Badge\QRCode\QRCodeIdentifierQuery;
use Proximum\Vimeet\Application\Query\Badge\UserBadgeByEventView;
use Proximum\Vimeet\Domain\Model\Badge;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Service\SheetsGroup\GroupNameResolver;
use Proximum\Vimeet\Domain\Service\Type\TypeNameResolver;

class GetUserBadgeByEventQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $badge = $this->prophesize(Badge::class);
        $badge->isActivated()->shouldBeCalled()->willReturn(true);
        $badge->isShowQRCode()->shouldBeCalled()->willReturn(true);
        $badge->isShowHeader()->shouldBeCalled()->willReturn(true);
        $badge->getHeader()->shouldBeCalled()->willReturn(null);
        $badge->isShowFirstName()->shouldBeCalled()->willReturn(true);
        $badge->isShowLastName()->shouldBeCalled()->willReturn(true);
        $badge->isShowPosition()->shouldBeCalled()->willReturn(false);
        $badge->isShowSheetTitle()->shouldBeCalled()->willReturn(true);

        $type = $this->prophesize(Type::class);
        $type->getTitle('en')->shouldBeCalled()->willReturn('Exhibitor');

        $sheet = $this->prophesize(Sheet::class);
        $sheets = [$sheet->reveal()];

        $event = $this->prophesize(Event::class);
        $event->getFallback()->shouldBeCalled()->willReturn('en');
        $event->getLocalizedMobileLogo('en')->shouldBeCalled()->willReturn('/path/to/header.png');

        $user = $this->prophesize(User::class);

        $queryBus = $this->prophesize(QueryBusInterface::class);
        $queryBus
            ->handle(new QRCodeIdentifierQuery($event->reveal(), $user->reveal()))
            ->shouldBeCalled()
            ->willReturn('0000133700042')
        ;
        $queryBus
            ->handle(new GetBadgeConfigurationByTypeQuery($type->reveal()))
            ->shouldBeCalled()
            ->willReturn($badge->reveal())
        ;

        $qrCodeGenerator = $this->prophesize(QRCodeGeneratorInterface::class);
        $qrCodeGenerator
            ->generateBase64Image('0000133700042')
            ->shouldBeCalled()
            ->willReturn('data:qrCodeImageBase64')
        ;

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetRepository
            ->getSheetsByUserAndEvent($user->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn($sheets)
        ;

        $groupNameResolver = $this->prophesize(GroupNameResolver::class);
        $groupNameResolver
            ->resolve($event->reveal(), $user->reveal(), $sheets)
            ->shouldBeCalled()
            ->willReturn('Taxi company')
        ;

        $typeNameResolver = $this->prophesize(TypeNameResolver::class);
        $typeNameResolver
            ->resolveTypeWithPreloadedSheets($sheets)
            ->shouldBeCalled()
            ->willReturn($type->reveal())
        ;

        $userInfoGuesser = $this->prophesize(UserInfoGuesser::class);
        $userInfoGuesser
            ->getUserInfoFromParticipant($user->reveal(), 'en', $sheets)
            ->shouldBeCalled()
            ->willReturn(['firstName' => 'Korben', 'lastName' => 'Dallas', 'position' => 'Taxi driver'])
        ;

        $expectedUserBadgeByEventView = new UserBadgeByEventView(
            'Taxi company',
            'Korben',
            'Dallas',
            null,
            'Exhibitor',
            '0000133700042',
            'data:qrCodeImageBase64',
            '/path/to/header.png'
        );

        $getUserBadgeByEventQueryHandler = new GetUserBadgeByEventQueryHandler(
            $queryBus->reveal(),
            $qrCodeGenerator->reveal(),
            $sheetRepository->reveal(),
            $groupNameResolver->reveal(),
            $typeNameResolver->reveal(),
            $userInfoGuesser->reveal()
        );
        $result = $getUserBadgeByEventQueryHandler->handle(new GetUserBadgeByEventQuery($event->reveal(), $user->reveal()));

        $this->assertEquals($expectedUserBadgeByEventView, $result);
    }
}
