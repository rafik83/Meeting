<?php


namespace Proximum\Vimeet\Application\Command\Admin;

use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;

class RestoreHandler
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

    public function handle(Restore $restore): void
    {
        $restore->admin->restore();
        $this->adminRepository->set($restore->admin);
    }
}
