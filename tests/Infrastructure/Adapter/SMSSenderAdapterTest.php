<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Infrastructure\Adapter;

use GuzzleHttp\Exception\ClientException;
use Ovh\Api;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Exception\Messaging\SMS\FailToSendSMSException;
use Proximum\Vimeet\Application\Exception\Messaging\SMS\NoSenderAvailableException;
use Proximum\Vimeet\Application\Exception\Messaging\SMS\NoServiceAvailableException;
use Proximum\Vimeet\Domain\Messaging\SMS\SMS;
use Proximum\Vimeet\Infrastructure\Adapter\SMSSenderAdapter;

class SMSSenderAdapterTest extends TestCase
{
    public function testSendNoService()
    {
        $this->expectException(NoServiceAvailableException::class);

        $sms = new SMS('+33102030405', 'message content');

        // Mock
        $ovh = $this->prophesize(Api::class);
        $ovh->get('/sms')->shouldBeCalled()->willReturn([]);

        $adapter = new SMSSenderAdapter($ovh->reveal());
        $adapter->send($sms);
    }

    public function testSendNoSender()
    {
        $this->expectException(NoSenderAvailableException::class);

        $sms = new SMS('+33102030405', 'message content');

        // Mock
        $ovh = $this->prophesize(Api::class);
        $ovh->get('/sms')->shouldBeCalled()->willReturn(['serviceName']);
        $ovh->get('/sms/serviceName/senders')->shouldBeCalled()->willReturn([]);

        $adapter = new SMSSenderAdapter($ovh->reveal());
        $adapter->send($sms);
    }

    public function testSendClientException()
    {
        $this->expectException(FailToSendSMSException::class);

        $sms = new SMS('+33102030405', 'message content');

        // Mock
        $ovh = $this->prophesize(Api::class);
        $ovh->get('/sms')->shouldBeCalled()->willReturn(['serviceName']);
        $ovh->get('/sms/serviceName/senders')->shouldBeCalled()->willReturn(['PROXIMUM']);
        $ovh->post(
                '/sms/serviceName/jobs',
                [
                    'message'   => 'message content',
                    'receivers' => ['+33102030405'],
                    'sender'    => 'PROXIMUM',
                ]
            )
            ->shouldBeCalled()
            ->willThrow(ClientException::class);

        $adapter = new SMSSenderAdapter($ovh->reveal());
        $adapter->send($sms);
    }

    public function testSendInvalidReceiver()
    {
        $this->expectException(FailToSendSMSException::class);

        $sms = new SMS('+33102030405', 'message content');

        // Mock
        $ovh = $this->prophesize(Api::class);
        $ovh->get('/sms')->shouldBeCalled()->willReturn(['serviceName']);
        $ovh->get('/sms/serviceName/senders')->shouldBeCalled()->willReturn(['PROXIMUM']);
        $ovh->post(
                '/sms/serviceName/jobs',
                [
                    'message'   => 'message content',
                    'receivers' => ['+33102030405'],
                    'sender'    => 'PROXIMUM',
                ]
            )
            ->shouldBeCalled()
            ->willReturn(['invalidReceivers' => ['+33102030405']]);

        $adapter = new SMSSenderAdapter($ovh->reveal());
        $adapter->send($sms);
    }

    public function testSend()
    {
        $sms = new SMS('+33102030405', 'message content');

        // Mock
        $ovh = $this->prophesize(Api::class);
        $ovh->get('/sms')->shouldBeCalled()->willReturn(['serviceName']);
        $ovh->get('/sms/serviceName/senders')->shouldBeCalled()->willReturn(['PROXIMUM']);
        $ovh->post(
            '/sms/serviceName/jobs',
            [
                'message'   => 'message content',
                'receivers' => ['+33102030405'],
                'sender'    => 'PROXIMUM',
            ]
        )
            ->shouldBeCalled()
            ->willReturn([]);

        $adapter = new SMSSenderAdapter($ovh->reveal());
        $adapter->send($sms);
    }
}
