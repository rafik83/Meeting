<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\tests\Application\Command\Messaging\Message;

use Proximum\Vimeet\Application\Command\Messaging\Message\Update;
use Proximum\Vimeet\Application\Command\Messaging\Message\UpdateHandler;
use Proximum\Vimeet\Domain\Model\Messaging\Message;
use Proximum\Vimeet\Domain\Repository\Messaging\MessageRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use PHPUnit\Framework\TestCase;

class UpdateHandlerTest extends TestCase
{
    public function testHandle()
    {
        $message          = new Message(EventFactory::createEvent(), new \DateTime(), 'pro', 'xi', 'mum');
        $command          = new Update($message);
        $command->name    = 'message_name_updated';
        $locale           = 'fr';
        $dateTime         = new \DateTime();

        $command->translations = [
            'fr' => [
                'subject' => 'french subject',
                'content' => 'french content',
                'locale'  => 'fr',
            ],
            'en' => [
                'subject' => 'english tea time',
                'content' => 'english content',
                'locale'  => 'en',
            ],
        ];

        $messageRepository = $this->prophesize(MessageRepositoryInterface::class);
        $messageRepository->set($message)->shouldBeCalled();
        (new UpdateHandler($messageRepository->reveal(), $dateTime))->handle($command);

        $this->assertSame('message_name_updated', $message->getName());
        $this->assertSame('french subject', $message->getSubject($locale));
        $this->assertSame('french content', $message->getContent($locale));
    }
}
