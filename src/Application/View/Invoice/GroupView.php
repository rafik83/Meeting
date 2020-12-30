<?php

namespace Proximum\Vimeet\Application\View\Invoice;

class GroupView
{
    const TYPE_OPTION = 'option';

    /** @var int */
    public $groupId;

    /** @var string */
    public $label;

    /** @var string */
    public $type;

    /** @var RowView[] */
    public $rowViews = [];

    /** @var CustomRowView[] */
    public $customRowViews = [];

    /**
     * @param string          $label
     * @param string          $type
     * @param int             $groupId
     * @param RowView[]       $rowViews
     * @param CustomRowView[] $customRowViews
     */
    public function __construct(
        $label,
        $type,
        $groupId,
        array $rowViews = [],
        array $customRowViews = []
    ) {
        $this->label          = $label;
        $this->type           = $type;
        $this->groupId        = $groupId;
        $this->rowViews       = $rowViews;
        $this->customRowViews = $customRowViews;
    }

    /**
     * @return bool
     */
    public function isOption()
    {
        return self::TYPE_OPTION === $this->type;
    }
}
