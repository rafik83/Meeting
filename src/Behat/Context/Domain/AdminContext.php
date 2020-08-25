<?php

namespace Proximum\Vimeet\Behat\Context\Domain;

use Behat\Behat\Context\Context;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\AdminContextProxyInterface;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;

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
    public function createSuperAdmin(string $email): void
    {
        $this->createAdmin($email, 'ROLE_SUPER_ADMIN');
    }

    /**
     * @Given /^the admin "(?P<email>[^"]+)" with role "(?P<role>[^"]+)" is created$/
     *
     * @param string $email
     * @param string $role
     */
    public function createAdmin(string $email, string $role): void
    {
        $admin = $this->adminContextProxy->getAdminManager()->create($email, $role);

        $this->adminContextProxy->getStorage()->set('admin', $admin);
    }

    /**
     * @Given /^this admin has an activation token "(?P<token>[^"]+)"$/
     */
    public function createAdminWithToken(string $token): void
    {
        $admin = $this->adminContextProxy->getStorage()->get('admin');

        if (!$admin instanceof Admin) {
            throw new \InvalidArgumentException('Admin not found');
        }

        $this->adminContextProxy->getAdminManager()->assignToken($admin, $token);
    }

    /**
     * @Given /^this admin can access this event$/
     */
    public function assignEvent(): void
    {
        $admin = $this->adminContextProxy->getStorage()->get('admin');
        $event = $this->adminContextProxy->getStorage()->get('event');

        if (!$admin instanceof Admin) {
            throw new \InvalidArgumentException('Admin not found');
        }

        if (!$event instanceof Event) {
            throw new \InvalidArgumentException('Event not found');
        }

        $this->adminContextProxy->getAdminManager()->assignEvent($admin, $event);
    }
}
