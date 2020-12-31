<?php

namespace Proximum\Vimeet\Application\Query\Package\Option;

use Proximum\Vimeet\Application\View\Package\ProductView;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Domain\ProductAttributedToParticipant\ParticipantWithAttributedProductGetter;

class OptionViewQueryHandler
{
    /** @var CartManager */
    private $cartManager;

    /** @var Merger */
    private $merger;

    /** @var ParticipantWithAttributedProductGetter */
    private $participantWithAttributedProductGetter;

    /** @var \DateTimeInterface */
    private $now;

    public function __construct(
        CartManager $cartManager,
        Merger $merger,
        ParticipantWithAttributedProductGetter $participantWithAttributedProductGetter,
        \DateTimeInterface $now
    ) {
        $this->cartManager = $cartManager;
        $this->merger = $merger;
        $this->participantWithAttributedProductGetter = $participantWithAttributedProductGetter;
        $this->now = $now;
    }

    /**
     * @param OptionViewQuery $optionViewQuery
     *
     * @return ProductView
     */
    public function handle(OptionViewQuery $optionViewQuery): ProductView
    {
        $cart         = $this->cartManager->getCart($optionViewQuery->sheet);
        $selectedPlan = null;
        $included     = 0;

        if (null !== $cart->getPlanRow()) {
            $selectedPlan = $cart->getPlanRow()->getProduct();
        }

        // handle new order
        if ($optionViewQuery->sheet->hasNotCancelledOrders()) {
            $orderMerged  = $this->merger->merge($optionViewQuery->sheet->getNotCancelledOrders());
            $selectedPlan = $orderMerged->getPlan();
        }

        if ($selectedPlan) {
            $includedProduct = $selectedPlan->getIncludedProduct($optionViewQuery->product);

            if ($includedProduct) {
                $included = $includedProduct->getQuantity();
            }
        }

        return new ProductView(
            $optionViewQuery->product->getId(),
            $optionViewQuery->product->getTitle($optionViewQuery->locale),
            $optionViewQuery->product->getUnitPrice(),
            $optionViewQuery->product->getHeading($optionViewQuery->locale),
            $optionViewQuery->product->getDescription($optionViewQuery->locale),
            $optionViewQuery->product->getAddon($optionViewQuery->locale),
            $optionViewQuery->product->getImage(),
            $optionViewQuery->product->getAvailabilityCurrent(),
            $optionViewQuery->product->getAvailabilityMax(),
            $optionViewQuery->product->isOutOfStock(),
            $optionViewQuery->sheet->getEvent()->getMode(),
            $optionViewQuery->sheet->getEvent()->getCurrency(),
            $optionViewQuery->product->getSubjectedToValidationHelp($optionViewQuery->locale),
            $optionViewQuery->product->isSubjectedToValidation(),
            $included,
            $optionViewQuery->product->isBuyable($this->now),
            $this->getParticipantsCompleteNameByAttributedProduct(
                $optionViewQuery->sheet->getParticipantsArray(),
                $optionViewQuery->product
            )
        );
    }

    /**
     * @param Participant[] $participants
     * @param Product       $product
     *
     * @return string[]
     */
    private function getParticipantsCompleteNameByAttributedProduct(array $participants, Product $product): array
    {
        if (!$this->isAttributableButNoLongerBuyable($product)) {
            return [];
        }

        return $this->participantWithAttributedProductGetter->getParticipantsCompleteNameByAttributedProduct(
            $participants,
            $product
        );
    }

    private function isAttributableButNoLongerBuyable(Product $product): bool
    {
        return $product->isAttributable() && !$product->isBuyable($this->now);
    }
}
