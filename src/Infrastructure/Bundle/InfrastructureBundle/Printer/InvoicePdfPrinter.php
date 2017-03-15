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

    /** @var string */
    private $phantomjsHttpUser;

    /** @var string */
    private $phantomjsHttpPassword;

    /**
     * @param RouterInterface $router
     * @param string          $phantomjsPath
     * @param string          $phantomjsScript
     * @param string          $phantomjsHttpUser
     * @param string          $phantomjsHttpPassword
     */
    public function __construct(
        RouterInterface $router,
        $phantomjsPath,
        $phantomjsScript,
        $phantomjsHttpUser,
        $phantomjsHttpPassword
    ) {
        $this->router                = $router;
        $this->phantomjsPath         = $phantomjsPath;
        $this->phantomjsScript       = $phantomjsScript;
        $this->phantomjsHttpUser     = $phantomjsHttpUser;
        $this->phantomjsHttpPassword = $phantomjsHttpPassword;
    }

    /**
     * @param Invoice $invoice
     *
     * @return string
     */
    public function generate(Invoice $invoice)
    {
        $pathToPdf = sprintf(
            '%s/invoice-%s.pdf',
            sys_get_temp_dir(),
            $invoice->getId()
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
