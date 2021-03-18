<?php

namespace Proximum\Vimeet\Application\Components\Package;

use Proximum\Vimeet\Application\Query\Package\Participant\ParticipantProductViewQuery;
use Proximum\Vimeet\Application\Query\Package\Participant\ParticipantProductViewQueryHandler;
use Proximum\Vimeet\Domain\Cart\Cart;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;

class ProductByParticipantGetter
{
    /** @var ProductByParticipantCartGetter */
    private $productByParticipantCartGetter;

    /** @var ParticipantProductViewQueryHandler */
    private $participantProductViewQueryHandler;

    public function __construct(
        ProductByParticipantCartGetter $productByParticipantCartGetter,
        ParticipantProductViewQueryHandler $participantProductViewQueryHandler
    ) {
        $this->productByParticipantCartGetter = $productByParticipantCartGetter;
        $this->participantProductViewQueryHandler = $participantProductViewQueryHandler;
    }

    /**
     * This method returns the product of type participant indexed by the participant id
     * The participant product can be null
     * If there is only one product, this product is taken for all the participant
     *
     * @param Cart $cart
     *
     * @return array of participantId => Product
     */
    public function handle(Cart $cart): array
    {
        $productIndexedByParticipantId = $this->productByParticipantCartGetter->getFromCart($cart);
        $sheet = $cart->getSheet();
        $participantProducts = $sheet->getPackage()->getParticipants();
        $participants = $sheet->getParticipantsArray();

        if (!$this->areThereAnyParticipantsWithoutProduct($participants, $productIndexedByParticipantId)) {
            return $productIndexedByParticipantId;
        }

        $this->setIncludedProductToParticipantsWithoutProductIfThereAreAnyLeft(
            $sheet,
            $participants,
            $participantProducts,
            $productIndexedByParticipantId
        );

        $this->setProductToParticipantsWithoutProductIfOnlyOneProductInPackage(
            $participants,
            $participantProducts,
            $productIndexedByParticipantId
        );

        $this->addMissingParticipantToProductIndexedByParticipantId($participants, $productIndexedByParticipantId);

        return $productIndexedByParticipantId;
    }

    /**
     * @param array $participants
     * @param array $productIndexedByParticipantId
     */
    private function addMissingParticipantToProductIndexedByParticipantId(
        array &$participants,
        array &$productIndexedByParticipantId
    ): void {
        foreach ($participants as $participant) {
            if (!isset($productIndexedByParticipantId[$participant->getId()])) {
                $productIndexedByParticipantId[$participant->getId()] = null;
            }
        }
    }

    /**
     * Set included product to participants without product if there are any left
     *
     * @param Sheet         $sheet
     * @param Participant[] $participants
     * @param Product[]     $participantProducts
     * @param array         $productIndexedByParticipantId array of participantId => Product
     */
    private function setIncludedProductToParticipantsWithoutProductIfThereAreAnyLeft(
        Sheet $sheet,
        array &$participants,
        array &$participantProducts,
        array &$productIndexedByParticipantId
    ): void {
        $participantProductViews = $this->participantProductViewQueryHandler->handle(
            new ParticipantProductViewQuery($sheet)
        );

        $participantProductsIndexedById = [];

        foreach ($participantProducts as $product) {
            $participantProductsIndexedById[$product->getId()] = $product;
        }

        $remainingQuantityIncludedIndexedByProductId = [];

        foreach ($participantProductViews as $participantProductView) {
            $remainingQuantityIncludedIndexedByProductId[$participantProductView->id] = $participantProductView->remainingQuantityIncluded;
        }

        foreach ($participants as $participant) {
            if (!isset($productIndexedByParticipantId[$participant->getId()])) {
                foreach ($remainingQuantityIncludedIndexedByProductId as $productId => $remainingQuantityIncluded) {
                    if ($remainingQuantityIncluded > 0 && isset($participantProductsIndexedById[$productId])) {
                        // assign included product to participant
                        $productIndexedByParticipantId[$participant->getId()] = $participantProductsIndexedById[$productId];

                        // remove 1 from remaining quantity for this product
                        --$remainingQuantityIncludedIndexedByProductId[$productId];
                    }
                }
            }
        }
    }

    /**
     * Set product to all participants without product if there only one participant product in the package
     *
     * @param Participant[] $participants
     * @param Product[]     $participantProducts
     * @param array         $productIndexedByParticipantId array of participantId => Product
     */
    private function setProductToParticipantsWithoutProductIfOnlyOneProductInPackage(
        array &$participants,
        array &$participantProducts,
        array &$productIndexedByParticipantId
    ): void {
        if (1 !== \count($participantProducts)) {
            return;
        }

        $product = reset($participantProducts);

        if (false !== $product) {
            foreach ($participants as $participant) {
                if (!isset($productIndexedByParticipantId[$participant->getId()])) {
                    $productIndexedByParticipantId[$participant->getId()] = $product;
                }
            }
        }
    }

    /**
     * @param Participant[] $participants
     * @param array         $productIndexedByParticipantId array of participantId => Product
     *
     * @return bool
     */
    private function areThereAnyParticipantsWithoutProduct(
        array &$participants,
        array &$productIndexedByParticipantId
    ): bool {
        foreach ($participants as $participant) {
            if (!isset($productIndexedByParticipantId[$participant->getId()])) {
                return true;
            }
        }

        return false;
    }
}
