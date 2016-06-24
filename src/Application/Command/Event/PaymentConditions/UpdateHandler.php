<?php

/* 
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Event\PaymentConditions;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;

class UpdateHandler
{
    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * UpdateHandler constructor.
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
                $update->allowDeposit,
                $update->depositUntil,
                $update->minimumForDeposit,
                $update->deposit
            )
        ;
        
        $this->eventRepository->set($update->event);
    }
}
