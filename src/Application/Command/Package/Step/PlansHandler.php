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

class PlansHandler
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
     * @param Plans $plans
     */
    public function handle(Plans $plans)
    {
        $cartRow = $this->cartRowRepository->findCartRowPlanBySheet($plans->sheet);

        if (null === $cartRow || $cartRow->getProduct() !== $plans->plan) {
            // remove if other plan selected previously
            if ($cartRow) {
                $this->cartRowRepository->delete($cartRow);
            }

            $this->cartRowRepository->add(new CartRow(
                $plans->sheet,
                $plans->plan,
                1,
                $this->datetime
            ));
        }
    }
}
