<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Exception\User;

use Proximum\Vimeet\Domain\Model\Admin;

class EmailAlreadyExistsException extends RegisterException
{
    private $existingAdmin;

    public function getExistingAdmin(): Admin
    {
        return $this->existingAdmin;
    }

    public function setExistingAdmin(Admin $existingAdmin): void
    {
        $this->existingAdmin = $existingAdmin;
    }
}
