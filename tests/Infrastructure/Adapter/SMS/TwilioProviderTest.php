<?php

namespace Proximum\Vimeet\Tests\Infrastructure\Adapter\SMS;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Domain\Messaging\SMS\SMS;
use Proximum\Vimeet\Infrastructure\Adapter\SMS\Client\TwilioClient;
use Proximum\Vimeet\Infrastructure\Adapter\SMS\TwilioProvider;
use Twilio\Rest\Api\V2010\Account\MessageList;
use Twilio\Rest\Client;

class TwilioProviderTest extends TestCase
{
    private const TWILIO_SENDER = '+123456789';

    /** @var ObjectProphecy */
    private $twilioClient;

    public function setUp()
    {
        $this->twilioClient = $this->prophesize(TwilioClient::class);
    }

    public function testCanSend(): void
    {
        $smsUs = new SMS('+1102030405', 'message content');
        $smsFr = new SMS('+33123456789', 'message content');

        $provider = new TwilioProvider($this->twilioClient->reveal(), self::TWILIO_SENDER);

        $this->assertTrue($provider->canSend($smsUs));
        $this->assertFalse($provider->canSend($smsFr));
    }

    public function testSendMessage(): void
    {
        $smsUs = new SMS('+1102030405', 'message content');

        $messageList = $this->prophesize(MessageList::class);
        $this->twilioClient->getMessageList()->shouldBeCalled()->willReturn($messageList);
        $messageList->create(
            '+1102030405',
            [
                'from' => self::TWILIO_SENDER,
                'body' => 'message content',
            ]
        )->shouldBeCalled();

        $provider = new TwilioProvider($this->twilioClient->reveal(), self::TWILIO_SENDER);
        $provider->sendMessage($smsUs);
    }
}
