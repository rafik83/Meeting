<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Behat\Service\Manager;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;

class AdminManager
{
    /** @var AdminRepositoryInterface */
    private $adminRepository;

    /**
     * @param AdminRepositoryInterface $adminRepository
     */
    public function __construct(AdminRepositoryInterface $adminRepository)
    {
        $this->adminRepository = $adminRepository;
    }

    /**
     * @param string|null $email
     * @param string      $role
     *
     * @return Admin
     */
    public function create(string $email = null, string $role)
    {
        if ($email === null) {
            $email = sprintf('%s@example.net', uniqid());
        }

        $admin = new Admin(
            $email,
            'D/TBAVl5oYyYU6/4F7gOT0mQkbBD8c5rBHga80zO',
            'YzzBNEhw7I6H5xPuziQEAPAsg5g=',
            'fr',
            'Bob',
            'Teemiv',
            $role,
            new \DateTime()
        ); // password: vimeet_admin

        $this->adminRepository->add($admin);

        return $admin;
    }
}
