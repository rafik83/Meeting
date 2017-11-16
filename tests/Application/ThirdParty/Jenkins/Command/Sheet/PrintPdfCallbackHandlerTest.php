<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\ThirdParty\Jenkins\Command\Sheet;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\ThirdParty\Jenkins\Command\Sheet\PrintPdfCallbackHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\FileRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Command\PrintPdfMail;

class PrintPdfCallbackHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $mailer;

    /** @var ObjectProphecy */
    private $eventRepository;

    /** @var ObjectProphecy */
    private $fileStorage;

    /** @var ObjectProphecy */
    private $fileRepository;

    /** @var \DateTime */
    private $dateTime;

    public function setUp()
    {
        $this->dateTime = new \DateTime();
        $this->event = $this->prophesize(Event::class);
        $this->mailer = $this->prophesize(MailerInterface::class);
        $this->eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $this->fileStorage = $this->prophesize(FileStorageInterface::class);
        $this->fileRepository = $this->prophesize(FileRepositoryInterface::class);
    }

    public function testHandle()
    {
        $mail = new PrintPdfMail(
            $this->event->Reveal(),
            'sender@vimeet.dev',
            'email@example.net',
            'fr',
            'hash',
            'id'
        );
        $this->mailer->send($mail)->shouldBeCalled();

        $handler = new PrintPdfCallbackHandler(
            $this->fileStorage->reveal(),
            $this->eventRepository->reveal(),
            $this->fileRepository->reveal(),
            $this->mailer->reveal(),
            'sender@vimeet.dev',
            'trusted_name',
            $this->dateTime
        );
    }

    public function testHandleFailure()
    {
        $handler = new PrintPdfCallbackHandler(
            $this->fileStorage->reveal(),
            $this->eventRepository->reveal(),
            $this->fileRepository->reveal(),
            $this->mailer->reveal(),
            'sender@vimeet.dev',
            'trusted_name',
            $this->dateTime
        );
    }
}
