<?php

namespace Proximum\Vimeet\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;

class CartRow
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Sheet
     */
    private $sheet;

    /**
     * @var Product
     */
    private $product;

    /**
     * @var int
     */
    private $quantity = 0;

    /**
     * @var string
     */
    private $serializedProduct;

    /** @var ArrayCollection of CartRowParticipant */
    private $cartRowParticipants;

    /**
     * @param Sheet   $sheet
     * @param Product $product
     * @param int     $quantity
     */
    public function __construct(Sheet $sheet, Product $product, $quantity)
    {
        $this->sheet = $sheet;
        $this->quantity = $quantity;
        $this->product = $product;
        $this->serializedProduct = $product->getSerializedData();
        $this->cartRowParticipants = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Sheet
     */
    public function getSheet()
    {
        return $this->sheet;
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Set product
     *
     * @param Product $product
     *
     * @return CartRow
     */
    public function setProduct(Product $product)
    {
        $this->product           = $product;
        $this->serializedProduct = $product->getSerializedData();

        return $this;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @return bool
     */
    public function isNegative()
    {
        return true === ($this->quantity < 0);
    }

    /**
     * Set quantity
     *
     * @param int $quantity
     *
     * @return CartRow
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Add quantity
     *
     * @param int $quantity
     *
     * @return CartRow
     */
    public function addQuantity($quantity)
    {
        $this->quantity += $quantity;

        return $this;
    }

    /**
     * @return string
     */
    public function getSerializedProduct()
    {
        return $this->serializedProduct;
    }

    /**
     * @param CartRowParticipant $cartRowParticipant
     */
    public function addCartRowParticipant(CartRowParticipant $cartRowParticipant): void
    {
        $this->cartRowParticipants->add($cartRowParticipant);
    }

    /**
     * @return Participant[]
     */
    public function getParticipants(): array
    {
        $participants = [];

        /** @var CartRowParticipant $cartRowParticipant */
        foreach ($this->cartRowParticipants as $cartRowParticipant) {
            $participants[] = $cartRowParticipant->getParticipant();
        }

        return $participants;
    }
}
