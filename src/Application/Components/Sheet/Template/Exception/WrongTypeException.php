<?php

namespace Proximum\Vimeet\Application\Components\Sheet\Template\Exception;

class WrongTypeException extends TemplateException
{
    /**
     * WrongTypeException constructor.
     *
     * @param string|object $wrongType
     * @param string        $expectedType
     */
    public function __construct($wrongType, $expectedType)
    {
        parent::__construct(sprintf('"%s" type given, "%s" type expected', is_object($wrongType) ? get_class($wrongType) : $wrongType, $expectedType));
    }
}
