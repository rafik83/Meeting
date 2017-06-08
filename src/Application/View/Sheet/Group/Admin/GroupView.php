<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Sheet\Group\Admin;

use Proximum\Vimeet\Application\View\Sheet\Group\SheetView;

class GroupView
{
    /** @var int */
    public $id;

    /** @var string */
    public $title;

    /** @var string */
    public $emailManager;

    /** @var \DateTimeInterface */
    public $createdAt;

    /** @var SheetView[] */
    public $sheetViews;

    /** @var string */
    public $impersonateLink;

    /**
     * @param int                $id
     * @param string             $title
     * @param string             $emailManager
     * @param SheetView[]        $sheetViews
     * @param string             $impersonateLink
     * @param \DateTimeInterface $createdAt
     */
    public function __construct(
        $id,
        $title,
        $emailManager,
        array $sheetViews,
        $impersonateLink,
        \DateTimeInterface $createdAt
    ) {
        $this->id              = $id;
        $this->title           = $title;
        $this->sheetViews      = $sheetViews;
        $this->emailManager    = $emailManager;
        $this->impersonateLink = $impersonateLink;
        $this->createdAt       = $createdAt;
    }
}
