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
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\FileRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TaggedDataFactory;
use Proximum\Vimeet\Infrastructure\Adapter\LocalFileStorageAdapter;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Printer\SheetPdfPrinter;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Command\PrintPdfMail;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Templating\EngineInterface;

class BatchPdfHandler
{
    const PDF_TEMPLATE   = 'AdminBundle:Sheet/Pdf:index.html.twig';
    const SHEET_TEMPLATE = 'AdminBundle:Sheet/Pdf:sheet.html.twig';

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

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var string */
    private $domain;

    /** @var string */
    private $scheme;

    /** @var Router */
    private $router;

    /**
     * @param Router                   $router
     * @param EventRepositoryInterface $eventRepository
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
     * @param string                   $domain
     * @param string                   $scheme
     */
    public function __construct(
        Router $router,
        EventRepositoryInterface $eventRepository,
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
        SheetInfosHelper $sheetInfosHelper,
        string $domain,
        string $scheme
    ) {
        $this->eventRepository         = $eventRepository;
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
        $this->domain                  = $domain;
        $this->scheme                  = $scheme;
        $this->router = $router;
    }

    /**
     * @param BatchPdf $batchPdf
     */
    public function handle(BatchPdf $batchPdf)
    {
        $event  = $this->eventRepository->getById($batchPdf->eventId);
        $sheets = $this->sheetRepository->getSheetsById($batchPdf->sheetIds);
        $print  = '';

        $context = $this->router->getContext();
        $context->setHost($this->domain);
        $context->setScheme($this->scheme);

        foreach ($sheets as $sheet) {
            $print .= $this->generateSheetHtml($sheet, $event, $batchPdf->locale);
        }

        $html = $this->templating->render(self::PDF_TEMPLATE, [
            'event'  => $event,
            'print'  => $print,
            'locale' => $batchPdf->locale,
        ]);

        $htmlFile = $this->createFile($html);
        $pdfPath  = $this->sheetPdfPrinter->printFromFile($htmlFile, sys_get_temp_dir());

        $pdfFile = new File($pdfPath, $this->dateTime);
        $this->fileRepository->add($pdfFile);

        // Remove html file
        $this->localFileStorageAdapter->remove($htmlFile->getPath(), true);
        $this->fileRepository->remove($htmlFile);

        $this->notifyCreationOfFile($event, $batchPdf->emailToNotify, $batchPdf->locale, $pdfFile);
    }

    /**
     * Send a mail to the emailToNotify with the summary of the types, orderBy and a link to see the file
     *
     * @param Event  $event
     * @param string $email
     * @param string $locale
     * @param File   $pdfFile
     */
    private function notifyCreationOfFile(Event $event, string $email, string $locale, File $pdfFile)
    {
        $this->mailer->send(new PrintPdfMail(
            $event,
            $this->mailSender,
            $email,
            $locale,
            $pdfFile->getHash(),
            $pdfFile->getId()
        ));
    }

    /**
     * @param string $content
     *
     * @return File
     */
    private function createFile(string &$content)
    {
        $filePath = $this->localFileStorageAdapter->create(
            $content,
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
    private function generateSheetHtml(Sheet $sheet, Event $event, string $locale): string
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

        $template = $this->templating->render(self::SHEET_TEMPLATE, [
            'event'         => $event,
            'sheet'         => $sheet,
            'taggedData'    => $taggedData,
            'locale'        => $locale,
            'nomenclatures' => $nomenclatures,
            'participants'  => $participants,
            'templateData'  => $templateData,
        ]);

        return $template;
    }
}
