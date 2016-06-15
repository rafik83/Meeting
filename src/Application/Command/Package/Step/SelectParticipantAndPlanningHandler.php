<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Package\Step;

use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\CartRowRepositoryInterface;

class SelectParticipantAndPlanningHandler
{
    /**
     * @var CartRowRepositoryInterface
     */
    private $cartRowRepository;

    /**
     * @var CartManager
     */
    private $cartManager;

    /**
     * @var \DateTimeInterface
     */
    private $datetime;

    /**
     * @param CartRowRepositoryInterface $cartRowRepository
     * @param CartManager                $cartManager
     * @param \DateTimeInterface         $datetime
     */
    public function __construct(CartRowRepositoryInterface $cartRowRepository, CartManager $cartManager, \DateTimeInterface $datetime)
    {
        $this->cartRowRepository = $cartRowRepository;
        $this->cartManager       = $cartManager;
        $this->datetime          = $datetime;
    }

    /**
     * @param SelectParticipantAndPlanning $selectParticipantAndPlanning
     */
    public function handle(SelectParticipantAndPlanning $selectParticipantAndPlanning)
    {
        $sheet   = $selectParticipantAndPlanning->sheet;
        $package = $sheet->getPackage();

        $cart = $this->cartManager->getCart($sheet);
        $cart->resolveParticipantsQuantity();

        if ($package && $package->getPlanning()) {
            $cart->setProduct($package->getPlanning(), $selectParticipantAndPlanning->planningQuantity);
        }

        $this->cartManager->save($cart);
    }
}
