<?php

namespace Proximum\Vimeet\Application\Command\Admin;

use Proximum\Vimeet\Application\Exception\Admin\AdminLinkedToPlannerJobException;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\PlannerJobRepositoryInterface;

class DeleteHandler
{
    /** @var AdminRepositoryInterface */
    private $adminRepository;

    /** @var PlannerJobRepositoryInterface */
    private $plannerJobRepository;

    public function __construct(
        AdminRepositoryInterface $adminRepository,
        PlannerJobRepositoryInterface $plannerJobRepository
    ) {
        $this->adminRepository = $adminRepository;
        $this->plannerJobRepository = $plannerJobRepository;
    }

    public function handle(Delete $delete): void
    {
        if ($this->plannerJobRepository->countByAdmin($delete->admin) > 0) {
            throw new AdminLinkedToPlannerJobException();
        }

        $this->adminRepository->remove($delete->admin);
    }
}
