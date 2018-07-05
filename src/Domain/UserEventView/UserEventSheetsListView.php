<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\UserEventView;

class UserEventSheetsListView
{
    /** @var int */
    public $id;

    /** @var string */
    public $title;

    /** @var bool */
    public $isOwner;

    /** @var string */
    public $typeTitle;

    /** @var string */
    public $categoriesTitle;

    public function __construct(
        int $id,
        string $title,
        bool $isOwner,
        string $typeTitle,
        string $categoriesTitle
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->isOwner = $isOwner;
        $this->typeTitle = $typeTitle;
        $this->categoriesTitle = $categoriesTitle;
    }
}
