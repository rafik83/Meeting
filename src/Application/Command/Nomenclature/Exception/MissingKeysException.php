<?php

namespace Proximum\Vimeet\Application\Command\Nomenclature\Exception;

class MissingKeysException extends \Exception
{
    /**
     * MissingKeysException constructor.
     *
     * @param array      $keys
     * @param int        $code
     * @param \Exception $previous
     */
    public function __construct(array $keys, $code = 0, \Exception $previous = null)
    {
        parent::__construct(sprintf('Missing keys : "%s"', implode('", "', $keys)), $code, $previous);
    }
}
