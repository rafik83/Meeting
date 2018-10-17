<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\StaticFormulation\Customized;

class CustomizedStaticFormulationView
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
}
