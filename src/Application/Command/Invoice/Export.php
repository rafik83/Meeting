<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Invoice;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;

class Export
{
    /**
     * @var AdvancedUserInterface
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
     * @param AdvancedUserInterface $admin
     */
    public function __construct(AdvancedUserInterface $admin)
    {
        $this->admin = $admin;
    }
}
