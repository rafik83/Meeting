<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\Components\Sheet\Pdf\GenerateHtml;
use Proximum\Vimeet\Application\Components\Sheet\Pdf\GenerateHtmlFile;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Command\PrintPdfMail;

class BatchPdfHandler
{
    /** @var MailerInterface */
    private $mailer;

    /** @var string */
    private $mailSender;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var string */
    private $domain;

    /** @var string */
    private $scheme;

    /** @var GenerateHtml */
    private $generateHtml;

    /** @var GenerateHtmlFile */
    private $generateHtmlFile;

    /**
     * @param EventRepositoryInterface $eventRepository
     * @param MailerInterface          $mailer
     * @param string                   $mailSender
     * @param SheetRepositoryInterface $sheetRepository
     * @param GenerateHtmlFile         $generateHtmlFile
     * @param GenerateHtml             $generateHtml
     * @param string                   $domain
     * @param string                   $scheme
     */
    public function __construct(
        EventRepositoryInterface $eventRepository,
        MailerInterface $mailer,
        string $mailSender,
        SheetRepositoryInterface $sheetRepository,
        GenerateHtmlFile $generateHtmlFile,
        GenerateHtml $generateHtml,
        string $domain,
        string $scheme
    ) {
        $this->eventRepository  = $eventRepository;
        $this->mailer           = $mailer;
        $this->mailSender       = $mailSender;
        $this->sheetRepository  = $sheetRepository;
        $this->generateHtmlFile = $generateHtmlFile;
        $this->generateHtml     = $generateHtml;
        $this->domain           = $domain;
        $this->scheme           = $scheme;
    }

    /**
     * @param BatchPdf $batchPdf
     */
    public function handle(BatchPdf $batchPdf)
    {
        $event  = $this->eventRepository->getById($batchPdf->eventId);
        $sheets = $this->sheetRepository->getSheetsById($batchPdf->sheetIds);

        $this->generateHtml->setContext($this->scheme, $this->domain);
        $html    = $this->generateHtml->printSheets($event, $sheets, $event->getAvailableLocale($batchPdf->locale));
        $htmlFile = $this->generateHtmlFile->generateFile($html);

        $this->notifyCreationOfFile($event, $batchPdf->emailToNotify, $batchPdf->locale, $htmlFile);
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
}
