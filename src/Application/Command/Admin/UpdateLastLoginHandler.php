<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Admin;

use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;

class UpdateLastLoginHandler
{
    /**
     * @var AdminRepositoryInterface
     */
    private $adminRepository;

    /**
     * @param AdminRepositoryInterface $adminRepository
     */
    public function __construct(AdminRepositoryInterface $adminRepository)
    {
        $this->adminRepository = $adminRepository;
    }

    /**
     * @param UpdateLastLogin $updateLastLogin
     */
    public function handle(UpdateLastLogin $updateLastLogin)
    {
        $admin = $this->adminRepository->findByEmail($updateLastLogin->email);

        if (null !== $admin) {
            $admin->setLastLoginAt($updateLastLogin->date);
            $this->adminRepository->set($admin);
        }
    }
}
