<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\HttpFoundation\Response;

use Proximum\Vimeet\Application\Serializer\Charset;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class CsvFileResponse extends Response
{
    /**
     * @param mixed  $file
     * @param string $filename
     * @param int    $status
     * @param array  $headers
     * @param string $charset
     */
    public function __construct(
        $file,
        $filename,
        $status = 200,
        $headers = [],
        $charset = Charset::WINDOWS_1252
    ) {
        parent::__construct($file, $status, $headers);
        $disposition = $this->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename);
        $this->headers->set('Content-Disposition', $disposition);
        $this->headers->set('Content-Type', sprintf('text/csv; charset=%s', $charset));
    }
}
