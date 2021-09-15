<?php


namespace Proximum\Vimeet\Application\Command\Admin;

use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;

class RestoreHandler
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

    public function handle(Restore $restore): void
    {
        $restore->admin->restore();
        $this->adminRepository->set($restore->admin);
    }
}
