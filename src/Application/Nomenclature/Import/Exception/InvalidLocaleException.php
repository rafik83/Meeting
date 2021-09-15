<?php

namespace Proximum\Vimeet\Application\Nomenclature\Import\Exception;

use Throwable;

class InvalidLocaleException extends ImportException
{
    /** @var string */
    private $invalidLocale;

    public function __construct(string $invalidLocale, string $message = '', int $code = 0, Throwable $previous = null)
    {
        $this->invalidLocale = $invalidLocale;

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string
     */
    public function getInvalidLocale(): string
    {
        return $this->invalidLocale;
    }
}
