<?php

namespace Proximum\Vimeet\Application\Command\Admin;

use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;

class DeleteHandler
{
    /** @var AdminRepositoryInterface */
    private $adminRepository;

    /** @var \DateTimeInterface */
    private $datetime;

    public function __construct(
        AdminRepositoryInterface $adminRepository,
        \DateTimeInterface $datetime
    ) {
        $this->adminRepository = $adminRepository;
        $this->datetime = $datetime;
    }

    public function handle(Delete $delete): void
    {
        $delete->admin->setDeletedAt($this->datetime);
        $this->adminRepository->set($delete->admin);
    }
}
