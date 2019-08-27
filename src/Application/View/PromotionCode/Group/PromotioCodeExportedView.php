<?php

namespace Proximum\Vimeet\Application\View\PromotionCode\Group;

class PromotioCodeExportedView
{
    /** @var string */
    public $code;

    /** @var string */
    public $title;

    /** @var \DateTimeInterface|null */
    public $validUntil;

    /** @var int|null */
    public $stock;

    public function __construct(string $code, string $title, ?\DateTimeInterface $validUntil, ?int $stock)
    {
        $this->code = $code;
        $this->title = $title;
        $this->validUntil = $validUntil;
        $this->stock = $stock;
    }
}
