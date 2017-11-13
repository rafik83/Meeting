<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Sheet;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\Command\Sheet\BatchPdf;
use Proximum\Vimeet\Application\Command\Sheet\BatchPdfHandler;
use Proximum\Vimeet\Application\Components\Sheet\Pdf\GenerateHtml;
use Proximum\Vimeet\Application\Components\Sheet\Pdf\GenerateHtmlFile;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Command\PrintPdfMail;

class BatchPdfHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);
        $sheet3 = $this->prophesize(Sheet::class);
        $file   = $this->prophesize(File::class);
        $file->getHash()->willReturn('hash');
        $file->getId()->willReturn('id');

        $event->getAvailableLocale('fr')->shouldBeCalled()->willReturn('fr');

        $sheets = [
            $sheet1->reveal(),
            $sheet2->reveal(),
            $sheet3->reveal(),
        ];

        $sender           = 'sender@vimeet.dev';
        $eventRepository  = $this->prophesize(EventRepositoryInterface::class);
        $sheetRepository  = $this->prophesize(SheetRepositoryInterface::class);
        $generateHtml     = $this->prophesize(GenerateHtml::class);
        $generateHtmlFile = $this->prophesize(GenerateHtmlFile::class);
        $mailer           = $this->prophesize(MailerInterface::class);

        $eventRepository->getById(12)->shouldBeCalled()->willReturn($event->reveal());
        $sheetRepository->getSheetsById([9, 11, 13, 17])->shouldBeCalled()->willReturn($sheets);
        $generateHtml->setContext('https', 'admin.vimeet.proximum.dev')->shouldBeCalled();
        $generateHtml->printSheets($event->reveal(), $sheets, 'fr')->shouldBeCalled()->willReturn('ficheAficheBficheC');
        $generateHtmlFile->generateFile('ficheAficheBficheC')->shouldBeCalled()->willReturn($file->reveal());

        $mail = new PrintPdfMail(
            $event->Reveal(),
            'sender@vimeet.dev',
            'email@example.net',
            'fr',
            'hash',
            'id'
        );
        $mailer->send($mail)->shouldBeCalled();

        $command = new BatchPdf(12, [9, 11, 13, 17], 'email@example.net', 'fr');
        $handler = new BatchPdfHandler(
            $eventRepository->reveal(),
            $mailer->reveal(),
            $sender,
            $sheetRepository->reveal(),
            $generateHtmlFile->reveal(),
            $generateHtml->reveal(),
            'admin.vimeet.proximum.dev',
            'https'
        );

        $handler->handle($command);
    }
}
