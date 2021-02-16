<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\HttpFoundation\Response;

use Proximum\Vimeet\Application\Serializer\Charset;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class CsvFileResponse extends Response
{
    /**
     * @param string $content
     * @param string $filename
     * @param int    $status
     * @param array  $headers
     * @param string $charset
     */
    public function __construct(
        $content,
        $filename,
        $status = 200,
        $headers = [],
        $charset = Charset::WINDOWS_1252
    ) {
        parent::__construct($content, $status, $headers);

        $disposition = $this->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename);
        $this->headers->set('Content-Disposition', $disposition);
        $this->headers->set('Content-Type', sprintf('text/csv; charset=%s', $charset));
    }
}
