<?php

namespace Proximum\Vimeet\Application\Command\Event\PaymentConditions;

use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;

class UpdateHandler
{
    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * UpdateHandler constructor.
     *
     * @param EventRepositoryInterface $eventRepository
     */
    public function __construct(EventRepositoryInterface $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    /**
     * @param Update $update
     */
    public function handle(Update $update)
    {
        $update->event
            ->getConfiguration()
            ->updatePaymentConditions(
                $update->paymentModes,
                $update->allowDeposit,
                $update->depositUntil,
                $update->minimumForDeposit,
                $update->deposit
            )
        ;

        $this->eventRepository->set($update->event);
    }
}
