<?php

namespace Proximum\Vimeet\Domain\Cart;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Domain\Model\CartRow;
use Proximum\Vimeet\Domain\Model\CartRowParticipant;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\PromotionCode;
use Proximum\Vimeet\Domain\Model\PromotionCodeRow;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Promotion\Exception\PromotionCodeAlreadyExistException;
use Proximum\Vimeet\Domain\Promotion\Exception\PromotionCodeConflictException;
use Proximum\Vimeet\Domain\Promotion\Exception\PromotionCodeException;
use Proximum\Vimeet\Domain\Promotion\Exception\PromotionCodeNegativeRowException;
use Proximum\Vimeet\Domain\Promotion\Exception\PromotionCodeNotUsedException;
use Proximum\Vimeet\Domain\Promotion\Exception\PromotionCodeOutDatedException;
use Proximum\Vimeet\Domain\Promotion\Exception\PromotionCodeSoldOutException;

class Cart
{
    /** @var Sheet */
    private $sheet;

    /** @var ArrayCollection of CartRow */
    private $rows;

    /** @var ArrayCollection of PromotionCodeRow */
    private $promotionCodeRows;

    /** @var int */
    private $currentStep;

    /**
     * @param Sheet              $sheet
     * @param CartRow[]          $rows
     * @param PromotionCodeRow[] $promotionRows
     * @param int                $currentStep
     */
    public function __construct(Sheet $sheet, array $rows, array $promotionRows, $currentStep = null)
    {
        $this->sheet             = $sheet;
        $this->rows              = new ArrayCollection($rows);
        $this->promotionCodeRows = new ArrayCollection($promotionRows);
        $this->currentStep       = $currentStep;
    }

    /**
     * @return Sheet
     */
    public function getSheet(): Sheet
    {
        return $this->sheet;
    }

    /**
     * @param Product $product
     * @param int     $quantity
     * @param array   $participants
     *
     * @return Cart
     */
    public function setProduct(Product $product, int $quantity, array $participants = []): Cart
    {
        if ($this->hasProduct($product)) {
            $row = $this->getRow($product);

            if (0 === $quantity) {
                $this->rows->removeElement($row);
            } else {
                $row->setProduct($product)->setQuantity($quantity);

                if (true === $product->isAttributable()) {
                    foreach ($participants as $participant) {
                        $row->addCartRowParticipant(new CartRowParticipant($row, $participant));
                    }
                }
            }
        } elseif (0 !== $quantity) {
            $cartRow = new CartRow($this->sheet, $product, $quantity);

            if (true === $product->isAttributable()) {
                foreach ($participants as $participant) {
                    $cartRow->addCartRowParticipant(new CartRowParticipant($cartRow, $participant));
                }
            }

            $this->rows[] = $cartRow;
        }

        return $this;
    }

    /**
     * @return Product\ProductIncluded[]
     */
    public function getIncludedParticipantProducts(): array
    {
        if (null === $this->getPlanRow()) {
            return [];
        }

        return $this->getPlanRow()->getProduct()->getIncludedParticipantProducts();
    }

    /**
     * @return Product\ProductIncluded[]
     */
    public function getIncludedAttributableOptionProducts(): array
    {
        if (null === $this->getPlanRow()) {
            return [];
        }

        return $this
            ->getPlanRow()
            ->getProduct()
            ->getIncludedAttributableOptionProducts()
        ;
    }

    /**
     * Get how many participant are included.
     *
     * @return int
     *
     * @deprecated
     */
    public function getIncludedParticipantQuantity()
    {
        return array_reduce($this->getRows(), function ($carry, CartRow $row) {
            return $carry + $row->getProduct()->getIncludedParticipantQuantity();
        }, 0);
    }

    /**
     * @return null|CartRow
     */
    public function getPlanRow()
    {
        /** @var CartRow $cartRow */
        foreach ($this->rows as $cartRow) {
            if ($cartRow->getProduct()->isPlan()) {
                return $cartRow;
            }
        }

        return null;
    }

    /**
     * @return null|CartRow
     *
     * @deprecated use getParticipantRows()
     */
    public function getParticipantRow()
    {
        foreach ($this->rows as $cartRow) {
            if ($cartRow->getProduct()->isParticipant()) {
                return $cartRow;
            }
        }

        return null;
    }

    /**
     * @return CartRow[]
     */
    public function getParticipantRows(): array
    {
        return array_filter($this->getRows(), static function (CartRow $cartRow) {
            return $cartRow->getProduct()->isParticipant();
        });
    }

    public function getPlanningRow(): ?CartRow
    {
        foreach ($this->rows as $cartRow) {
            if ($cartRow->getProduct()->isPlanning()) {
                return $cartRow;
            }
        }

        return null;
    }

    /**
     * @return ArrayCollection CartRow[]
     */
    public function getOptionsRow()
    {
        return $this->rows->filter(static function (CartRow $cartRow) {
            return $cartRow->getProduct()->isOption();
        });
    }

    /**
     * @return CartRow[]
     */
    public function getOptionsRowArray(): array
    {
        return $this->getOptionsRow()->toArray();
    }

    /**
     * @param Product $product
     *
     * @return bool
     */
    public function hasProduct(Product $product): bool
    {
        return $this->rows->exists(static function ($key, CartRow $cartRow) use ($product) {
            return $cartRow->getProduct() === $product;
        });
    }

    /**
     * @param PromotionCode $promotionCode
     *
     * @return bool
     */
    public function hasPromotionCode(PromotionCode $promotionCode): bool
    {
        return $this->promotionCodeRows->exists(
            static function ($key, PromotionCodeRow $promotionCodeRow) use ($promotionCode) {
                return $promotionCodeRow->getPromotionCode() === $promotionCode;
            });
    }

    /**
     * @param Product $product
     *
     * @return CartRow|null
     */
    public function getCartRowForProduct(Product $product): ?CartRow
    {
        foreach ($this->rows as $cartRow) {
            if ($cartRow->getProduct() === $product) {
                return $cartRow;
            }
        }

        return null;
    }

    /**
     * @param Product $product
     *
     * @return false|CartRow
     */
    public function getRow(Product $product)
    {
        return $this->rows->filter(static function (CartRow $cartRow) use ($product) {
            return $cartRow->getProduct() === $product;
        })->first();
    }

    /**
     * @return CartRow[]
     */
    public function getRows(): array
    {
        return $this->rows->toArray();
    }

    /**
     * @return PromotionCodeRow[]
     */
    public function getPromotionCodeRows(): array
    {
        return $this->promotionCodeRows->toArray();
    }

    /**
     * Clear the cart
     */
    public function clear(): void
    {
        $this->rows->clear();
    }

    /**
     * Clear the cart
     */
    public function clearOptions(): void
    {
        foreach ($this->getOptionsRow() as $row) {
            $this->rows->removeElement($row);
        }
    }

    /**
     * @return null|int
     */
    public function getCurrentStep(): ?int
    {
        return $this->currentStep;
    }

    /**
     * @param int $currentStep
     *
     * @return Cart
     */
    public function setCurrentStep($currentStep)
    {
        $this->currentStep = $currentStep;

        return $this;
    }

    /**
     * @return float
     */
    public function getTotal()
    {
        $rows = $this->rows->toArray();

        return empty($rows) ? 0 : array_reduce(
            $rows,
            static function ($carry, CartRow $row) {
                return $carry + ($row->getQuantity() * $row->getProduct()->getUnitPrice());
            },
            0
        );
    }

    /**
     * @param PromotionCode      $promotionCode
     * @param \DateTimeInterface $dateTime
     *
     * @throws PromotionCodeException
     *
     * @return Cart
     */
    public function apply(PromotionCode $promotionCode, \DateTimeInterface $dateTime)
    {
        if ($promotionCode->isOutDated($dateTime)) {
            throw new PromotionCodeOutDatedException();
        }

        if ($promotionCode->isSoldOut()) {
            throw new PromotionCodeSoldOutException();
        }

        if ($this->hasPromotionCode($promotionCode)) {
            throw new PromotionCodeAlreadyExistException();
        }

        if (!$this->cartRowProductInPromotionCode($promotionCode)) {
            throw new PromotionCodeNotUsedException();
        }

        if ($this->isPromotionHaveConflict($promotionCode)) {
            throw new PromotionCodeConflictException();
        }

        if (!$this->isCartRowPositive($promotionCode)) {
            throw new PromotionCodeNegativeRowException();
        }

        $this->promotionCodeRows->add(new PromotionCodeRow($this->sheet, $promotionCode));

        return $this;
    }

    /**
     * @param PromotionCode $promotionCode
     *
     * @return bool
     */
    public function cartRowProductInPromotionCode(PromotionCode $promotionCode)
    {
        foreach ($promotionCode->getPromotions() as $promotion) {
            if (null !== $this->getCartRowForProduct($promotion->getProduct())) {
                return true;
            }
        }

        return false;
    }

    /**
     * Conflict when two promotion code offer promotion on the same product
     *
     * @param PromotionCode $promotionCode
     *
     * @return bool
     */
    public function isPromotionHaveConflict(PromotionCode $promotionCode)
    {
        foreach ($promotionCode->getPromotions() as $promotion) {
            foreach ($this->getPromotionCodeRows() as $promotionCodeRow) {
                if ($promotionCodeRow->getPromotionCode()->hasPromotion($promotion->getProduct())) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param PromotionCode $promotionCode
     *
     * @return bool
     */
    public function isCartRowPositive(PromotionCode $promotionCode): bool
    {
        foreach ($promotionCode->getPromotions() as $promotion) {
            if ($cartRow = $this->getCartRowForProduct($promotion->getProduct())) {
                if (!$cartRow->isNegative()) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get product discount for a specific promotion code
     *
     * @param PromotionCode $promotionCode
     * @param Product       $product
     *
     * @return float
     */
    public function getDiscountForProduct(PromotionCode $promotionCode, Product $product): float
    {
        $cartRow = $this->getCartRowForProduct($product);

        if (null === $cartRow) {
            return 0;
        }

        $total = 0;

        foreach ($promotionCode->getPromotions() as $promotion) {
            $total += $promotion->getDiscountAmountForProduct($product, $cartRow->getQuantity());
        }

        return $total;
    }

    /**
     * Get total discount for a specific promotion code
     *
     * @param PromotionCode $promotionCode
     *
     * @return float|int
     */
    public function getDiscount(PromotionCode $promotionCode)
    {
        $total = 0;

        foreach ($promotionCode->getPromotions() as $promotion) {
            $product = $promotion->getProduct();
            $total += $this->getDiscountForProduct($promotionCode, $product);
        }

        return $total;
    }

    /**
     * Get total discount of all promotion code on the cart
     *
     * @return float|int
     */
    public function getTotalDiscount()
    {
        $total = 0;
        foreach ($this->getPromotionCodeRows() as $promotionCodeRow) {
            $total += $this->getDiscount($promotionCodeRow->getPromotionCode());
        }

        return $total;
    }

    /**
     * @param CartRow $cartRow
     */
    public function removeRow(CartRow $cartRow)
    {
        $this->rows->removeElement($cartRow);
    }

    /**
     * Merge and resolve cart row quantity and order merged row quantity
     *
     * @param Product    $product
     * @param null|Order $order
     *
     * @return int
     */
    public function getOrderCartQuantity(Product $product, Order $order = null)
    {
        $mergedQuantity = 0;
        $cartRow        = $this->getCartRowForProduct($product);

        // handle first order
        if (null !== $cartRow) {
            $mergedQuantity = $cartRow->getQuantity();
        }

        // handle new order
        if (null !== $order && $product = $order->getRowForProduct($product)) {
            $orderQuantity  = $product->getQuantity();
            $mergedQuantity = $orderQuantity;

            if (null !== $cartRow) {
                $mergedQuantity = $orderQuantity + $cartRow->getQuantity();
            }
        }

        return $mergedQuantity;
    }

    public function getAbsoluteProductsQuantity(): int
    {
        $quantity = 0;
        foreach ($this->rows as $cartRow) {
            $quantity += abs($cartRow->getQuantity());
        }

        return $quantity;
    }

    public function hasProducts(): bool
    {
        return !empty($this->rows);
    }
}
