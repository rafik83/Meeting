<?php

namespace Proximum\Vimeet\Domain\Template\Exception;

class ObjectNotFoundException extends TemplateException
{
    /**
     * ObjectNotFoundException constructor.
     *
     * @param string     $key
     * @param int        $code
     * @param \Exception $previous
     */
    public function __construct($key, $code = 0, \Exception $previous = null)
    {
        parent::__construct(sprintf('Object with key "%s" not found.', $key), $code, $previous);
    }
}
