<?php

namespace Proximum\Vimeet\Tests\Infrastructure\Adapter\SMS;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Ovh\Api;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Exception\Messaging\SMS\FailToSendSMSException;
use Proximum\Vimeet\Application\Exception\Messaging\SMS\InvalidReceiverException;
use Proximum\Vimeet\Domain\Messaging\SMS\SMS;
use Proximum\Vimeet\Infrastructure\Adapter\SMS\OvhProvider;

class OvhProviderTest extends TestCase
{
    private const SENDER  = 'senderName';
    private const SERVICE = 'serviceName';

    /** @var ObjectProphecy */
    private $ovh;

    public function setUp()
    {
        $this->ovh = $this->prophesize(Api::class);
    }

    public function testSendClientException(): void
    {
        $this->expectException(FailToSendSMSException::class);
        $sms = new SMS('+33102030405', 'message content');

        // Mock
        $this->ovh->post(
            '/sms/serviceName/jobs',
            [
                'message' => 'message content',
                'receivers' => ['+33102030405'],
                'sender' => 'senderName',
                'noStopClause' => true,
            ]
        )
            ->shouldBeCalled()
            ->willThrow(ClientException::class);

        // Adapter
        $adapter = new OvhProvider($this->ovh->reveal(), self::SERVICE, self::SENDER);
        $adapter->sendMessage($sms);
    }

    public function testSendServerException(): void
    {
        $this->expectException(FailToSendSMSException::class);
        $sms = new SMS('+33102030405', 'message content');

        // Mock
        $this->ovh->post(
            '/sms/serviceName/jobs',
            [
                'message' => 'message content',
                'receivers' => ['+33102030405'],
                'sender' => 'senderName',
                'noStopClause' => true,
            ]
        )
            ->shouldBeCalled()
            ->willThrow(ServerException::class)
        ;

        // Adapter
        $adapter = new OvhProvider($this->ovh->reveal(), self::SERVICE, self::SENDER);
        $adapter->sendMessage($sms);
    }

    public function testSendInvalidReceiver(): void
    {
        $this->expectException(InvalidReceiverException::class);
        $sms = new SMS('+33102030405', 'message content');

        // Mock
        $this->ovh->post(
            '/sms/serviceName/jobs',
            [
                'message' => 'message content',
                'receivers' => ['+33102030405'],
                'sender' => 'senderName',
                'noStopClause' => true,
            ]
        )
            ->shouldBeCalled()
            ->willReturn(['invalidReceivers' => ['+33102030405']]);

        // Adapter
        $adapter = new OvhProvider($this->ovh->reveal(), self::SERVICE, self::SENDER);
        $adapter->sendMessage($sms);
    }

    public function testSend(): void
    {
        $sms = new SMS('+33102030405', 'message content');

        // Mock
        $this->ovh->post(
            '/sms/serviceName/jobs',
            [
                'message' => 'message content',
                'receivers' => ['+33102030405'],
                'sender' => 'senderName',
                'noStopClause' => true,
            ]
        )
            ->shouldBeCalled()
            ->willReturn([]);

        // Adapter
        $adapter = new OvhProvider($this->ovh->reveal(), self::SERVICE, self::SENDER);
        $adapter->sendMessage($sms);
    }

    public function testCanSendNumber(): void
    {
        $smsUs = new SMS('+1102030405', 'message content');
        $smsFr = new SMS('+33123456789', 'message content');

        // Adapter
        $adapter = new OvhProvider($this->ovh->reveal(), self::SERVICE, self::SENDER);

        $this->assertFalse($adapter->canSend($smsUs));
        $this->assertTrue($adapter->canSend($smsFr));
    }

    public function testSendWithStopClause(): void
    {
        $sms = new SMS('+33102030405', 'message content', true);

        // Mock
        $this->ovh->post(
            '/sms/serviceName/jobs',
            [
                'message' => 'message content',
                'receivers' => ['+33102030405'],
                'sender' => 'senderName',
            ]
        )
            ->shouldBeCalled()
            ->willReturn([]);

        // Adapter
        $adapter = new OvhProvider($this->ovh->reveal(), self::SERVICE, self::SENDER);
        $adapter->sendMessage($sms);
    }
}
