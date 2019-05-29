<?php

namespace Proximum\Vimeet\Tests\Application\Command\Sheet;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\InvoicesPdfBulkPrinterInterface;
use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\Command\Sheet\BatchPrintInvoices;
use Proximum\Vimeet\Application\Command\Sheet\BatchPrintInvoicesHandler;
use Proximum\Vimeet\Domain\File\FileFactory;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Invoice\PrintInvoicesMail;

class BatchPrintInvoicesHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        // prepare data
        $sheetIds = [1, 2, 3, 5, 8];
        $eventId = 42;
        $file = $this->prophesize(File::class);
        $event = $this->prophesize(Event::class);

        $file->getId()->willReturn(7777);
        $file->getHash()->willReturn('hashTropFortPourToi');

        // prophecy dependencies
        $mailer = $this->prophesize(MailerInterface::class);
        $invoicesPdfBulkPrinterInterface = $this->prophesize(InvoicesPdfBulkPrinterInterface::class);
        $fileFactory = $this->prophesize(FileFactory::class);
        $eventRepositoryInterface = $this->prophesize(EventRepositoryInterface::class);

        $invoicesPdfBulkPrinterInterface->generate($sheetIds)
            ->shouldBeCalled()
            ->willReturn('A:\invoices.pdf')
        ;

        $eventRepositoryInterface->getById($eventId)
            ->shouldBeCalled()
            ->willReturn($event->reveal())
        ;

        $fileFactory->createAndPersistFile('A:\invoices.pdf', File::TYPE_PRINT_INVOICES)
            ->shouldBeCalled()
            ->willReturn($file->reveal())
        ;

        $mailer->send(
            new PrintInvoicesMail(
                $event->reveal(),
                'phpunit@tests.local',
                'foo@bar.fr',
                'fr',
                'hashTropFortPourToi',
                7777
            )
        )
            ->shouldBeCalled()
        ;

        // run tests
        $query = new BatchPrintInvoices($eventId, $sheetIds, 'foo@bar.fr', 'fr');
        $handler = new BatchPrintInvoicesHandler(
            $mailer->reveal(),
            $invoicesPdfBulkPrinterInterface->reveal(),
            'phpunit@tests.local',
            $fileFactory->reveal(),
            $eventRepositoryInterface->reveal()
        );
        $handler->handle($query);
    }
}
