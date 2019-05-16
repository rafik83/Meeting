<?php

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Adapter\InvoicesPdfBulkPrinterInterface;
use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Domain\File\FileFactory;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Invoice\PrintInvoicesMail;

class BatchPrintInvoicesHandler
{
    /** @var MailerInterface */
    private $mailer;

    /** @var InvoicesPdfBulkPrinterInterface */
    private $invoicesPdfBulkPrinter;

    /** @var string */
    private $mailSender;

    /** @var FileFactory */
    private $fileFactory;

    /** @var EventRepositoryInterface */
    private $eventRepository;

    public function __construct(
        MailerInterface $mailer,
        InvoicesPdfBulkPrinterInterface $invoicesPdfBulkPrinter,
        string $mailSender,
        FileFactory $fileFactory,
        EventRepositoryInterface $eventRepository
    ) {
        $this->mailer = $mailer;
        $this->invoicesPdfBulkPrinter = $invoicesPdfBulkPrinter;
        $this->mailSender = $mailSender;
        $this->fileFactory = $fileFactory;
        $this->eventRepository = $eventRepository;
    }

    public function handle(BatchPrintInvoices $batchPrintInvoices): void
    {
        $filePath = $this->invoicesPdfBulkPrinter->generate($batchPrintInvoices->sheetIds);

        $file = $this->fileFactory->createAndPersistFile($filePath, File::TYPE_PRINT_INVOICES);

        $event = $this->eventRepository->getById($batchPrintInvoices->eventId);

        $this->mailer->send(
            new PrintInvoicesMail(
                $event,
                $this->mailSender,
                $batchPrintInvoices->emailToNotify,
                $batchPrintInvoices->locale,
                $file->getHash(),
                $file->getId()
            )
        );
    }
}
