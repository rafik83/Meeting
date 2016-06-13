<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Package\Step;

use Proximum\Vimeet\Domain\Model\CartRow;
use Proximum\Vimeet\Domain\Repository\CartRowRepositoryInterface;

class SelectPlanHandler
{
    /**
     * @var CartRowRepositoryInterface
     */
    private $cartRowRepository;

    /**
     * @var \DateTimeInterface
     */
    private $datetime;

    /**
     * @param CartRowRepositoryInterface $cartRowRepository
     * @param \DateTimeInterface         $datetime
     */
    public function __construct(CartRowRepositoryInterface $cartRowRepository, \DateTimeInterface $datetime)
    {
        $this->cartRowRepository = $cartRowRepository;
        $this->datetime          = $datetime;
    }

    /**
     * @param SelectPlan $plans
     */
    public function handle(SelectPlan $plans)
    {
        $cartRow = $this->cartRowRepository->findCartRowPlanBySheet($plans->sheet);

        if (null === $cartRow || $cartRow->getProduct() !== $plans->plan) {
            // Delete all Sheet Cart when the plan is selected or has changed
            $this->cartRowRepository->deleteCartRowsBySheet($plans->sheet);

            // Add the selected plan
            $this->cartRowRepository->add(new CartRow($plans->sheet, $plans->plan, 1, $this->datetime));
        }
    }
}
