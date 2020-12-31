<?php

namespace Proximum\Vimeet\Application\View\Package;

class PlanView
{
    /** @var int */
    public $id;

    /** @var string */
    public $title;

    /** @var float */
    public $price;

    /** @var string */
    public $heading;

    /** @var null|string */
    public $description;

    /** @var null|string */
    public $image;

    /** @var string */
    public $vatMode;

    /** @var string */
    public $currency;

    /** @var FeatureView[] */
    public $features;

    /** @var bool */
    public $isOutOfStock;

    /** @var null|string */
    public $addon;

    public function __construct(
        int $id,
        string $title,
        float $price,
        bool $isOutOfStock,
        string $heading,
        ?string $description,
        ?string $image,
        string $vatMode,
        string $currency,
        array $features,
        ?string $addon
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->price = $price;
        $this->isOutOfStock = $isOutOfStock;
        $this->heading = $heading;
        $this->description = $description;
        $this->image = $image;
        $this->vatMode = $vatMode;
        $this->currency = $currency;
        $this->features = $features;
        $this->addon = $addon;
    }
}
