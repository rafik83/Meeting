<?php

namespace Proximum\Vimeet\Application\View\Flux;

class SheetView
{
    /** @var string */
    public $title;

    /** @var string */
    public $description;

    /** @var string */
    public $type;

    /** @var string */
    public $country;

    public function __construct(string $title, string $description, string $type, string $country)
    {
        $this->title = $title;
        $this->description = $description;
        $this->type = $type;
        $this->country = $country;
    }
}
