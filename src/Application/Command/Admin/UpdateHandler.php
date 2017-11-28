<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Admin;

use Proximum\Vimeet\Application\Exception\User\EmailAlreadyExistsException;
use Proximum\Vimeet\Domain\Helper\StringHelper;

class UpdateHandler extends AbstractCreateHandler
{
    public function handle(Update $update)
    {
        $admin = $update->admin;
        $update->email = StringHelper::trimSpacesAndNonBreakSpaces($update->email);
        $newMail = ($update->email !== $admin->getEmail());

        if ($newMail && $this->adminRepository->emailExists($update->email)) {
            throw new EmailAlreadyExistsException(sprintf('"%s" already exists.', $update->email));
        }

        if (null !== $update->password) {
            $salt     = $this->saltGenerator->generate();
            $password = $this->encoder->encode($admin->updatePassword($salt, null), $update->password);

            $admin->updatePassword($salt, $password);
        }

        $admin->setFirstname($update->firstname)
            ->setLastname($update->lastname)
            ->setEmail($update->email)
            ->setRole($update->role)
            ->setEvents($update->events);

        $this->adminRepository->set($admin);
    }
}
