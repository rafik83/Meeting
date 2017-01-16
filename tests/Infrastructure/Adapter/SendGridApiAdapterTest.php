<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

use Prophecy\Argument;
use Proximum\Vimeet\Domain\Messaging\SendGridApiClient;
use Proximum\Vimeet\Domain\Model\Messaging\Message;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Infrastructure\Adapter\SendGridApiAdapter;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use SendGrid\Mail;
use SendGrid\Response;

class SendGridApiAdapterTest extends \PHPUnit_Framework_TestCase
{
    public function testSend()
    {
        $event     = EventFactory::createEvent();
        $receiver  = new User('user@vimeet.com', 'salt', 'password', 'fr');
        $createdAt = new \DateTime();
        $message   = new Message($event, $createdAt, 'test', 'test subject', 'test content');

        $template = $this->prophesize(\Twig_TemplateInterface::class);
        $template->render(['mail' => $message])->shouldBeCalled()->willReturn('test content');

        $twig = $this->prophesize(\Twig_Environment::class);
        $twig->load($message->getTemplate())->shouldBeCalled()->willReturn($template);

        $client = $this->prophesize(SendGridApiClient::class);
        $client->send(Argument::type(Mail::class))->shouldBeCalledTimes(1)->willReturn(new Response(202, false, []));

        $adapter = new SendGridApiAdapter($client->reveal(), $twig->reveal());
        $adapter->send($message, 'no-reply@vimeet.com', [$receiver]);
    }
}
