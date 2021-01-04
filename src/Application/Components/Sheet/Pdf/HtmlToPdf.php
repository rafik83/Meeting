<?php

namespace Proximum\Vimeet\Application\Components\Sheet\Pdf;

use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Repository\FileRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\LocalFileStorageAdapter;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Printer\SheetPdfPrinter;

/**
 * @deprecated
 *
 * This class generate a pdf file from an html string
 */
class HtmlToPdf
{
    /** @var LocalFileStorageAdapter */
    private $localFileStorageAdapter;

    /** @var FileRepositoryInterface */
    private $fileRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var SheetPdfPrinter */
    private $sheetPdfPrinter;

    /** @var string */
    private $pdfPath;

    /**
     * @param SheetPdfPrinter         $sheetPdfPrinter
     * @param LocalFileStorageAdapter $localFileStorageAdapter
     * @param FileRepositoryInterface $fileRepository
     * @param string                  $pdfPath
     * @param \DateTimeInterface      $dateTime
     */
    public function __construct(
        SheetPdfPrinter $sheetPdfPrinter,
        LocalFileStorageAdapter $localFileStorageAdapter,
        FileRepositoryInterface $fileRepository,
        string $pdfPath,
        \DateTimeInterface $dateTime
    ) {
        $this->sheetPdfPrinter         = $sheetPdfPrinter;
        $this->localFileStorageAdapter = $localFileStorageAdapter;
        $this->fileRepository          = $fileRepository;
        $this->dateTime                = $dateTime;
        $this->pdfPath                 = $pdfPath;
    }

    /**
     * @param File $htmlFile
     *
     * @return File
     */
    public function generatePdf(File $htmlFile): File
    {
        $pdfPath = $this->sheetPdfPrinter->printFromFile($htmlFile, $this->pdfPath);

        $pdfFile = new File($pdfPath, $this->dateTime);
        $this->fileRepository->add($pdfFile);

        // Remove html file
        $this->localFileStorageAdapter->remove($htmlFile->getPath());
        $this->fileRepository->remove($htmlFile);

        return $pdfFile;
    }
}
