<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Template\Exception;

class ChoiceNotFoundException extends TemplateException
{
    /**
     * InvalidTypeException constructor.
     *
     * @param string          $choice
     * @param array           $availableChoices
     * @param int             $code
     * @param \Exception|null $previous
     */
    public function __construct($choice, array $availableChoices, $code = 0, \Exception $previous = null)
    {
        $message = sprintf('Choice "%s" not found. Available choices are "%s"', $choice, implode('", "', $availableChoices));

        parent::__construct($message, $code, $previous);
    }
}
