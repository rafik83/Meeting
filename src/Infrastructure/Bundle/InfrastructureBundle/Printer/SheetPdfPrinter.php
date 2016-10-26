<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Printer;

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
    private $phantomjsHttpPassword;

    /**
     * @param RouterInterface $router
     * @param string          $phantomjsPath
     * @param string          $phantomjsScript
     * @param string          $phantomjsHttpPassword
     */
    public function __construct(RouterInterface $router, $phantomjsPath, $phantomjsScript, $phantomjsHttpPassword)
    {
        $this->router                = $router;
        $this->phantomjsPath         = $phantomjsPath;
        $this->phantomjsScript       = $phantomjsScript;
        $this->phantomjsHttpPassword = $phantomjsHttpPassword;
    }

    /**
     * @param User   $user   who want to print the pdf
     * @param Sheet  $sheet  targetted sheet
     * @param string $locale locale of the sheet
     *
     * @return string
     */
    public function generate(User $user, Sheet $sheet, $locale)
    {
        $pathToPdf = sprintf(
            '%s/%s-%s.pdf',
            sys_get_temp_dir(),
            $user->getId(),
            $sheet->getId()
        );

        $urlToPrint = $this->router->generate(
            'event_sheet_internal_print',
            [
                'sheet'  => $sheet->getId(),
                'locale' => $locale,
                'user'   => $user->getId(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $process = new Process(
            sprintf(
                '%s %s %s %s %s',
                $this->phantomjsPath,
                $this->phantomjsScript,
                $urlToPrint,
                $pathToPdf,
                $this->phantomjsHttpPassword
            )
        );

        $process->setTimeout(60);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }

        return $pathToPdf;
    }
}
