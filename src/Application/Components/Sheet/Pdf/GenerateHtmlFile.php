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

class GenerateHtmlFile
{
    /** @var LocalFileStorageAdapter */
    private $localFileStorageAdapter;

    /** @var FileRepositoryInterface */
    private $fileRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var string */
    private $pdfPath;

    /**
     * @param LocalFileStorageAdapter $localFileStorageAdapter
     * @param FileRepositoryInterface $fileRepository
     * @param string                  $pdfPath
     * @param \DateTimeInterface      $dateTime
     */
    public function __construct(
        LocalFileStorageAdapter $localFileStorageAdapter,
        FileRepositoryInterface $fileRepository,
        string $pdfPath,
        \DateTimeInterface $dateTime
    ) {
        $this->localFileStorageAdapter = $localFileStorageAdapter;
        $this->fileRepository          = $fileRepository;
        $this->dateTime                = $dateTime;
        $this->pdfPath                 = $pdfPath;
    }

    /**
     * @param string $html
     *
     * @return File
     */
    public function generateFile(string $html): File
    {
        return $this->createFile($html);
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
            $this->pdfPath
        );

        $file = new File($filePath, $this->dateTime);
        $this->fileRepository->add($file);

        return $file;
    }
}
