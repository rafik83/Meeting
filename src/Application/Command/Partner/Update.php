<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Partner;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Type;

class Update
{
    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $lastname;

    /**
     * @var string
     */
    public $firstname;

    /**
     * @var Admin
     */
    public $partner;

    /**
     * @var Type[]
     */
    public $types;

    /**
     * @param Admin $partner
     */
    public function __construct(Admin $partner)
    {
        $this->partner   = $partner;
        $this->email     = $partner->getEmail();
        $this->firstname = $partner->getFirstname();
        $this->lastname  = $partner->getLastname();
        $this->types     = $partner->getAllowedTypes();
    }
}
