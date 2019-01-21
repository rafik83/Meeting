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
use GuzzleHttp\Exception\ServerException;
use Ovh\Api;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Exception\Messaging\SMS\FailToSendSMSException;
use Proximum\Vimeet\Application\Exception\Messaging\SMS\InvalidReceiverException;
use Proximum\Vimeet\Domain\Messaging\SMS\SMS;
use Proximum\Vimeet\Infrastructure\Adapter\SMS\TwilioClient;
use Proximum\Vimeet\Infrastructure\Adapter\SMSSenderAdapter;

class SMSSenderAdapterTest extends TestCase
{
    const SENDER  = 'senderName';
    const SERVICE = 'serviceName';

    /** @var ObjectProphecy */
    private $twilioClient;

    public function setUp()
    {
        $this->twilioClient = $this->prophesize(TwilioClient::class);
    }

    public function testSendClientException()
    {
        $this->expectException(FailToSendSMSException::class);

        $sms = new SMS('+33102030405', 'message content');

        // Mock
        $ovh = $this->prophesize(Api::class);
        $ovh->post(
                '/sms/serviceName/jobs',
                [
                    'message'   => 'message content',
                    'receivers' => ['+33102030405'],
                    'sender'    => 'senderName',
                ]
            )
            ->shouldBeCalled()
            ->willThrow(ClientException::class);

        // Adapter
        $adapter = new SMSSenderAdapter($ovh->reveal(), self::SERVICE, self::SENDER, $this->twilioClient->reveal());
        $adapter->send($sms);
    }

    public function testSendServerException()
    {
        $this->expectException(FailToSendSMSException::class);

        $sms = new SMS('+33102030405', 'message content');

        // Mock
        $ovh = $this->prophesize(Api::class);
        $ovh->post(
            '/sms/serviceName/jobs',
            [
                'message'   => 'message content',
                'receivers' => ['+33102030405'],
                'sender'    => 'senderName',
            ]
        )
            ->shouldBeCalled()
            ->willThrow(ServerException::class);

        // Adapter
        $adapter = new SMSSenderAdapter($ovh->reveal(), self::SERVICE, self::SENDER, $this->twilioClient->reveal());
        $adapter->send($sms);
    }

    public function testSendInvalidReceiver()
    {
        $this->expectException(InvalidReceiverException::class);

        $sms = new SMS('+33102030405', 'message content');

        // Mock
        $ovh = $this->prophesize(Api::class);
        $ovh->post(
                '/sms/serviceName/jobs',
                [
                    'message'   => 'message content',
                    'receivers' => ['+33102030405'],
                    'sender'    => 'senderName',
                ]
            )
            ->shouldBeCalled()
            ->willReturn(['invalidReceivers' => ['+33102030405']]);

        // Adapter
        $adapter = new SMSSenderAdapter($ovh->reveal(), self::SERVICE, self::SENDER, $this->twilioClient->reveal());
        $adapter->send($sms);
    }

    public function testSend()
    {
        $sms = new SMS('+33102030405', 'message content');

        // Mock
        $ovh = $this->prophesize(Api::class);
        $ovh->post(
            '/sms/serviceName/jobs',
            [
                'message'   => 'message content',
                'receivers' => ['+33102030405'],
                'sender'    => 'senderName',
            ]
        )
            ->shouldBeCalled()
            ->willReturn([]);

        // Adapter
        $adapter = new SMSSenderAdapter($ovh->reveal(), self::SERVICE, self::SENDER, $this->twilioClient->reveal());
        $adapter->send($sms);
    }

    public function testSendNotAdvertising()
    {
        $sms = new SMS('+33102030405', 'message content', false);

        // Mock
        $ovh = $this->prophesize(Api::class);
        $ovh->post(
            '/sms/serviceName/jobs',
            [
                'message'      => 'message content',
                'noStopClause' => true,
                'receivers'    => ['+33102030405'],
                'sender'       => 'senderName',
            ]
        )
            ->shouldBeCalled()
            ->willReturn([]);

        // Adapter
        $adapter = new SMSSenderAdapter($ovh->reveal(), self::SERVICE, self::SENDER, $this->twilioClient->reveal());
        $adapter->send($sms);
    }
}
