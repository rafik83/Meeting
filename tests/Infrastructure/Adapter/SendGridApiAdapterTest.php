<?php

use Proximum\Vimeet\Domain\Model\Messaging\Message;
use Proximum\Vimeet\Domain\Messaging\SendGridApiClient;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;
use Proximum\Vimeet\Infrastructure\Adapter\TranslatorAdapter;
use Proximum\Vimeet\Infrastructure\Adapter\SendGridApiAdapter;
use Symfony\Component\Translation\TranslatorInterface;
use Prophecy\Argument;
use SendGrid\Mail;

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

class SendGridApiAdapterTest extends \PHPUnt_Framework_TestCase
{
    public function testSend()
    {
        $event = EventFactory::createEvent();
        $receiver = new User('user@vimeet.com', 'salt', 'password', 'fr');
        $createdAt = new \DateTime();
        $message = new Message($event, $createdAt, 'test', 'test subject', 'test content');

        $client = $this->prophesize(SendGridApiClient::class);
        $client->send(Argument::type(Mail::class))->shouldBeCalledTimes(1);

        $adapter = new SendGridApiAdapter(
            $client->reveal(),
            new \Twig_Environment(),
            new TranslatorAdapter($this->prophesize(TranslatorInterface::class)->reveal())
        );

        $adapter->send($message, 'no-reply@vimeet.com', [$receiver]);
    }
}
