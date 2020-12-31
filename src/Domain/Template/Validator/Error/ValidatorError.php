<?php

namespace Proximum\Vimeet\Domain\Template\Validator\Error;

abstract class ValidatorError
{
    /**
     * @var string
     */
    protected $message;

    /**
     * @var mixed
     */
    protected $data;

    /**
     * @var bool
     */
    protected $hasNoError = false;

    /**
     * ValidatorError constructor.
     *
     * @param string $message
     * @param mixed  $data
     * @param bool   $hasNoError
     */
    public function __construct($message, $data, $hasNoError)
    {
        $this->message = $message;
        $this->data = $data;
        $this->hasNoError = $hasNoError;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return bool
     */
    public function hasError()
    {
        return false === $this->hasNoError;
    }
}
