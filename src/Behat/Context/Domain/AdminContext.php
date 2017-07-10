<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Behat\Context\Domain;

use Behat\Behat\Context\Context;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\AdminContextProxyInterface;

class AdminContext implements Context
{
    /** @var AdminContextProxyInterface */
    private $adminContextProxy;

    /**
     * @param AdminContextProxyInterface $adminContextProxy
     */
    public function __construct(AdminContextProxyInterface $adminContextProxy)
    {
        $this->adminContextProxy = $adminContextProxy;
    }

    /**
     * @Given /^the super admin "(?P<email>[^"]+)" is created$/
     *
     * @param string $email
     */
    public function createSuperAdmin(string $email)
    {
        $user = $this->adminContextProxy->getAdminManager()->create($email, 'ROLE_SUPER_ADMIN');

        $this->adminContextProxy->getStorage()->set('admin', $user);
    }
}
