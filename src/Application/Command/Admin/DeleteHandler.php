<?php

namespace Proximum\Vimeet\Application\Command\Admin;

use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;

class DeleteHandler
{
    /** @var AdminRepositoryInterface */
    private $adminRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        AdminRepositoryInterface $adminRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->adminRepository = $adminRepository;
        $this->dateTime = $dateTime;
    }

    public function handle(Delete $delete): void
    {
        $delete->admin->setDeletedAt($this->dateTime);
        $this->adminRepository->set($delete->admin);
    }
}
