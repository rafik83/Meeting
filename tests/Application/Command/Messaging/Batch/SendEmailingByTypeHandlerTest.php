<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Messaging\Batch;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Command\Messaging\Batch\Process;
use Proximum\Vimeet\Application\Command\Messaging\Batch\ProcessHandler;
use Proximum\Vimeet\Application\Command\Messaging\Batch\SendEmailingByType;
use Proximum\Vimeet\Application\Command\Messaging\Batch\SendEmailingByTypeHandler;
use Proximum\Vimeet\Application\Components\Messaging\MessageFactory;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Messaging\Message;
use Proximum\Vimeet\Domain\Model\Sheet;

class SendEmailingByTypeHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $sheet1;

    /** @var ObjectProphecy */
    private $sheet2;

    /** @var ObjectProphecy */
    private $sheet3;

    /** @var ObjectProphecy */
    private $message1;

    /** @var ObjectProphecy */
    private $message2;

    /** @var ObjectProphecy */
    private $messageFactory;

    /** @var ObjectProphecy */
    private $processHandler;

    /** @var SendEmailingByTypeHandler */
    private $sendEmailingByTypeHandler;

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);

        $this->sheet1 = $this->prophesize(Sheet::class);
        $this->sheet2 = $this->prophesize(Sheet::class);
        $this->sheet3 = $this->prophesize(Sheet::class);

        $this->message1 = $this->prophesize(Message::class);
        $this->message2 = $this->prophesize(Message::class);

        $this->messageFactory = $this->prophesize(MessageFactory::class);
        $this->processHandler = $this->prophesize(ProcessHandler::class);

        $this->sendEmailingByTypeHandler = new SendEmailingByTypeHandler(
            $this->messageFactory->reveal(),
            $this->processHandler->reveal()
        );
    }

    public function testHandle()
    {
        $this->messageFactory
            ->create($this->event->reveal(), 'sheet.validated', true)
            ->shouldBeCalled()
            ->willReturn($this->message1->reveal())
        ;

        $this->processHandler
            ->handle(
                new Process(
                    $this->message1->reveal(),
                    [$this->sheet1->reveal(), $this->sheet2->reveal(), $this->sheet3->reveal()]
                )
            )
            ->shouldBeCalled()
        ;

        $this->sendEmailingByTypeHandler->handle(
            new SendEmailingByType(
                $this->event->reveal(),
                'sheet.validated',
                [$this->sheet1->reveal(), $this->sheet2->reveal(), $this->sheet3->reveal()],
                true
            )
        );
    }
}
