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

    public function __construct(string $type, ?string $title, ?string $description, ?string $country)
    {
        $this->type = $type;
        $this->title = $title;
        $this->description = $description;
        $this->country = $country;
    }
}
