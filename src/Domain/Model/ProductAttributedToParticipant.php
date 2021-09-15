<?php

namespace Proximum\Vimeet\Domain\Model;

use Proximum\Vimeet\Domain\Model\Order\Row;

/**
 * Link between Product attributable and a Participant
 */
class ProductAttributedToParticipant
{
    /** @var Product */
    private $product;

    /** @var Participant */
    private $participant;

    /** @var \DateTimeInterface */
    private $createdAt;

    /** @var Row|null */
    private $orderRow;

    public function __construct(
        Product $product,
        Participant $participant,
        \DateTimeInterface $createdAt,
        Row $orderRow = null
    ) {
        $this->product = $product;
        $this->participant = $participant;
        $this->createdAt = $createdAt;
        $this->orderRow = $orderRow;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function getParticipant(): Participant
    {
        return $this->participant;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getOrderRow(): ?Row
    {
        return $this->orderRow;
    }
}
