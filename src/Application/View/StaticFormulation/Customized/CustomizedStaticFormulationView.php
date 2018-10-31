<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\StaticFormulation\Customized;

use Proximum\Vimeet\Domain\Intention\IntentionType;

class CustomizedStaticFormulationView
{
    /** @var string */
    public $key;

    /** @var string */
    public $title;

    /** @var string[] */
    public $typeTitles;

    /** @var int */
    public $staticFormulationId;

    public function __construct(
        string $key,
        int $staticFormulationId,
        string $title,
        array $typeTitles = []
    ) {
        $this->key = $key;
        $this->title = $title;
        $this->typeTitles = $typeTitles;
        $this->staticFormulationId = $staticFormulationId;
    }

    public function getIntention(): string
    {
        return IntentionType::INTENTION_REMOVE_STATIC_FORMULATION;
    }
}
