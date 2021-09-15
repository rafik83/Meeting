<?php

namespace Proximum\Vimeet\Application\Command\Package\Step;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Package\StepDoneEvent;
use Proximum\Vimeet\Domain\Cart\BuyableObjectResolver;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Package\Funnel\Step;
use Proximum\Vimeet\Domain\ProductAttributedToParticipant\ProductsAttributedToParticipantRemoveAllBySheet;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class SelectPlanHandler
{
    /** @var CartManager */
    private $cartManager;

    /** @var BuyableObjectResolver */
    private $buyableObjectResolver;

    /** @var DelayedEventDispatcher */
    private $eventDispatcher;

    /** @var ProductsAttributedToParticipantRemoveAllBySheet */
    private $productsAttributedToParticipantRemoveAllBySheet;

    public function __construct(
        CartManager $cartManager,
        BuyableObjectResolver $buyableObjectResolver,
        DelayedEventDispatcher $eventDispatcher,
        ProductsAttributedToParticipantRemoveAllBySheet $productsAttributedToParticipantRemoveAllBySheet
    ) {
        $this->cartManager = $cartManager;
        $this->buyableObjectResolver = $buyableObjectResolver;
        $this->eventDispatcher = $eventDispatcher;
        $this->productsAttributedToParticipantRemoveAllBySheet = $productsAttributedToParticipantRemoveAllBySheet;
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
            $this->productsAttributedToParticipantRemoveAllBySheet->handle($selectPlan->sheet);
            $cart->clear();
        }

        if (null === $previousPlan || $previousPlan->getProduct() !== $selectPlan->plan) {
            $this->cartManager->deleteCartStep($cart);
            $cart->setProduct($selectPlan->plan, 1);
            $this->cartManager->save($cart);
        }

        $this->buyableObjectResolver->resolveTemplate($selectPlan->sheet);
        $this->buyableObjectResolver->resolvePlan($selectPlan->sheet, $selectPlan->plan);

        $packageStepDone = new StepDoneEvent($selectPlan->sheet, Step::TYPE_PLAN);
        $this->eventDispatcher->dispatch(Events::PACKAGE_STEP_DONE, $packageStepDone);
    }
}
