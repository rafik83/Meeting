<?php

namespace Proximum\Vimeet\Tests\Infrastructure\Adapter;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Messaging\SMS\SMS;
use Proximum\Vimeet\Infrastructure\Adapter\SMS\MonologProvider;
use Proximum\Vimeet\Infrastructure\Adapter\SMS\SMSProviderGuesser;
use Proximum\Vimeet\Infrastructure\Adapter\SMSSenderAdapter;

class SMSSenderAdapterTest extends TestCase
{
    public function testSend(): void
    {
        $sms = new SMS('+123456789', 'This is a test message', true);

        $smsProviderGuesser = $this->prophesize(SMSProviderGuesser::class);
        $provider = $this->prophesize(MonologProvider::class);
        $smsProviderGuesser->guessProvider($sms)->shouldBeCalled()->willReturn($provider->reveal());
        $provider->sendMessage($sms)->shouldBeCalled();

        $smsSender = new SMSSenderAdapter($smsProviderGuesser->reveal());
        $smsSender->send($sms);
    }
}
