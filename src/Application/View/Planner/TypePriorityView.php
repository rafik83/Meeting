<?php

namespace Proximum\Vimeet\Application\View\Planner;

class TypePriorityView
{
    /** @var TypeView */
    public $fromType;

    /** @var TypeView */
    public $toType;

    /** @var int */
    public $priority;

    /** @var string */
    public $reference;

    /**
     * @param TypeView $fromType
     * @param TypeView $toType
     * @param int      $priority
     */
    public function __construct(TypeView $fromType, TypeView $toType, $priority)
    {
        $this->fromType  = $fromType;
        $this->toType    = $toType;
        $this->priority  = $priority;
        $this->reference = sprintf('priorityType%s-%s', $fromType->id, $toType->id);
    }

    /**
     * @return string
     */
    public function getFromTypeReference()
    {
        return $this->fromType->reference;
    }

    /**
     * @return string
     */
    public function getToTypeReference()
    {
        return $this->toType->reference;
    }
}
