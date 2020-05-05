<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Badge\QRCode;

use Proximum\Vimeet\Application\Query\Badge\QRCode\QRCodeIdentifierQuery;
use Proximum\Vimeet\Application\Query\Badge\QRCode\QRCodeIdentifierQueryHandler;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class QRCodeIdentifierQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $event->getId()->willReturn(1337);
        $user = $this->prophesize(User::class);
        $user->getId()->willReturn(42);

        $qrCodeIdentifierQueryHandler = new QRCodeIdentifierQueryHandler();
        $this->assertEquals(
            '000004201337',
            $qrCodeIdentifierQueryHandler->handle(new QRCodeIdentifierQuery($event->reveal(), $user->reveal()))
        );
    }
}
