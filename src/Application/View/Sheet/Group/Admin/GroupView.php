<?php

namespace Proximum\Vimeet\Application\View\Sheet\Group\Admin;

use Proximum\Vimeet\Application\View\Sheet\Group\SheetView;

class GroupView
{
    /** @var int */
    public $id;

    /** @var string */
    public $title;

    /** @var string */
    public $managerEmail;

    /** @var int */
    public $managerId;

    /** @var \DateTimeInterface */
    public $createdAt;

    /** @var SheetView[] */
    public $sheetViews;

    /** @var string */
    public $impersonateLink;

    public function __construct(
        int $id,
        string $title,
        string $managerEmail,
        int $managerId,
        array $sheetViews,
        \DateTimeInterface $createdAt
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->sheetViews = $sheetViews;
        $this->managerEmail = $managerEmail;
        $this->managerId = $managerId;
        $this->createdAt = $createdAt;
    }
}
