<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Sheet\Group;

class ImpersonationUserView
{
    /** @var string */
    public $fromEmail;

    /** @var string */
    public $toEmail;

    /** @var string */
    public $toFirstName;

    /** @var string */
    public $toLastName;

    /**
     * @param string $fromEmail
     * @param string $toEmail
     * @param string $toFirstName
     * @param string $toLastName
     */
    public function __construct($fromEmail, $toEmail, $toFirstName, $toLastName)
    {
        $this->fromEmail = $fromEmail;
        $this->toEmail = $toEmail;
        $this->toFirstName = $toFirstName;
        $this->toLastName = $toLastName;
    }
}
