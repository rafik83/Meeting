<?php

namespace Proximum\Vimeet\Application\Command\Package\Step;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Package\StepDoneEvent;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Domain\Package\Funnel\Step;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class SelectOptionsHandler
{
    /** @var CartManager */
    private $cartManager;

    /** @var \DateTimeInterface */
    private $now;

    /** @var Merger */
    private $merger;

    /** @var DelayedEventDispatcher */
    private $eventDispatcher;

    public function __construct(
        CartManager $cartManager,
        \DateTimeInterface $now,
        Merger $merger,
        DelayedEventDispatcher $eventDispatcher
    ) {
        $this->cartManager = $cartManager;
        $this->now = $now;
        $this->merger = $merger;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function handle(SelectOptions $selectOptions): void
    {
        $sheet = $selectOptions->sheet;
        $package = $sheet->getPackage();
        $cart = $this->cartManager->getCart($sheet, $selectOptions->currentStep);

        /** @var Product[] $options */
        $optionsById = [];

        foreach ($package->getAvailablesOptions($this->now) as $option) {
            $optionsById[$option->getId()] = $option;
        }

        $cart->clearOptions();

        $orderMerged = null;
        if ($sheet->hasNotCancelledOrders()) {
            $orderMerged = $this->merger->merge($sheet->getNotCancelledOrders());
        }

        $attributableOptionsIncludedByProductId = $this->cartManager->getAttributableOptionsIncludedByProductId(
            $cart,
            $orderMerged
        );

        foreach ($selectOptions->options as $id => $optionRow) {
            $this->cartManager->updateOptionsQuantity(
                $cart,
                $optionRow,
                $optionsById[$id],
                $orderMerged,
                $attributableOptionsIncludedByProductId
            );
        }

        $this->cartManager->save($cart);

        $packageStepDone = new StepDoneEvent($selectOptions->sheet, Step::TYPE_OPTIONS);
        $this->eventDispatcher->dispatch(Events::PACKAGE_STEP_DONE, $packageStepDone);
    }
}
