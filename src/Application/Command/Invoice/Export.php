<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Invoice;

use Symfony\Component\Security\Core\User\UserInterface;

class Export
{
    /**
     * @var Admin
     */
    public $admin;

    /**
     * @var \DateTimeInterface
     */
    public $beginDate;

    /**
     * @var \DateTimeInterface
     */
    public $endDate;

    /**
     * Export constructor.
     *
     * @param UserInterface $admin
     */
    public function __construct(UserInterface $admin)
    {
        $this->admin = $admin;
    }
}
