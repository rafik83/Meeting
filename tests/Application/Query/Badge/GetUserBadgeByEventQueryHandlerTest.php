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
use Proximum\Vimeet\Application\Query\Badge\GetUserBadgeByEventQuery;
use Proximum\Vimeet\Application\Query\Badge\GetUserBadgeByEventQueryHandler;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Badge\QRCode\QRCodeIdentifierQuery;
use Proximum\Vimeet\Application\Query\Badge\QRCode\QRCodeIdentifierQueryHandler;
use Proximum\Vimeet\Application\Query\Badge\UserBadgeByEventView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class GetUserBadgeByEventQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);

        $qrCodeIdentifierQueryHandler = $this->prophesize(QRCodeIdentifierQueryHandler::class);
        $qrCodeIdentifierQueryHandler
            ->handle(new QRCodeIdentifierQuery($event->reveal(), $user->reveal()))
            ->shouldBeCalled()
            ->willReturn('0000133700042')
        ;

        $qrCodeGenerator = $this->prophesize(QRCodeGeneratorInterface::class);
        $qrCodeGenerator
            ->generateBase64Image('0000133700042')
            ->shouldBeCalled()
            ->willReturn('data:qrCodeImageBase64')
        ;

        $expectedUserBadgeByEventView = new UserBadgeByEventView(
            'sheet title',
            'first name',
            'last name',
            'user position',
            'participation type',
            '0000133700042',
            'data:qrCodeImageBase64'
        );

        $getUserBadgeByEventQueryHandler = new GetUserBadgeByEventQueryHandler(
            $qrCodeIdentifierQueryHandler->reveal(),
            $qrCodeGenerator->reveal()
        );
        $result = $getUserBadgeByEventQueryHandler->handle(new GetUserBadgeByEventQuery($event->reveal(), $user->reveal()));

        $this->assertEquals($expectedUserBadgeByEventView, $result);
    }
}
