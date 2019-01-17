<?php

namespace Proximum\Vimeet\Application\View\Flux;

class SheetView
{
    /** @var string */
    public $type;

    /** @var null|string */
    public $title;

    /** @var null|string */
    public $description;

    /** @var null|string */
    public $country;

    /** @var null|string */
    public $logo;

    public function __construct(
        string $type,
        ?string $title,
        ?string $description,
        ?string $country,
        string $logo
    ) {
        $this->type = $type;
        $this->title = $title;
        $this->description = $description;
        $this->country = $country;
        $this->logo = $logo;
    }
}
