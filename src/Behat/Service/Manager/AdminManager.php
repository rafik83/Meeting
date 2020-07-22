<?php

namespace Proximum\Vimeet\Behat\Service\Manager;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;

class AdminManager
{
    /** @var AdminRepositoryInterface */
    private $adminRepository;

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
    public function create(
        string $email = null,
        string $role
    ): Admin {
        if (null === $email) {
            $email = sprintf('%s@example.net', uniqid());
        }

        $admin = new Admin(
            $email,
            '221e7e2646071ec7c0f39602f8a900d6516907ed',
            '$argon2i$v=19$m=65536,t=4,p=1$bElLS0RnV1EvbDhPLmN1eQ$oUWZgn3mFLvergOyQHLFX/Vm8pstN/pDVdfh8vU5D/0',
            'fr',
            'Bob',
            'Teemiv',
            $role,
            new \DateTime()
        ); // password: Vimeet_admin1

        $this->adminRepository->add($admin);

        return $admin;
    }

    public function assignEvent(Admin $admin, Event $event): void
    {
        $admin->addEvent($event);
        $this->adminRepository->set($admin);
    }
}
