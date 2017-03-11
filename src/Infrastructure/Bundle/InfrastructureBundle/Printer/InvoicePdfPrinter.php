<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Printer;

use Proximum\Vimeet\Domain\Model\Invoice\Invoice;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class InvoicePdfPrinter
{
    /** @var RouterInterface */
    private $router;

    /** @var string */
    private $phantomjsPath;

    /** @var string */
    private $phantomjsScript;

    /**
     * @param RouterInterface $router
     * @param string          $phantomjsPath
     * @param string          $phantomjsScript
     */
    public function __construct(RouterInterface $router, $phantomjsPath, $phantomjsScript)
    {
        $this->router                = $router;
        $this->phantomjsPath         = $phantomjsPath;
        $this->phantomjsScript       = $phantomjsScript;
    }

    /**
     * @param Invoice $invoice
     *
     * @return string
     */
    public function generate(Invoice $invoice)
    {
        $pathToPdf = sprintf(
            '%s/%s-%s.pdf',
            sys_get_temp_dir(),
            $invoice->getId(),
            $invoice->getNumber()
        );

        $urlToPrint = $this->router->generate(
            'event_invoice_show',
            [
                'sheet'  => $invoice->getSheet()->getId(),
                'invoice' => $invoice->getId(),
                'hash'   => $invoice->getHash(),
                'format' => 'html',
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $process = new Process(
            sprintf(
                '%s %s %s %s',
                $this->phantomjsPath,
                $this->phantomjsScript,
                $urlToPrint,
                $pathToPdf
            )
        );

        $process->setTimeout(10);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }

        return $pathToPdf;
    }
}
