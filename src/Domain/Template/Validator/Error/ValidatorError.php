<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

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
    protected $error = false;

    /**
     * ValidatorError constructor.
     *
     * @param string $message
     * @param mixed  $data
     * @param bool   $error
     */
    public function __construct($message, $data, $error)
    {
        $this->message = $message;
        $this->data    = $data;
        $this->error   = $error;
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
        return $this->error === false;
    }
}
