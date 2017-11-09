<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfosHelper;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\FileRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TaggedDataFactory;
use Proximum\Vimeet\Infrastructure\Adapter\LocalFileStorageAdapter;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Printer\SheetPdfPrinter;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Command\PrintPdfMail;
use Symfony\Component\Templating\EngineInterface;

class BatchPdfHandler
{
    const SHEETS_TEMPLATE = 'AdminBundle:Sheet/Pdf:sheets.html.twig';

    /** @var MailerInterface */
    private $mailer;

    /** @var string */
    private $mailSender;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var EngineInterface */
    private $templating;

    /** @var LocalFileStorageAdapter */
    private $localFileStorageAdapter;

    /** @var string */
    private $generateSheetsPdfPath;

    /** @var FileRepositoryInterface */
    private $fileRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var SheetPdfPrinter */
    private $sheetPdfPrinter;

    /** @var TaggedDataFactory */
    private $taggedDataFactory;

    /** @var SheetInfosHelper */
    private $sheetInfosHelper;

    /**
     * @param MailerInterface          $mailer
     * @param string                   $mailSender
     * @param SheetRepositoryInterface $sheetRepository
     * @param EngineInterface          $templating
     * @param LocalFileStorageAdapter  $localFileStorageAdapter
     * @param string                   $generateSheetsPdfPath
     * @param FileRepositoryInterface  $fileRepository
     * @param \DateTimeInterface       $dateTime
     * @param SheetPdfPrinter          $sheetPdfPrinter
     * @param TaggedDataFactory        $taggedDataFactory
     * @param SheetInfosHelper         $sheetInfosHelper
     */
    public function __construct(
        MailerInterface $mailer,
        string $mailSender,
        SheetRepositoryInterface $sheetRepository,
        EngineInterface $templating,
        LocalFileStorageAdapter $localFileStorageAdapter,
        string $generateSheetsPdfPath,
        FileRepositoryInterface $fileRepository,
        \DateTimeInterface $dateTime,
        SheetPdfPrinter $sheetPdfPrinter,
        TaggedDataFactory $taggedDataFactory,
        SheetInfosHelper $sheetInfosHelper
    ) {
        $this->mailer                  = $mailer;
        $this->mailSender              = $mailSender;
        $this->sheetRepository         = $sheetRepository;
        $this->templating              = $templating;
        $this->localFileStorageAdapter = $localFileStorageAdapter;
        $this->generateSheetsPdfPath   = $generateSheetsPdfPath;
        $this->fileRepository          = $fileRepository;
        $this->dateTime                = $dateTime;
        $this->sheetPdfPrinter         = $sheetPdfPrinter;
        $this->taggedDataFactory       = $taggedDataFactory;
        $this->sheetInfosHelper        = $sheetInfosHelper;
    }

    /**
     * @param BatchPdf $batchPdf
     */
    public function handle(BatchPdf $batchPdf)
    {
        $sheets = $this->sheetRepository->getSheetsById($batchPdf->sheetIds);
        $event  = reset($sheets)->getEvent();
        $print  = '';

        foreach ($sheets as $sheet) {
            $print .= $this->generateHtml($sheet, $event, $batchPdf->locale);
        }

        $file    = $this->createFile($print);
        $pdfPath = $this->sheetPdfPrinter->printFromFile($file);

        $pdfFile = new File($pdfPath, $this->dateTime);
        $this->fileRepository->add($pdfFile);

        $this->notifyCreationOfFile($event, $batchPdf, $pdfFile);
    }

    /**
     * Send a mail to the emailToNotify with the summary of the types, orderBy and a link to see the file
     *
     * @param Event    $event
     * @param BatchPdf $batchPdf
     */
    private function notifyCreationOfFile(Event $event, BatchPdf $batchPdf, File $pdfFile)
    {
        $this->mailer->send(new PrintPdfMail(
            $event,
            $this->mailSender,
            $batchPdf->emailToNotify,
            $batchPdf->locale,
            $pdfFile->getHash(),
            $pdfFile->getId()
        ));
    }

    /**
     * @param string $print
     *
     * @return File
     */
    private function createFile(&$print)
    {
        $filePath = $this->localFileStorageAdapter->create(
            $print,
            'generate_sheets_pdf.html',
            sys_get_temp_dir()
        );

        $file = new File($filePath, $this->dateTime);
        $this->fileRepository->add($file);

        return $file;
    }

    /**
     * @param Sheet  $sheet
     * @param Event  $event
     * @param string $locale
     *
     * @return string
     */
    private function generateHtml(Sheet $sheet, Event $event, string $locale)
    {
        // Build sheet template data and attach tagged data view to template object with tags
        $templateData = $this->taggedDataFactory->buildTaggedDataView($sheet, $locale);
        $users        = $sheet->getUsers();
        $user         = reset($users);

        list ($nomenclatures, $participants, $taggedData) = $this->sheetInfosHelper->getInfos(
            $sheet,
            $user,
            $locale
        );

        return $this->templating->render(self::SHEETS_TEMPLATE, [
            'event'         => $event,
            'sheet'         => $sheet,
            'taggedData'    => $taggedData,
            'locale'        => $locale,
            'nomenclatures' => $nomenclatures,
            'participants'  => $participants,
            'templateData'  => $templateData,
        ]);
    }
}
