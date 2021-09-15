<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Package;

use Proximum\Vimeet\Application\Command\Package\Step\OptionRow;
use Proximum\Vimeet\Application\Command\Package\Step\SelectOptions;
use Proximum\Vimeet\Domain\Cart\BuyableObjectResolver;
use Proximum\Vimeet\Domain\Cart\Cart;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Domain\Package\Product\ConflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode;
use Proximum\Vimeet\Domain\Package\Product\QuantityMaxGuesser;
use Proximum\Vimeet\Domain\Package\Product\TemplateProductGuesser;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class OptionsValidator extends ConstraintValidator
{
    /** @var QuantityMaxGuesser */
    private $quantityMaxGuesser;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var ConflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode */
    private $conflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode;

    /** @var Merger */
    private $merger;

    /** @var TemplateProductGuesser */
    private $templateProductGuesser;

    /** @var CartManager */
    private $cartManager;

    public function __construct(
        QuantityMaxGuesser $quantityMaxGuesser,
        ConflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode $conflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode,
        TemplateProductGuesser $templateProductGuesser,
        \DateTimeInterface $dateTime,
        Merger $merger,
        CartManager $cartManager
    ) {
        $this->quantityMaxGuesser = $quantityMaxGuesser;
        $this->conflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode = $conflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode;
        $this->dateTime = $dateTime;
        $this->merger = $merger;
        $this->templateProductGuesser = $templateProductGuesser;
        $this->cartManager = $cartManager;
    }

    /**
     * @param SelectOptions $selectOptions
     * @param Constraint    $constraint
     */
    public function validate($selectOptions, Constraint $constraint): void
    {
        $order   = null;
        $cart    = $this->cartManager->getCart($selectOptions->sheet);
        $options = $this->getAvailableOptionsIndexedByProductId($selectOptions->sheet);

        if ($selectOptions->sheet->hasNotCancelledOrders()) {
            $order = $this->merger->merge($selectOptions->sheet->getNotCancelledOrders());
        }

        foreach ($selectOptions->options as $optionId => $optionRow) {
            $this->validateOption($options, $selectOptions, $optionRow, $optionId, $cart, $order);
        }
    }

    private function validateOption(
        array &$options,
        SelectOptions $selectOptions,
        OptionRow $optionRow,
        int $optionId,
        Cart $cart,
        ?Order $order
    ): void {
        if (!$this->validateProductIsAvailable($options, $optionId)) {
            return;
        }

        $option = $options[$optionId];
        $quantity = $optionRow->getQuantity();

        $this->validateNoConflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode(
            $selectOptions->sheet,
            $option,
            $quantity,
            $order
        );

        $this->validateProductNotDeletable($order, $option, $quantity);

        $this->validateQuantityMinPayableOption($selectOptions->sheet, $option, $quantity, $cart, $order);

        $quantityMax = $this->quantityMaxGuesser->getMaxByProduct($selectOptions->sheet, $option);
        $this->validateQuantity(
            $quantity,
            $quantityMax,
            $option
        );
    }

    private function validateProductIsAvailable(array &$options, $optionId): bool
    {
        if (!isset($options[$optionId]) && !$options[$optionId] instanceof Product) {
            $this
                ->context
                ->buildViolation('package.product.notAvailable')
                ->addViolation();

            return false;
        }

        return true;
    }

    /**
     * @param int     $quantity
     * @param float   $quantityMax int or INF
     * @param Product $option
     */
    private function validateQuantity(
        int $quantity,
        float $quantityMax,
        Product $option
    ): void {
        if ($quantity < 0 || $quantity > $quantityMax) {
            $this
                ->context
                ->buildViolation(
                    $option->isAttributable()
                        ? 'package.product.selectedParticipantsQuantityNotMatch'
                        : 'package.product.quantityNotMatch'
                )
                ->setParameters(['%min%' => 0, '%max%' => $quantityMax])
                ->atPath($this->getPathForOption($option))
                ->addViolation();
        }
    }

    private function validateQuantityMinPayableOption(
        Sheet $sheet,
        Product $option,
        int $quantity,
        Cart $cart,
        ?Order $order
    ): void {
        $linkedProduct = $this->templateProductGuesser->guessProduct($sheet, $option);

        if (null === $linkedProduct) {
            return;
        }

        $quantityMin = $this->resolveQuantityMinWithLinkedProduct($quantity, $linkedProduct, $cart, $order);

        if ($quantityMin < BuyableObjectResolver::PAYABLE_OPTION_QUANTITY) {
            $this
                ->context
                ->buildViolation('package.product.quantityMinPayableOption')
                ->atPath($this->getPathForOption($option))
                ->addViolation();
        }
    }

    private function validateNoConflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode(
        Sheet $sheet,
        Product $option,
        int $quantity,
        ?Order $order
    ): void {
        if ($this->conflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode->hasConflict(
            $sheet,
            $option,
            $quantity,
            $order
        )) {
            $this
                ->context
                ->buildViolation('package.product.quantityMinPromotionCode')
                ->atPath($this->getPathForOption($option))
                ->addViolation();
        }
    }

    private function validateProductNotDeletable(?Order $order, Product $option, int $quantity): void
    {
        if (!$order instanceof Order || $option->isDeletable($this->dateTime)) {
            return;
        }

        $orderRow = $order->getRowForProduct($option);

        if ($orderRow instanceof Order\Row && $quantity < $orderRow->getQuantity()) {
            $this
                ->context
                ->buildViolation('package.product.productNotDeletable')
                ->atPath($this->getPathForOption($option))
                ->addViolation();
        }
    }

    /**
     * @return Product[] indexed by Product id
     */
    private function getAvailableOptionsIndexedByProductId(Sheet $sheet): array
    {
        $options = $sheet->getPackage()->getAvailablesOptions($this->dateTime);

        $optionsIndexedByProductId = [];

        foreach ($options as $option) {
            $optionsIndexedByProductId[$option->getId()] = $option;
        }

        return $optionsIndexedByProductId;
    }

    /**
     * Resolve minimum quantity by handling included product in plan
     */
    private function resolveQuantityMinWithLinkedProduct(
        int $quantity,
        Product $linkedProduct,
        Cart $cart,
        ?Order $order
    ): int {
        $plan = $this->getPlan($cart, $order);

        if (null === $plan) {
            return $quantity;
        }

        $includedProduct = $plan->getIncludedProduct($linkedProduct);

        if (null === $includedProduct) {
            return $quantity;
        }

        return $quantity + $includedProduct->getQuantity();
    }

    private function getPlan(Cart $cart, Order $order = null): ?Product
    {
        if (null !== $order) {
            return $order->getPlan();
        }

        if ($planRow = $cart->getPlanRow()) {
            return $planRow->getProduct();
        }

        return null;
    }

    private function getPathForOption(Product $option): string
    {
        return sprintf('%s.%s', $option->getId(), $option->isAttributable() ? 'participants' : 'quantity');
    }
}
