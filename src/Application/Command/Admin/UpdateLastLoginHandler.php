<?php

namespace Proximum\Vimeet\Application\Command\Admin;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;

class UpdateLastLoginHandler
{
    /** @var AdminRepositoryInterface */
    private $adminRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(AdminRepositoryInterface $adminRepository, \DateTimeInterface $dateTime)
    {
        $this->adminRepository = $adminRepository;
        $this->dateTime = $dateTime;
    }

    /**
     * @param UpdateLastLogin $updateLastLogin
     */
    public function handle(UpdateLastLogin $updateLastLogin)
    {
        $admin = $this->adminRepository->findByEmail($updateLastLogin->email);

        if ($admin instanceof Admin) {
            $admin->setLastLoginAt($this->dateTime);
            $this->adminRepository->set($admin);
        }
    }
}
