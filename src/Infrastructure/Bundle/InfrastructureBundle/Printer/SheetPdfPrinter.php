<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Printer;

use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class SheetPdfPrinter
{
    /** @var RouterInterface */
    private $router;

    /** @var string */
    private $phantomjsPath;

    /** @var string */
    private $phantomjsScript;

    /** @var string */
    private $phantomjsHttpUser;

    /** @var string */
    private $phantomjsHttpPassword;

    /** @var string */
    private $pdfPath;

    /**
     * @param RouterInterface $router
     * @param string          $phantomjsPath
     * @param string          $phantomjsScript
     * @param string          $phantomjsHttpUser
     * @param string          $phantomjsHttpPassword
     * @param string          $pdfPath
     */
    public function __construct(
        RouterInterface $router,
        string $phantomjsPath,
        string $phantomjsScript,
        string $phantomjsHttpUser,
        string $phantomjsHttpPassword,
        string $pdfPath
    ) {
        $this->router                = $router;
        $this->phantomjsPath         = $phantomjsPath;
        $this->phantomjsScript       = $phantomjsScript;
        $this->phantomjsHttpUser     = $phantomjsHttpUser;
        $this->phantomjsHttpPassword = $phantomjsHttpPassword;
        $this->pdfPath               = $pdfPath;
    }

    /**
     * @param User   $user           who want to print the pdf
     * @param Sheet  $sheet          targetted sheet
     * @param Sheet  $sheetToDisplay
     * @param string $locale         locale of the sheet
     *
     * @return string
     */
    public function generate(User $user, Sheet $sheet, Sheet $sheetToDisplay, $locale)
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
                'user'           => $user->getId(),
                'sheet'          => $sheet->getId(),
                'sheetToDisplay' => $sheetToDisplay->getId(),
                'locale'         => $locale,
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $process = new Process(
            sprintf(
                '%s %s %s %s %s %s',
                $this->phantomjsPath,
                $this->phantomjsScript,
                $urlToPrint,
                $pathToPdf,
                $this->phantomjsHttpUser,
                $this->phantomjsHttpPassword
            )
        );

        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }

        return $pathToPdf;
    }

    /**
     * @param File   $file
     * @param string $directory
     *
     * @return string
     */
    public function printFromFile(File $file, string $directory)
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

        $process = new Process(
            sprintf(
                '%s %s %s %s',
                $this->phantomjsPath,
                $this->phantomjsScript,
                $pathToHtml,
                $pathToPdf
            )
        );

        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }

        return $pathToPdf;
    }
}
