<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Pdf;

use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Repository\FileRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\LocalFileStorageAdapter;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Printer\SheetPdfPrinter;

/**
 * This class generate a pdf file from an html string
 */
class HtmlToPdf
{
    /** @var string */
    private $tmpDir;

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

    /**
     * @param SheetPdfPrinter         $sheetPdfPrinter
     * @param LocalFileStorageAdapter $localFileStorageAdapter
     * @param string                  $generateSheetsPdfPath
     * @param FileRepositoryInterface $fileRepository
     * @param \DateTimeInterface      $dateTime
     */
    public function __construct(
        SheetPdfPrinter $sheetPdfPrinter,
        LocalFileStorageAdapter $localFileStorageAdapter,
        string $generateSheetsPdfPath,
        FileRepositoryInterface $fileRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->sheetPdfPrinter         = $sheetPdfPrinter;
        $this->localFileStorageAdapter = $localFileStorageAdapter;
        $this->generateSheetsPdfPath   = $generateSheetsPdfPath;
        $this->fileRepository          = $fileRepository;
        $this->dateTime                = $dateTime;
        $this->tmpDir                  = sys_get_temp_dir();
    }

    /**
     * @param string $html
     *
     * @return File
     */
    public function generatePdf(string $html): File
    {
        $htmlFile = $this->createFile($html);
        $pdfPath  = $this->sheetPdfPrinter->printFromFile($htmlFile, $this->tmpDir);

        $pdfFile = new File($pdfPath, $this->dateTime);
        $this->fileRepository->add($pdfFile);

        // Remove html file
        $this->localFileStorageAdapter->remove($htmlFile->getPath(), true);
        $this->fileRepository->remove($htmlFile);

        return $pdfFile;
    }

    /**
     * @param string $content
     *
     * @return File
     */
    private function createFile(string &$content): File
    {
        $filePath = $this->localFileStorageAdapter->create(
            $content,
            'generate_sheets_pdf.html',
            $this->tmpDir
        );

        $file = new File($filePath, $this->dateTime);
        $this->fileRepository->add($file);

        return $file;
    }
}
