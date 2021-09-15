<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Printer;

use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class SheetPdfPrinter
{
    /** @var RouterInterface */
    private $router;

    /** @var PdfPrinter */
    private $pdfPrinter;

    /** @var string */
    private $pdfPath;

    public function __construct(
        RouterInterface $router,
        PdfPrinter $pdfPrinter,
        string $pdfPath
    ) {
        $this->router     = $router;
        $this->pdfPrinter = $pdfPrinter;
        $this->pdfPath    = $pdfPath;
    }

    /**
     * @param User   $user           who want to print the pdf
     * @param Sheet  $sheet          targetted sheet
     * @param Sheet  $sheetToDisplay
     * @param string $locale         locale of the sheet
     *
     * @return string
     */
    public function generate(User $user, Sheet $sheet, Sheet $sheetToDisplay, $locale): string
    {
        $pathToPdf = sprintf(
            '%s/%s-%s.pdf',
            sys_get_temp_dir(),
            $user->getId(),
            $sheetToDisplay->getId()
        );

        $urlToPrint = $this->router->generate(
            'event_sheet_internal_print',
            [
                'user' => $user->getId(),
                'sheet' => $sheet->getId(),
                'sheetToDisplayId' => $sheetToDisplay->getId(),
                'locale' => $locale,
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return $this->pdfPrinter->generate($urlToPrint, $pathToPdf);
    }

    /**
     * @deprecated
     *
     * @param File   $file
     * @param string $directory
     *
     * @return string
     */
    public function printFromFile(File $file, string $directory): string
    {
        $pathToPdf = sprintf(
            '%s/%s-%s-%s.pdf',
            $this->pdfPath,
            $file->getHash(),
            $file->getId(),
            'batch'
        );

        $pathToHtml = sprintf(
            '%s/%s',
            $directory,
            $file->getPath()
        );

        return $this->pdfPrinter->generate($pathToHtml, $pathToPdf);
    }
}
