<?php

namespace Proximum\Vimeet\Application\View\StaticFormulation\Generic;

class GenericStaticFormulationView
{
    /** @var string */
    public $key;

    /** @var string */
    public $title;

    /** @var string[] */
    public $typeTitles;

    public function __construct(
        string $key,
        string $title,
        array $typeTitles = []
    ) {
        $this->key = $key;
        $this->title = $title;
        $this->typeTitles = $typeTitles;
    }

    public function hasNoneTypeRemaining(): bool
    {
        return empty($this->typeTitles);
    }
}
