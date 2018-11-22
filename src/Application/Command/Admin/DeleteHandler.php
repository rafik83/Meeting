<?php

namespace Proximum\Vimeet\Application\Command\Admin;

use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;

class DeleteHandler
{
    /** @var AdminRepositoryInterface */
    private $adminRepository;

    public function __construct(AdminRepositoryInterface $adminRepository)
    {
        $this->adminRepository = $adminRepository;
    }

    public function handle(Delete $delete): void
    {
        $this->adminRepository->remove($delete->admin);
    }
}
