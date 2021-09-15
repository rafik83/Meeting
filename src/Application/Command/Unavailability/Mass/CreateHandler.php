<?php

namespace Proximum\Vimeet\Application\Command\Unavailability\Mass;

use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassRepositoryInterface;

class CreateHandler
{
    /**
     * @var MassRepositoryInterface
     */
    private $massRepository;

    /** @var JobQueueInterface */
    private $jobQueueAdapter;

    /**
     * @param MassRepositoryInterface $massRepository
     * @param JobQueueInterface       $jobQueueAdapter
     */
    public function __construct(MassRepositoryInterface $massRepository, JobQueueInterface $jobQueueAdapter)
    {
        $this->massRepository  = $massRepository;
        $this->jobQueueAdapter = $jobQueueAdapter;
    }

    /**
     * @param Create $create
     */
    public function handle(Create $create)
    {
        $mass = new Mass(
            $create->event,
            $create->category,
            $create->name,
            $create->begin,
            $create->end,
            $create->blocking,
            $create->dispatch,
            $create->timeSlots,
            $create->types
        );

        foreach ($create->translations as $locale => $translation) {
            $mass->createTranslation($locale, $translation['title'], $translation['description']);
        }

        $this->massRepository->create($mass);

        if (true === $create->blocking) {
            $this->jobQueueAdapter->aggregateEventUsersFullUnavailability($create->event);
            $this->jobQueueAdapter->aggregateAvailableSlot($create->event);
        }
    }
}
