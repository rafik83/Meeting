<?php


namespace Proximum\Vimeet\Application\Command\Admin;

use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;

class UnDeleteHandler
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

    public function handle(UnDelete $unDelete): void
    {
        $unDelete->admin->setDeletedAt(null);
        $this->adminRepository->set($unDelete->admin);
    }
}
