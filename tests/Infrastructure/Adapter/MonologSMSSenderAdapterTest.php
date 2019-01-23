<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Infrastructure\Adapter;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Messaging\SMS\SMS;
use Proximum\Vimeet\Infrastructure\Adapter\SMS\MonologSMSSenderAdapter;
use Psr\Log\LoggerInterface;

class MonologSMSSenderAdapterTest extends TestCase
{
    public function testSend()
    {
        $sms = $this->prophesize(SMS::class);
        $sms->getReceiver()->shouldBeCalled()->willReturn('+123456789');
        $sms->getMessage()->shouldBeCalled()->willReturn('This is the message');

        $monolog = $this->prophesize(LoggerInterface::class);
        $monolog
            ->info(sprintf('SMS sent to %s with message: %s', '+123456789', 'This is the message'))
            ->shouldBeCalled()
        ;

        $adapter = new MonologSMSSenderAdapter($monolog->reveal());
        $adapter->sendMessage($sms->reveal());
    }
}
