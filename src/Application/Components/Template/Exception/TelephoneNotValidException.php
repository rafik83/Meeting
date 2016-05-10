<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Template\Exception;

class TelephoneNotValidException extends TemplateException
{
    /**
     * @var array
     */
    private $keys;

    /**
     * TelephoneNotValidException constructor.
     *
     * @param array           $keys
     * @param int             $code
     * @param \Exception|null $previous
     */
    public function __construct(array $keys, $code = 0, \Exception $previous = null)
    {
        $message = count($keys) > 1 ? 'The data fields "%s" are not valid telephone data' : 'The data field "%s" is not a valid telephone data';

        parent::__construct(sprintf($message, implode('", "', $keys)), $code, $previous);

        $this->keys = $keys;
    }

    /**
     * Get keys
     *
     * @return array
     */
    public function getKeys()
    {
        return $this->keys;
    }
}
