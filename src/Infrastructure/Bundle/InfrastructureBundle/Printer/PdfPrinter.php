<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Printer;

use Symfony\Component\Process\Process;

class PdfPrinter
{
    /** @var string */
    private $phantomjsPath;

    /** @var string */
    private $phantomjsScript;

    /** @var string */
    private $phantomjsHttpUser;

    /** @var string */
    private $phantomjsHttpPassword;

    public function __construct(
        string $phantomjsPath,
        string $phantomjsScript,
        string $phantomjsHttpUser,
        string $phantomjsHttpPassword
    ) {
        $this->phantomjsPath         = $phantomjsPath;
        $this->phantomjsScript       = $phantomjsScript;
        $this->phantomjsHttpUser     = $phantomjsHttpUser;
        $this->phantomjsHttpPassword = $phantomjsHttpPassword;
    }

    public function generate(string $urlToPrint, string $pathToPdf): string
    {
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
}
