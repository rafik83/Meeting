<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Messaging\Campaign;

final class ReceiverView
{
    /**
     * @var string
     */
    private $email;

    /**
     * @var array
     */
    private $replaces;

    /**
     * @param string $email
     * @param array  $replaces An array of format [placeholder => value]
     *                         to be used for mail rendering
     */
    public function __construct($email, array $replaces)
    {
        $this->email    = $email;
        $this->replaces = $replaces;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return array
     */
    public function getReplaces()
    {
        return $this->replaces;
    }
}
