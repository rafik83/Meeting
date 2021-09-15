<?php

namespace Proximum\Vimeet\Domain\Exception\Happening;

use Exception;
use Proximum\Vimeet\Domain\Model\Happening;
use Throwable;

class HappeningException extends Exception
{
    /** @var Happening */
    protected $happening;

    public function __construct(Happening  $happening, $message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->happening = $happening;
    }
}
