<?php

namespace Proximum\Vimeet\Application\Query\PromotionCode\Batch\PromotionCodeGroupList;

class PromotionCodeGroupListView
{
    /** @var int */
    public $id;

    /** @var string */
    public $title;

    /** @var int */
    public $number;

    /** @var string|null */
    public $prefix;

    /** @var int|null */
    public $stock;

    /** @var \DateTimeInterface|null */
    public $validUntil;

    /** @var bool */
    public $canBeUpdatable;

    public function __construct(
        int $id,
        string $title,
        int $number,
        ?string $prefix,
        ?int $stock,
        ?\DateTimeInterface $validUntil,
        bool $canBeUpdatable
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->number = $number;
        $this->prefix = $prefix;
        $this->stock = $stock;
        $this->validUntil = $validUntil;
        $this->canBeUpdatable = $canBeUpdatable;
    }
}
