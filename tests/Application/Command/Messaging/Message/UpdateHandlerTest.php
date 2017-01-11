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

class UpdateHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $message          = new Message(EventFactory::createEvent(), new \DateTime(), 'pro', 'xi', 'mum');
        $command          = new Update($message);
        $command->name    = 'vi';
        $command->subject = 'me';
        $command->content = 'et';

        $messageRepository = $this->prophesize(MessageRepositoryInterface::class);
        $messageRepository->set($message)->shouldBeCalled();

        (new UpdateHandler($messageRepository->reveal()))->handle($command);

        $this->assertSame('vi', $message->getName());
        $this->assertSame('me', $message->getSubject());
        $this->assertSame('et', $message->getContent());
    }
}
