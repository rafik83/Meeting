<?php

namespace Proximum\Vimeet\Domain\Package\Funnel;

class Step
{
    public const TYPE_PLAN = 'plan';
    public const TYPE_PARTICIPANT_PLANNING = 'participant_planning';
    public const TYPE_OPTIONS = 'options';

    public const TYPE_STEPS = [self::TYPE_PLAN, self::TYPE_PARTICIPANT_PLANNING, self::TYPE_OPTIONS];

    /**
     * @var int
     */
    public $index;

    /**
     * @var string
     */
    public $title;

    /**
     * @var bool
     */
    public $completed;

    /**
     * @var string
     */
    public $type;

    /**
     * @param int    $index
     * @param string $title
     * @param string $type
     * @param bool   $completed
     */
    public function __construct($index, $title, $type, $completed = false)
    {
        $this->index     = $index;
        $this->title     = $title;
        $this->type      = $type;
        $this->completed = $completed;
    }
}
