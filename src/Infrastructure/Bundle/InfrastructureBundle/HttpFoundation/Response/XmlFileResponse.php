<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\HttpFoundation\Response;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class XmlFileResponse extends Response
{
    /**
     * @param mixed  $file
     * @param string $filename
     * @param int    $status
     * @param array  $headers
     */
    public function __construct(
        $file,
        $filename,
        $status = 200,
        $headers = []
    ) {
        parent::__construct($file, $status, $headers);

        $disposition = $this->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename);
        $this->headers->set('Content-Disposition', $disposition);
        $this->headers->set('Content-Type', 'xml');
    }
}
