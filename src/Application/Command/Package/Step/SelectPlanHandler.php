<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Package\Step;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Package\StepDoneEvent;
use Proximum\Vimeet\Domain\Cart\BuyableObjectResolver;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class SelectPlanHandler
{
    /**
     * @var CartManager
     */
    private $cartManager;

    /**
     * @var BuyableObjectResolver
     */
    private $buyableObjectResolver;

    /**
     * @var DelayedEventDispatcher
     */
    private $eventDispatcher;

    /**
     * SelectPlanHandler constructor.
     *
     * @param CartManager            $cartManager
     * @param BuyableObjectResolver  $buyableObjectResolver
     * @param DelayedEventDispatcher $eventDispatcher
     */
    public function __construct(
        CartManager $cartManager,
        BuyableObjectResolver $buyableObjectResolver,
        DelayedEventDispatcher $eventDispatcher
    ) {
        $this->cartManager           = $cartManager;
        $this->buyableObjectResolver = $buyableObjectResolver;
        $this->eventDispatcher       = $eventDispatcher;
    }

    /**
     * @param SelectPlan $selectPlan
     */
    public function handle(SelectPlan $selectPlan)
    {
        $cart = $this->cartManager->getCart($selectPlan->sheet, $selectPlan->currentStep);

        $previousPlan = $cart->getPlanRow();

        // previous plan different from new selected plan
        if (null !== $previousPlan && $previousPlan->getProduct() !== $selectPlan->plan) {
            $this->cartManager->emptyIncludedProductAttributedToParticipant($cart);
            $cart->clear();
        }

        if (null === $previousPlan || $previousPlan->getProduct() !== $selectPlan->plan) {
            $this->cartManager->deleteCartStep($cart);
            $cart->setProduct($selectPlan->plan, 1);
            $this->cartManager->save($cart);
        }

        $this->buyableObjectResolver->resolveTemplate($selectPlan->sheet);
        $this->buyableObjectResolver->resolvePlan($selectPlan->sheet, $selectPlan->plan);

        $packageStepDone = new StepDoneEvent($selectPlan->sheet);
        $this->eventDispatcher->dispatch(Events::PACKAGE_STEP_DONE, $packageStepDone);
    }
}
