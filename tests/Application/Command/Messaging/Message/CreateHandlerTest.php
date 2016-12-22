<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\tests\Application\Command\Messaging\Message;

use Proximum\Vimeet\Application\Command\Messaging\Message\Create;
use Proximum\Vimeet\Application\Command\Messaging\Message\CreateHandler;
use Proximum\Vimeet\Domain\Model\Messaging\Message;
use Proximum\Vimeet\Domain\Repository\Messaging\MessageRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class CreateHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event            = EventFactory::createEvent();
        $command          = new Create($event);
        $command->name    = 'pro';
        $command->subject = 'xi';
        $command->content = 'mum';
        $date             = new \DateTime();

        $messageRepository = $this->prophesize(MessageRepositoryInterface::class);
        $messageRepository->add(new Message($event, $date, 'pro', 'xi', 'mum'))->shouldBeCalled();

        (new CreateHandler($messageRepository->reveal(), $date))->handle($command);
    }
}
