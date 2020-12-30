<?php

namespace Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Sheet;

class SheetSatisfactionView
{
    /** @var int */
    public $id;

    /** @var string */
    public $typeTitle;

    /** @var null|string */
    public $title;

    /** @var int */
    public $typeId;

    /** @var int */
    public $satisfaction;

    /**
     * @param int         $id
     * @param string|null $title
     * @param int         $typeId
     * @param string      $typeTitle
     * @param int         $satisfaction
     */
    public function __construct(int $id, string $title = null, int $typeId, string $typeTitle, int $satisfaction)
    {
        $this->id = $id;
        $this->title = $title;
        $this->typeId = $typeId;
        $this->typeTitle = $typeTitle;
        $this->satisfaction = $satisfaction;
    }
}
