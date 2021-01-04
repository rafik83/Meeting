<?php

namespace Proximum\Vimeet\Application\Command\Unavailability\Mass;

use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassRepositoryInterface;

class DeleteHandler
{
    /**
     * @var MassRepositoryInterface
     */
    private $massRepository;

    /**
     * @var JobQueueInterface
     */
    private $jobQueueAdapter;

    /**
     * @param MassRepositoryInterface $massRepository
     * @param JobQueueInterface       $jobQueueAdapter
     */
    public function __construct(MassRepositoryInterface $massRepository, JobQueueInterface $jobQueueAdapter)
    {
        $this->massRepository = $massRepository;
        $this->jobQueueAdapter = $jobQueueAdapter;
    }

    /**
     * @param Delete $delete
     */
    public function handle(Delete $delete)
    {
        $isBlocking = $delete->mass->isBlocking();
        $event = $delete->mass->getEvent();

        $this->massRepository->remove($delete->mass);

        if ($isBlocking) {
            $this->jobQueueAdapter->aggregateEventUsersFullUnavailability($event);
        }
    }
}
