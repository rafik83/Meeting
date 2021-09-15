<?php

namespace Proximum\Vimeet\Application\View\Planner;

class SheetView
{
    /** @var int */
    public $id;

    /** @var TypeView */
    public $type;

    /** @var int */
    public $planningQuantity;

    /** @var int */
    public $possibleMeetingsQuantity;

    /** @var string */
    public $reference;

    /**
     * @param int      $id
     * @param TypeView $type
     * @param int      $planningQuantity
     * @param int      $possibleMeetingsQuantity
     */
    public function __construct(
        $id,
        TypeView $type,
        $planningQuantity,
        $possibleMeetingsQuantity
    ) {
        $this->id                       = $id;
        $this->type                     = $type;
        $this->planningQuantity         = $planningQuantity;
        $this->possibleMeetingsQuantity = $possibleMeetingsQuantity;
        $this->reference                = sprintf('sheet%s', $id);
    }

    /**
     * @return string
     */
    public function getTypeReference()
    {
        return $this->type->reference;
    }
}
