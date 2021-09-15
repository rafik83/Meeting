<?php

namespace Proximum\Vimeet\Application\View\Adapter\Http;

class Response
{
    /** @var int */
    public $statusCode;

    /** @var string */
    public $body;

    /**
     * @param int    $statusCode
     * @param string $body
     */
    public function __construct(int $statusCode, string $body)
    {
        $this->statusCode = $statusCode;
        $this->body = $body;
    }
}
