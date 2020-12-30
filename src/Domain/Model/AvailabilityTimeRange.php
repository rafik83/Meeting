<?php

namespace Proximum\Vimeet\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Plage de disponibilités
 *
 * @see docs/AvailabilityTimeRange.md
 */
class AvailabilityTimeRange
{
    /** @var null|int */
    private $id;

    /** @var Event */
    private $event;

    /**
     * Libellé backoffice
     *
     * @var string
     */
    private $name;

    /** @var \DateTimeInterface */
    private $begin;

    /** @var \DateTimeInterface */
    private $end;

    /** @var ArrayCollection of Product */
    private $products;

    public function __construct(Event $event, string $name, \DateTimeInterface $begin, \DateTimeInterface $end)
    {
        $this->event = $event;
        $this->name = $name;
        $this->begin = $begin;
        $this->end = $end;
        $this->products = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Event
     */
    public function getEvent(): Event
    {
        return $this->event;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getBegin(): \DateTimeInterface
    {
        return $this->begin;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getEnd(): \DateTimeInterface
    {
        return $this->end;
    }

    /**
     * @return Product[]
     */
    public function getProducts(): array
    {
        return $this->products->toArray();
    }

    public function addProduct(Product $product): void
    {
        $this->products->add($product);
    }

    public function removeProduct(Product $product): void
    {
        $this->products->removeElement($product);
    }
}
