<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Spot;

class SpotSatisfactionView
{
    /** @var int */
    public $id;

    /** @var string */
    public $reference;

    /** @var bool */
    public $shared;

    /** @var bool */
    public $visio;

    /** @var int */
    public $satisfaction;

    /** @var null|int */
    public $priority;

    public function __construct(
        int $id,
        string $reference,
        bool $shared,
        bool $visio,
        ?int $priority,
        int $satisfaction
    ) {
        $this->id = $id;
        $this->reference = $reference;
        $this->shared = $shared;
        $this->visio = $visio;
        $this->priority = $priority;
        $this->satisfaction = $satisfaction;
    }
}
