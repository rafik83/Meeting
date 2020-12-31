<?php

namespace Proximum\Vimeet\Application\Components\Sheet\Pdf;

use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Repository\FileRepositoryInterface;
use Proximum\Vimeet\Domain\Token\UniqidGenerator;

class GenerateHtmlFile
{
    /** @var FileStorageInterface */
    private $localFileStorageAdapter;

    /** @var FileRepositoryInterface */
    private $fileRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var string */
    private $pdfPath;

    /** @var UniqidGenerator */
    private $uniqidGenerator;

    /**
     * @param UniqidGenerator         $uniqidGenerator
     * @param FileStorageInterface    $localFileStorageAdapter
     * @param FileRepositoryInterface $fileRepository
     * @param string                  $pdfPath
     * @param \DateTimeInterface      $dateTime
     */
    public function __construct(
        UniqidGenerator $uniqidGenerator,
        FileStorageInterface $localFileStorageAdapter,
        FileRepositoryInterface $fileRepository,
        string $pdfPath,
        \DateTimeInterface $dateTime
    ) {
        $this->uniqidGenerator         = $uniqidGenerator;
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
            $this->uniqidGenerator->generate() . '_generate_sheets_pdf.html',
            $this->pdfPath
        );

        $file = new File($filePath, $this->dateTime);
        $this->fileRepository->add($file);

        return $file;
    }

    /**
     * @return string
     */
    public function getFileDirectory(): string
    {
        return $this->pdfPath;
    }
}
