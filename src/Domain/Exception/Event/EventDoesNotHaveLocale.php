<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Exception\Event;

class EventDoesNotHaveLocale extends EventException
{
    /** @var string */
    public $locale;

    /**
     * {@inheritdoc}
     */
    public function __construct(string $message, string $locale)
    {
        parent::__construct($message);

        $this->message = $message;
        $this->locale = $locale;
    }
}
